<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Determine the path to login.php depending on if we are in admin/ or a subfolder like admin/appointments/
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $login_url = ($current_dir === 'admin') ? 'login.php' : '../login.php';
    
    header('Location: ' . $login_url);
    exit;
}
?>
