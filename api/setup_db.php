<?php
require_once __DIR__ . '/config/constants.php';

try {
    // Connect to MySQL server without selecting a database first
    $conn = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Execute the SQL
    $conn->exec($sql);
    
    echo "Database setup successful!\n";
} catch (PDOException $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
}
