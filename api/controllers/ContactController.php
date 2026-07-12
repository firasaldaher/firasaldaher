<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class ContactController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function submitMessage() {
        // Get JSON body
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->name) && !empty($data->email) && !empty($data->message)) {
            try {
                if (!$this->db) {
                    Response::error('Database connection failed', 500);
                }
                $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)";
                $stmt = $this->db->prepare($query);

                $stmt->bindParam(":name", $data->name);
                $stmt->bindParam(":email", $data->email);
                $stmt->bindParam(":subject", $data->subject);
                $stmt->bindParam(":message", $data->message);

                if ($stmt->execute()) {
                    Response::json('success', 'Message sent successfully');
                } else {
                    Response::error('Failed to send message', 500);
                }
            } catch (PDOException $e) {
                Response::error('Database error: ' . $e->getMessage(), 500);
            }
        } else {
            Response::error('Incomplete data. Name, email, and message are required.', 400);
        }
    }
}
