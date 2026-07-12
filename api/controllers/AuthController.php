<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ClientModel.php';

$database = new Database();
$db = $database->getConnection();

$client = new ClientModel($db);

// Get posted data
// Accept either JSON or Form Data
$data = json_decode(file_get_contents("php://input"));
if (!$data && !empty($_POST)) {
    $data = (object)$_POST;
}

if (!isset($data->action)) {
    http_response_code(400);
    echo json_encode(["message" => "Action is required.", "status" => "error"]);
    exit;
}

if ($data->action === 'login') {
    $email = $data->email ?? '';
    $password = $data->password ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["message" => "Email and password are required.", "status" => "error"]);
        exit;
    }

    if ($client->login($email, $password)) {
        $_SESSION['client_id'] = $client->id;
        $_SESSION['client_name'] = $client->name;
        $_SESSION['client_email'] = $client->email;
        
        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.", 
            "status" => "success",
            "redirect" => "dashboard.php"
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid email or password.", "status" => "error"]);
    }
} elseif ($data->action === 'register') {
    $name = $data->name ?? '';
    $email = $data->email ?? '';
    $phone = $data->phone ?? '';
    $password = $data->password ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        http_response_code(400);
        echo json_encode(["message" => "All fields are required.", "status" => "error"]);
        exit;
    }

    $client->name = $name;
    $client->email = $email;
    $client->phone = $phone;
    $client->password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    if ($client->register()) {
        http_response_code(201);
        echo json_encode(["message" => "Account created successfully! Please sign in.", "status" => "success"]);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Registration failed. Email might already exist.", "status" => "error"]);
    }
} elseif ($data->action === 'google_login') {
    $email = $data->email ?? '';
    $name = $data->name ?? '';
    
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(["message" => "Email is required.", "status" => "error"]);
        exit;
    }

    if ($client->googleLogin($email, $name)) {
        $_SESSION['client_id'] = $client->id;
        $_SESSION['client_name'] = $client->name;
        $_SESSION['client_email'] = $client->email;
        
        http_response_code(200);
        echo json_encode([
            "message" => "Google Login successful.", 
            "status" => "success",
            "redirect" => "dashboard.php"
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to authenticate with Google.", "status" => "error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid action.", "status" => "error"]);
}
