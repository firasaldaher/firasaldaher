<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ClientModel.php';

class ClientController {
    private $clientModel;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->clientModel = new ClientModel($db);
    }

    public function getClientDashboardData($clientId) {
        $this->clientModel->id = $clientId;
        
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT points FROM clients WHERE id = ?");
        $stmt->execute([$clientId]);
        $points = $stmt->fetchColumn() ?: 0;

        return [
            'appointments' => $this->clientModel->getAppointments(),
            'loyalty_points' => $points
        ];
    }
}
