<?php
/**
 * ============================================================
 *  Caraway — Visitor Intelligence Collector
 *  يستقبل بيانات الزوار ويحفظها بصيغة JSON
 * ============================================================
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'POST only']); exit; }

/* ============================================================
   CONFIG
============================================================ */
define('VISITS_DIR', __DIR__ . '/../../data/visits/');
define('INDEX_FILE', __DIR__ . '/../../data/visitors_index.json');
define('MAX_RECORDS', 5000);    // max rows in index
define('SECRET_KEY',  '');      // leave empty to disable auth

/* ============================================================
   AUTH (optional)
============================================================ */
if (SECRET_KEY !== '') {
    $token = $_SERVER['HTTP_X_COLLECTOR_KEY'] ?? '';
    if ($token !== SECRET_KEY) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
}

/* ============================================================
   PARSE BODY
============================================================ */
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }

/* ============================================================
   ENRICH WITH SERVER-SIDE DATA
   (server sees the real IP even behind proxies)
============================================================ */
$serverIP = $_SERVER['HTTP_CF_CONNECTING_IP']     // Cloudflare
         ?? $_SERVER['HTTP_X_FORWARDED_FOR']       // Nginx proxy
         ?? $_SERVER['HTTP_X_REAL_IP']             // Alt proxy header
         ?? $_SERVER['REMOTE_ADDR']                // Direct
         ?? 'unknown';

// If multiple IPs in X-Forwarded-For, take the first (client)
if (strpos($serverIP, ',') !== false) {
    $serverIP = trim(explode(',', $serverIP)[0]);
}

$data['server'] = [
    'ip_server_side'   => $serverIP,
    'user_agent_server'=> $_SERVER['HTTP_USER_AGENT'] ?? '',
    'accept_language'  => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
    'accept_encoding'  => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
    'method'           => $_SERVER['REQUEST_METHOD'],
    'protocol'         => $_SERVER['SERVER_PROTOCOL'] ?? '',
    'https'            => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'port'             => $_SERVER['SERVER_PORT'] ?? '',
    'collected_at'     => date('Y-m-d H:i:s T'),
];

/* ============================================================
   SAVE INDIVIDUAL VISIT FILE
============================================================ */
$visitId = date('Ymd_His') . '_' . substr(md5($serverIP . microtime()), 0, 8);
$visitFile = VISITS_DIR . $visitId . '.json';

if (!is_dir(VISITS_DIR)) { mkdir(VISITS_DIR, 0755, true); }

file_put_contents($visitFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

/* ============================================================
   UPDATE ROLLING INDEX (for dashboard)
============================================================ */
$indexData = [];
if (file_exists(INDEX_FILE)) {
    $indexData = json_decode(file_get_contents(INDEX_FILE), true) ?: [];
}

$indexEntry = [
    'id'          => $visitId,
    'ts'          => $data['timestamp'] ?? date('c'),
    'ip'          => $data['ip']['ip'] ?? $serverIP,
    'ip_server'   => $serverIP,
    'city'        => $data['ip']['city'] ?? '',
    'country'     => $data['ip']['country'] ?? '',
    'isp'         => $data['ip']['isp'] ?? '',
    'os'          => $data['device']['os'] ?? '',
    'device_type' => $data['device']['deviceType'] ?? '',
    'brand'       => $data['device']['brand'] ?? '',
    'browser'     => ($data['browser']['name'] ?? '') . ' ' . ($data['browser']['version'] ?? ''),
    'cores'       => $data['device']['cores'] ?? '',
    'ram'         => $data['device']['ram'] ?? '',
    'gpu'         => $data['browser']['gpu'] ?? '',
    'screen'      => $data['screen']['screenResolution'] ?? '',
    'viewport'    => $data['screen']['viewport'] ?? '',
    'dpr'         => $data['screen']['devicePixelRatio'] ?? '',
    'is_touch'    => $data['screen']['isTouch'] ?? false,
    'color_gamut' => $data['screen']['colorGamut'] ?? '',
    'language'    => $data['browser']['language'] ?? '',
    'timezone'    => $data['browser']['timezone'] ?? '',
    'tz_offset'   => $data['browser']['tzStr'] ?? '',
    'referrer'    => $data['page']['referrer'] ?? '',
    'page'        => $data['page']['path'] ?? '',
    'dwell_sec'   => $data['behavior']['dwellSec'] ?? 0,
    'scroll_pct'  => $data['behavior']['maxScrollPct'] ?? 0,
    'clicks'      => $data['behavior']['clicks'] ?? 0,
    'connection'  => $data['browser']['connection']['effectiveType'] ?? '',
    'canvas_hash' => $data['browser']['canvasHash'] ?? '',
    'file'        => $visitId . '.json',
];

array_unshift($indexData, $indexEntry);

// Trim to MAX_RECORDS
if (count($indexData) > MAX_RECORDS) {
    $indexData = array_slice($indexData, 0, MAX_RECORDS);
}

$dataDir = dirname(INDEX_FILE);
if (!is_dir($dataDir)) { mkdir($dataDir, 0755, true); }
file_put_contents(INDEX_FILE, json_encode($indexData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);

/* ============================================================
   RESPOND
============================================================ */
echo json_encode(['ok' => true, 'visitId' => $visitId]);