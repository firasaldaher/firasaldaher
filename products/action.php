<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../api/config/constants.php';
require_once __DIR__ . '/../api/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Categories Actions
    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
        }
    } 
    elseif ($action === 'delete_category') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }
    
    // Products Actions
    elseif ($action === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $category_id = $_POST['category_id'] ?? null;
        $price = $_POST['price'] ?? 0;
        $cost = $_POST['cost'] ?? 0;
        
        if (!empty($name) && !empty($category_id)) {
            $stmt = $db->prepare("INSERT INTO products (category_id, name, price, cost) VALUES (:category_id, :name, :price, :cost)");
            $stmt->execute([
                ':category_id' => $category_id,
                ':name' => $name,
                ':price' => $price,
                ':cost' => $cost
            ]);
        }
    }
    elseif ($action === 'delete_product') {
        $id = $_POST['id'] ?? 0;
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }
}

// Redirect back to products index
header("Location: index.php");
exit;
?>
