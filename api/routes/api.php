<?php
// API Routing file

// Helper function to extract endpoint from url
$endpoint = isset($urlParts[0]) && $urlParts[0] !== '' ? $urlParts[0] : null;
$action = isset($urlParts[1]) ? $urlParts[1] : null;
$method = $_SERVER['REQUEST_METHOD'];

if (!$endpoint) {
    Response::json('success', 'Welcome to the 33northlb API v' . API_VERSION);
}

// Route mapping
switch ($endpoint) {
    case 'book':
        require_once __DIR__ . '/../controllers/BookingController.php';
        $controller = new BookingController();
        
        if ($method === 'POST') {
            $controller->createBooking();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'contact':
        require_once __DIR__ . '/../controllers/ContactController.php';
        $controller = new ContactController();
        
        if ($method === 'POST') {
            $controller->submitMessage();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'services':
        require_once __DIR__ . '/../controllers/ServicesController.php';
        $controller = new ServicesController();
        
        if ($method === 'GET') {
            $controller->getServices();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'auth':
        // Placeholder for future AuthController
        Response::error('Auth endpoint not implemented yet', 501);
        break;
        
    default:
        Response::error('Endpoint not found', 404);
        break;
}

