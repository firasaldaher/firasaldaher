<?php
// Main API Entry Point

// 1. Require configurations
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

// 2. Require utilities and middlewares
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/middlewares/CorsMiddleware.php';

// 3. Handle CORS
CorsMiddleware::handle();

// 4. Parse URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$urlParts = explode('/', $url);

// 5. Route the request
require_once __DIR__ . '/routes/api.php';

