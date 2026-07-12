<?php
require_once __DIR__ . '/../config/database.php';

class ServiceController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Used for Server-Side Rendering (SSR) in pages like services.php and book.php
    public function getAllServices() {
        if (!$this->db) return [];
        
        try {
            $stmt = $this->db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY gender, name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getServicesGroupedByGender() {
        $services = $this->getAllServices();
        $menServices = [];
        $womenServices = [];

        foreach ($services as $service) {
            if ($service['gender'] === 'Men' || $service['gender'] === 'Unisex') {
                $menServices[] = $service;
            }
            if ($service['gender'] === 'Women' || $service['gender'] === 'Unisex') {
                $womenServices[] = $service;
            }
        }

        return ['men' => $menServices, 'women' => $womenServices];
    }
}

// Handle API requests (AJAX)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (!$data && !empty($_POST)) {
        $data = (object)$_POST;
    }

    if (isset($data->action) && $data->action === 'book_service') {
        header("Content-Type: application/json; charset=UTF-8");
        
        // This is a mockup of booking logic. In reality, it would insert into an 'appointments' table.
        $service_id = $data->service_id ?? '';
        $date = $data->date ?? '';
        $time = $data->time ?? '';
        
        if (empty($service_id) || empty($date) || empty($time)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        // Mock success
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Your appointment has been booked successfully!"]);
        exit;
    }
}
