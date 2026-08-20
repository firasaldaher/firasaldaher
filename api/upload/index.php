<?php
/**
 * ============================================================
 *  Caraway — Chunked Video Upload Backend (PHP)
 *  سيرفر رفع الفيديو بالأجزاء — يعمل على أي استضافة PHP
 * ============================================================
 *
 *  Endpoints (all handled by this single file):
 *  POST ?action=init    → start upload session, returns { uploadId }
 *  POST ?action=chunk   → receive one chunk
 *  POST ?action=finish  → merge all chunks into final file
 *  GET  ?action=status  → (optional) check a session status
 *
 *  Directory layout created automatically:
 *    /uploads/              ← final merged files
 *    /uploads/tmp/{id}/     ← temporary chunks while uploading
 */

/* ============================================================
   CONFIGURATION — تعديل حسب بيئتك
   ============================================================ */
define('UPLOAD_DIR',     __DIR__ . '/../../uploads/');         // final files
define('TMP_DIR',        __DIR__ . '/../../uploads/tmp/');     // chunk temp dir
define('MAX_FILE_MB',    200);                                 // 200 MB limit
define('ALLOWED_TYPES',  ['video/mp4','video/quicktime','video/x-msvideo',
                           'video/x-matroska','video/x-ms-wmv','video/x-flv',
                           'video/webm','video/m4v','application/octet-stream']);
define('SECRET_TOKEN',   '');    // اتركه فارغاً أو ضع token للحماية

/* ============================================================
   CORS & HEADERS
   ============================================================ */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Upload-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* ============================================================
   HELPERS
   ============================================================ */
function jsonOk(array $data): void {
    echo json_encode(array_merge(['ok' => true], $data));
    exit;
}

function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

function sanitizeFilename(string $name): string {
    $name = preg_replace('/[^a-zA-Z0-9._\-\u0621-\u064A ]/u', '_', $name);
    return mb_substr($name, 0, 120);
}

