<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../api/config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->items) && !empty($data->total) && !empty($data->order_type)) {
    try {
        $db->beginTransaction();

        $query = "INSERT INTO orders (total_amount, status, order_type, customer_info) VALUES (:total, 'completed', :order_type, :customer_info)";
        $stmt = $db->prepare($query);
        
        $customer_info = !empty($data->customer_info) ? htmlspecialchars(strip_tags($data->customer_info)) : null;
        
        $stmt->bindParam(":total", $data->total);
        $stmt->bindParam(":order_type", $data->order_type);
        $stmt->bindParam(":customer_info", $customer_info);
        
        if($stmt->execute()) {
            $order_id = $db->lastInsertId();
            
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $itemStmt = $db->prepare($itemQuery);
            
            foreach($data->items as $item) {
                $itemStmt->bindValue(":order_id", $order_id);
                $itemStmt->bindValue(":product_id", $item->id);
                $itemStmt->bindValue(":quantity", $item->qty);
                $itemStmt->bindValue(":price", $item->price);
                $itemStmt->execute();
            }
            
            $db->commit();
            http_response_code(201);
            echo json_encode(array("status" => "success", "message" => "Order created.", "order_id" => $order_id));
        } else {
            $db->rollBack();
            http_response_code(503);
            echo json_encode(array("status" => "error", "message" => "Unable to create order."));
        }
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Incomplete data."));
}
?>
