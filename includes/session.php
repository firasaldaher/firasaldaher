<?php
/**
 * Advanced Session Security System
 * Enforces strict cookie parameters, prevents hijacking and fixation.
 */

// 1. Force sessions to only use cookies (no URL parameters)
ini_set('session.use_only_cookies', 1);
// 2. Reject uninitialized session IDs
ini_set('session.use_strict_mode', 1);

// Determine if we are using HTTPS
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

// 3. Set secure cookie parameters BEFORE session starts
session_set_cookie_params([
    'lifetime' => 0, // Expires when browser is closed
    'path' => '/',
    // 'domain' is intentionally omitted here to prevent duplicate cookies (.domain.com vs domain.com)
    'secure' => $isSecure, // Sent over HTTPS ONLY (Dynamic to allow local HTTP testing)
    'httponly' => true,    // Javascript cannot access the cookie (Prevents XSS theft)
    'samesite' => 'Strict' // Cookie not sent on cross-site requests (Prevents CSRF)
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Session Hijacking Protection (Bind session to User Agent and partial IP)
// IP binding can cause issues for mobile users switching networks, so we bind to User Agent.
$currentUserAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';

if (isset($_SESSION['user_agent'])) {
    if ($_SESSION['user_agent'] !== $currentUserAgent) {
        // Anomaly detected: Destroy session
        session_unset();
        session_destroy();
        
        // Start a fresh, clean session
        session_start();
        session_regenerate_id(true);
    }
} else {
    // First time session is created, store the User Agent
    $_SESSION['user_agent'] = $currentUserAgent;
}

// 5. Periodic Session ID Regeneration to prevent fixation over long sessions
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} else {
    $interval = 60 * 30; // 30 minutes
    if (time() - $_SESSION['last_regeneration'] >= $interval) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>
