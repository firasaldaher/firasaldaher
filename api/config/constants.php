<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// API Constants

define('API_VERSION', '1.0');
define('APP_NAME', '33northlb');

// Database configuration
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, '"\'');
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }
}

// Fallbacks if .env doesn't exist or is missing values
$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);

if ($is_localhost) {
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'caraway_db'); // ضع اسم قاعدة بياناتك المحلية هنا
} else {
    // Production Credentials
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_USER')) define('DB_USER', 'caraway_admin_db');
    if (!defined('DB_PASS')) define('DB_PASS', 'dN6f9i1*,9hmv7G.');
    if (!defined('DB_NAME')) define('DB_NAME', 'ovabcgyl_caraway_db');
}

// JWT Secret Key (for future authentication)
define('JWT_SECRET', 'YOUR_SUPER_SECRET_JWT_KEY_HERE_CHANGE_IN_PRODUCTION');

// AES Encryption Key (Must be exactly 32 bytes for AES-256)
define('APP_KEY', '33northlb_secret_encryption_key!'); // Change this to a secure random 32-character string in production

// Base URL for the application
define('BASE_URL', '/33northlb/');
