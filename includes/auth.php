<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../api/config/database.php';

$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_locked_page = (basename($_SERVER['PHP_SELF']) === 'locked.php');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (!$is_locked_page) {
        $login_url = ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'login.php' : '../login.php';
        header('Location: ' . $login_url);
        exit;
    }
} else {
    // Check SaaS Lock
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT is_locked FROM system_settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $is_locked = $settings['is_locked'] ?? 0;
    $role = $_SESSION['admin_role'] ?? 'cashier';
    
    if ($is_locked == 1 && $role !== 'super_admin') {
        if (!$is_locked_page && basename($_SERVER['PHP_SELF']) !== 'logout.php') {
            $lock_url = ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'locked.php' : '../locked.php';
            header('Location: ' . $lock_url);
            exit;
        }
    } else {
        // If system is active or user is super_admin, they shouldn't be on locked page
        if ($is_locked_page) {
            $dashboard_url = ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'index.php' : '../index.php';
            header('Location: ' . $dashboard_url);
            exit;
        }
        
        // Cashier Role Restrictions
        if ($role === 'cashier') {
            if ($current_dir !== 'pos' && basename($_SERVER['PHP_SELF']) !== 'logout.php') {
                $pos_url = ($current_dir === 'admin' || $current_dir === 'caraway_system') ? 'pos/index.php' : '../pos/index.php';
                header('Location: ' . $pos_url);
                exit;
            }
        }
    }
}
?>
