<?php
require_once __DIR__ . '/api/config/constants.php';
echo "Host: " . DB_HOST . "\n";
echo "User: " . DB_USER . "\n";
echo "Pass: " . DB_PASS . "\n";
echo "DB: " . DB_NAME . "\n";

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection SUCCESSFUL!\n";
} catch(PDOException $e) {
    echo "Connection FAILED: " . $e->getMessage() . "\n";
}
?>
