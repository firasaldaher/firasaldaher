<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents("php://input"));
if (!$data && !empty($_POST)) {
    $data = (object)$_POST;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data->action) && $data->action === 'enroll') {
    $db = (new Database())->getConnection();
    $name = htmlspecialchars($data->name ?? '');
    $email = htmlspecialchars($data->email ?? '');
    $phone = htmlspecialchars($data->phone ?? '');
    $course = htmlspecialchars($data->course_name ?? '');

    if(empty($name) || empty($email) || empty($phone) || empty($course)){
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO academy_enrollments (name, email, phone, course_name) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $phone, $course])) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Application submitted successfully! Our admissions team will contact you soon."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "There was an error submitting your application. Please try again."]);
    }
}
