<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/FirebaseFCM.php';

// Admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (isset($data->title) && isset($data->body)) {
    $title = htmlspecialchars(strip_tags($data->title));
    $body = htmlspecialchars(strip_tags($data->body));

    $tokens = [];
    if (!empty($data->tokens) && is_array($data->tokens)) {
        // Send only to selected tokens
        $tokens = $data->tokens;
    } else {
        // Broadcast to all
        try {
            $query = "SELECT token FROM device_tokens";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "No devices table found or empty."]);
            exit;
        }
    }

    if (empty($tokens)) {
        http_response_code(400);
        echo json_encode(["message" => "No registered devices found."]);
        exit;
    }

    $fcm = new FirebaseFCM();
    $success = 0;
    $failed = 0;

    foreach ($tokens as $token) {
        try {
            $result = $fcm->sendNotification($token, $title, $body);
            if ($result['status'] == 200) {
                $success++;
            } else {
                $failed++;
            }
        } catch (Exception $e) {
            $failed++;
        }
    }

    echo json_encode([
        "message" => "Push completed.",
        "success" => $success,
        "failed" => $failed
    ]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Title and body are required."]);
}
