<?php
require_once __DIR__ . '/../config/database.php';

class BookingModel {
    private $conn;
    private $table_name = "appointments";

    // Booking Properties
    public $id;
    public $client_name;
    public $client_phone;
    public $client_id;
    public $service;
    public $appointment_date;
    public $appointment_time;
    public $status;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Create a new booking
     */
    public function create() {
        if (!$this->conn) {
            throw new Exception("Database connection failed.");
        }

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    client_name = :name,
                    client_phone = :phone,
                    service = :service,
                    appointment_date = :date,
                    appointment_time = :time,
                    status = :status,
                    client_id = :client_id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->client_phone = htmlspecialchars(strip_tags($this->client_phone));
        $this->service = htmlspecialchars(strip_tags($this->service));
        $this->appointment_date = htmlspecialchars(strip_tags($this->appointment_date));
        $this->appointment_time = htmlspecialchars(strip_tags($this->appointment_time));
        $this->status = "pending";

        // bind values
        $stmt->bindParam(":name", $this->client_name);
        $stmt->bindParam(":phone", $this->client_phone);
        $stmt->bindParam(":service", $this->service);
        $stmt->bindParam(":date", $this->appointment_date);
        $stmt->bindParam(":time", $this->appointment_time);
        $stmt->bindParam(":status", $this->status);
        
        $clientId = isset($this->client_id) ? $this->client_id : null;
        $stmt->bindParam(":client_id", $clientId);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

