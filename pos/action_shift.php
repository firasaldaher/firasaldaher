<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/constants.php';
require_once __DIR__ . '/../api/config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['admin_id'];
    $expected_cash = $_POST['expected_cash'] ?? 0;
    $actual_cash = $_POST['actual_cash'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    $difference = $actual_cash - $expected_cash;

    $stmt = $db->prepare("
        INSERT INTO shift_closings (user_id, expected_cash, actual_cash, difference, notes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$user_id, $expected_cash, $actual_cash, $difference, $notes])) {
        // You could also destroy the session here if you want to force logout:
        // session_destroy();
        // header("Location: ../login.php");
        // exit;

        header("Location: shift.php?success=1");
        exit;
    } else {
        echo "Error saving shift data.";
        exit;
    }
} else {
    header("Location: shift.php");
    exit;
}
?>
