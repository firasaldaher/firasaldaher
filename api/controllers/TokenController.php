<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (isset($data->token)) {
    $token = htmlspecialchars(strip_tags($data->token));
    $client_id = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : null;

    // Ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS device_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert or update token
    if ($client_id) {
        $query = "INSERT INTO device_tokens (client_id, token) VALUES (:cid, :tok) ON DUPLICATE KEY UPDATE client_id = :cid";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':cid', $client_id);
        $stmt->bindParam(':tok', $token);
    } else {
        $query = "INSERT INTO device_tokens (client_id, token) VALUES (NULL, :tok) ON DUPLICATE KEY UPDATE client_id = NULL";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tok', $token);
    }

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Token registered."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to register token."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Token missing."]);
}