function ensureDir(string $path): void {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

/* ============================================================
   TOKEN CHECK (optional)
   ============================================================ */
if (SECRET_TOKEN !== '') {
    $token = $_SERVER['HTTP_X_UPLOAD_TOKEN'] ?? $_POST['token'] ?? '';
    if ($token !== SECRET_TOKEN) {
        jsonError('Unauthorized', 401);
    }
}

/* ============================================================
   ROUTER
   ============================================================ */
$action = strtolower(trim($_GET['action'] ?? $_POST['action'] ?? ''));

switch ($action) {

    /* ----------------------------------------------------------
       INIT — بدء جلسة رفع جديدة
    ---------------------------------------------------------- */
    case 'init':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST required');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $filename   = sanitizeFilename($body['filename']   ?? 'video.mp4');
        $filesize   = (int)($body['filesize']  ?? 0);
        $chunks     = (int)($body['chunks']    ?? 0);
        $title      = htmlspecialchars($body['title']      ?? '');
        $type       = htmlspecialchars($body['type']       ?? '');
        $notes      = htmlspecialchars($body['notes']      ?? '');

        if ($filesize <= 0 || $chunks <= 0) {
            jsonError('filesize and chunks are required');
        }
        if ($filesize > MAX_FILE_MB * 1024 * 1024) {
            jsonError('File exceeds ' . MAX_FILE_MB . ' MB limit');
        }

        // Generate unique upload ID
        $uploadId = bin2hex(random_bytes(16));

        // Create temp directory for this session
        $tmpPath = TMP_DIR . $uploadId . '/';
        ensureDir($tmpPath);

        // Save session metadata
        $meta = [
            'uploadId'  => $uploadId,
            'filename'  => $filename,
            'filesize'  => $filesize,
            'chunks'    => $chunks,
            'title'     => $title,
            'type'      => $type,
            'notes'     => $notes,
            'created'   => time(),
            'received'  => [],
        ];
        file_put_contents($tmpPath . 'meta.json', json_encode($meta));

        jsonOk(['uploadId' => $uploadId, 'chunks' => $chunks]);
        break;

    /* ----------------------------------------------------------
       CHUNK — استقبال جزء واحد
    ---------------------------------------------------------- */
    case 'chunk':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST required');

        $uploadId   = preg_replace('/[^a-f0-9]/', '', $_POST['uploadId']   ?? '');
        $chunkIndex = (int)($_POST['chunkIndex']  ?? -1);
        $totalChunks= (int)($_POST['totalChunks'] ?? 0);

        if (!$uploadId || $chunkIndex < 0 || !$totalChunks) {
            jsonError('Missing uploadId, chunkIndex or totalChunks');
        }

        $tmpPath = TMP_DIR . $uploadId . '/';
        if (!is_dir($tmpPath)) jsonError('Upload session not found', 404);

        // Validate uploaded chunk
        if (empty($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
            $errCode = $_FILES['chunk']['error'] ?? -1;
            jsonError('Chunk upload error code: ' . $errCode);
        }

        $chunkFile = $tmpPath . 'chunk_' . str_pad($chunkIndex, 6, '0', STR_PAD_LEFT);
        if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkFile)) {
            jsonError('Failed to save chunk ' . $chunkIndex, 500);
        }

        // Update received list in metadata
        $metaPath = $tmpPath . 'meta.json';
        $meta = json_decode(file_get_contents($metaPath), true);
        if (!in_array($chunkIndex, $meta['received'])) {
            $meta['received'][] = $chunkIndex;
        }
        file_put_contents($metaPath, json_encode($meta));

        jsonOk([
            'chunkIndex' => $chunkIndex,
            'received'   => count($meta['received']),
            'total'      => $totalChunks,
        ]);
        break;

    /* ----------------------------------------------------------
       FINISH — دمج الأجزاء وإنشاء الملف النهائي
    ---------------------------------------------------------- */
    case 'finish':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('POST required');

        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $uploadId = preg_replace('/[^a-f0-9]/', '', $body['uploadId'] ?? '');

        if (!$uploadId) jsonError('uploadId required');

        $tmpPath = TMP_DIR . $uploadId . '/';
        if (!is_dir($tmpPath)) jsonError('Upload session not found', 404);

        $metaPath = $tmpPath . 'meta.json';
        if (!file_exists($metaPath)) jsonError('Session metadata missing', 500);

        $meta = json_decode(file_get_contents($metaPath), true);
        $expectedChunks = $meta['chunks'];
        $receivedCount  = count($meta['received']);

        // Verify all chunks arrived
        if ($receivedCount < $expectedChunks) {
            jsonError("Incomplete upload: received $receivedCount of $expectedChunks chunks");
        }

        // Build final filename with timestamp to avoid collisions
        $ext         = pathinfo($meta['filename'], PATHINFO_EXTENSION) ?: 'mp4';
        $safeName    = sanitizeFilename(pathinfo($meta['filename'], PATHINFO_FILENAME));
        $finalName   = date('Ymd_His') . '_' . $safeName . '.' . $ext;
        $finalPath   = UPLOAD_DIR . $finalName;

        ensureDir(UPLOAD_DIR);

        // Merge chunks in order
        $out = fopen($finalPath, 'wb');
        if (!$out) jsonError('Cannot create output file', 500);

        for ($i = 0; $i < $expectedChunks; $i++) {
            $chunkFile = $tmpPath . 'chunk_' . str_pad($i, 6, '0', STR_PAD_LEFT);
            if (!file_exists($chunkFile)) {
                fclose($out);
                jsonError("Missing chunk file #$i", 500);
            }
            $in = fopen($chunkFile, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }
        fclose($out);

        // Cleanup temp directory
        array_map('unlink', glob($tmpPath . '*'));
        rmdir($tmpPath);

        // Save upload log (optional)
        $logEntry = [
            'date'     => date('Y-m-d H:i:s'),
            'filename' => $finalName,
            'title'    => $meta['title'],
            'type'     => $meta['type'],
            'notes'    => $meta['notes'],
            'size'     => filesize($finalPath),
        ];
        file_put_contents(
            UPLOAD_DIR . 'upload_log.json',
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );

        $fileUrl = '/uploads/' . $finalName;

        jsonOk([
            'filename' => $finalName,
            'url'      => $fileUrl,
            'size'     => filesize($finalPath),
        ]);
        break;

    /* ----------------------------------------------------------
       STATUS — فحص حالة جلسة رفع
    ---------------------------------------------------------- */
    case 'status':
        $uploadId = preg_replace('/[^a-f0-9]/', '', $_GET['uploadId'] ?? '');
        if (!$uploadId) jsonError('uploadId required');

        $metaPath = TMP_DIR . $uploadId . '/meta.json';
        if (!file_exists($metaPath)) jsonError('Session not found', 404);

        $meta = json_decode(file_get_contents($metaPath), true);
        jsonOk([
            'uploadId'  => $uploadId,
            'filename'  => $meta['filename'],
            'chunks'    => $meta['chunks'],
            'received'  => count($meta['received']),
            'percent'   => round(count($meta['received']) / $meta['chunks'] * 100),
        ]);
        break;

    default:
        jsonError('Unknown action. Valid: init, chunk, finish, status');
}
