<?php

class Response {
    /**
     * Send a standardized JSON response
     */
    public static function json($status, $message, $data = [], $httpCode = 200) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Send a standardized JSON error response
     */
    public static function error($message, $httpCode = 400) {
        self::json('error', $message, [], $httpCode);
    }
}

