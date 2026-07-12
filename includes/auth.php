<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Determine the path to login.php depending on if we are in admin/ or a subfolder like admin/appointments/
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $login_url = ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'login.php' : '../login.php';
    
    header('Location: ' . $login_url);
    exit;
}

// Cashier Role Restrictions
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'cashier') {
    // Cashiers are ONLY allowed in the 'pos' directory or 'logout.php'
    if ($current_dir !== 'pos' && basename($_SERVER['PHP_SELF']) !== 'logout.php') {
        $pos_url = ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'pos/index.php' : '../pos/index.php';
        header('Location: ' . $pos_url);
        exit;
    }
}
?>
