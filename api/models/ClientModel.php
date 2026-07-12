<?php
require_once __DIR__ . '/../config/database.php';

class ClientModel {
    private $conn;
    private $table_name = "clients";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $password_hash;
    public $points;
    public $created_at;

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    public function register() {
        if (!$this->conn) return false;

        // Check if email exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            return false; // Email exists
        }

        $query = "INSERT INTO " . $this->table_name . " SET name=:name, email=:email, phone=:phone, password_hash=:pass, points=0";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":pass", $this->password_hash);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        if (!$this->conn) return false;

        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password_hash'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->phone = $row['phone'];
                $this->points = $row['points'];
                return true;
            }
        }
        return false;
    }

    public function getAppointments() {
        if (!$this->conn || !$this->id) return [];

        $query = "SELECT * FROM appointments WHERE client_id = :client_id ORDER BY appointment_date DESC, appointment_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":client_id", $this->id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function googleLogin($email, $name) {
        if (!$this->conn) return false;

        $email = htmlspecialchars(strip_tags($email));
        $name = htmlspecialchars(strip_tags($name));

        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            return true;
        } else {
            // Register new user
            $query = "INSERT INTO " . $this->table_name . " SET name=:name, email=:email, phone='', password_hash='', points=0";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                $this->name = $name;
                $this->email = $email;
                $this->phone = '';
                return true;
            }
        }
        return false;
    }
}
