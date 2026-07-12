<?php
require_once __DIR__ . '/constants.php';

/**
 * Encrypts a path using AES-256-CBC
 *
 * @param string $path The internal path to encrypt (e.g., 'admin/login.php')
 * @return string The base64 URL-safe encrypted string
 */
function encrypt_path($path) {
    if (empty($path)) return '';
    
    $cipher = "AES-256-CBC";
    $iv_length = openssl_cipher_iv_length($cipher);
    
    // Generate a secure random initialization vector
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    // Encrypt the path
    $encrypted = openssl_encrypt($path, $cipher, APP_KEY, 0, $iv);
    
    // Concatenate IV and Encrypted string, then base64 encode
    $payload = base64_encode($iv . $encrypted);
    
    // Make the base64 string URL-safe
    $payload = str_replace(array('+', '/', '='), array('-', '_', ''), $payload);
    
    return $payload;
}

/**
 * Decrypts an encrypted URL path
 *
 * @param string $payload The encrypted string from the URL
 * @return string|false The decrypted internal path or false on failure
 */
function decrypt_path($payload) {
    if (empty($payload)) return false;
    
    $cipher = "AES-256-CBC";
    $iv_length = openssl_cipher_iv_length($cipher);
    
    // Revert URL-safe base64 string
    $payload = str_replace(array('-', '_'), array('+', '/'), $payload);
    
    // padding for base64 decode
    $mod4 = strlen($payload) % 4;
    if ($mod4) {
        $payload .= substr('====', $mod4);
    }

    // Decode base64
    $decoded = base64_decode($payload);
    if ($decoded === false) return false;
    
    // Extract IV and encrypted string
    $iv = substr($decoded, 0, $iv_length);
    $encrypted = substr($decoded, $iv_length);
    
    if (strlen($iv) !== $iv_length) return false;
    
    // Decrypt
    $decrypted = openssl_decrypt($encrypted, $cipher, APP_KEY, 0, $iv);
    
    return $decrypted;
}

/**
 * Generates a full URL using the encrypted path
 *
 * @param string $path The internal path
 * @return string The absolute URL
 */
function e_url($path) {
    if ($path === '' || $path === '#') return $path;
    return BASE_URL . encrypt_path($path);
}
