<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class ServicesController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getServices() {
        try {
            if (!$this->db) {
                Response::error('Database connection failed', 500);
            }
            $query = "SELECT * FROM services WHERE is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by gender
            $grouped = [
                'men' => [],
                'women' => []
            ];
            
            foreach ($services as $service) {
                // Add leading zero to ID
                $service['num'] = str_pad($service['id'], 2, '0', STR_PAD_LEFT);
                // Format time and price
                $service['time'] = $service['duration'] . ' min';
                $service['price'] = '$' . round($service['price']);
                $service['desc'] = $service['description'];
                
                if ($service['gender'] === 'men') {
                    $grouped['men'][] = $service;
                } else {
                    $grouped['women'][] = $service;
                }
            }
            
            Response::json('success', 'Services fetched', $grouped);
        } catch (PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
}
