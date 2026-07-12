<?php
require_once __DIR__ . '/../config/database.php';

class ProductController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllProducts() {
        if (!$this->db) return [];
        
        try {
            $stmt = $this->db->query("SELECT * FROM products WHERE is_active = 1 ORDER BY category, name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
