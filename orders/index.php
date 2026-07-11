<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? 0;

    if ($action === 'update_status') {
        $status = $_POST['status'] ?? 'pending';
        $stmt = $db->prepare("UPDATE ecommerce_orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $message = "<div style='color: green; margin-bottom:15px; padding:10px; background:#e8f8f5; border-radius:6px;'>Order status updated to " . htmlspecialchars($status) . ".</div>";
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $db->prepare("DELETE FROM ecommerce_orders WHERE id = ?");
            $stmt->execute([$id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Order deleted successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all orders
$stmt = $db->query("SELECT * FROM ecommerce_orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Orders | Admin | 33° NORTH</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Ecommerce Orders</h1>
          <div class="page-subtitle">Manage online store purchases and shipments.</div>
        </div>
      </div>

      <?php echo $message; ?>

      <div class="card-panel" style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer Info</th>
              <th>Order Summary</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
              <td>
                <div style="font-weight: 700; color: var(--admin-primary); font-family: var(--font-head); font-size: 16px;">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
                <div style="color: var(--admin-text-muted); font-size: 13px; margin-top: 2px;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
              </td>
              <td>
                <div style="font-weight: 600; color: var(--admin-text); margin-bottom: 2px;"><?php echo htmlspecialchars($order['client_name']); ?></div>
                <div style="font-size: 13px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($order['phone']); ?></div>
                <div style="font-size: 13px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($order['address']); ?></div>
              </td>
              <td><div style="font-size: 13px; max-width: 200px; color: var(--admin-text-muted); line-height: 1.4;"><?php echo nl2br(htmlspecialchars($order['product_details'])); ?></div></td>
              <td style="font-weight:700; color:var(--admin-primary); font-size: 16px;">$<?php echo number_format($order['total_amount'], 2); ?></td>
              <td>
                <?php
                  $sClass = 'badge-warning';
                  if ($order['status'] === 'shipped') $sClass = 'badge-primary';
                  if ($order['status'] === 'delivered') $sClass = 'badge-success';
                  if ($order['status'] === 'cancelled') $sClass = 'badge-danger';
                ?>
                <span class="badge <?php echo $sClass; ?>">
                  <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                  <form method="POST" style="margin: 0; display: flex; gap: 6px;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                    
                    <?php if($order['status'] === 'pending'): ?>
                    <button type="submit" name="status" value="shipped" class="btn" style="background: rgba(52,152,219,0.1); color: #3498db; border: 1px solid rgba(52,152,219,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Ship Order</button>
                    <button type="submit" name="status" value="cancelled" class="btn" style="background: rgba(230,57,70,0.1); color: var(--admin-danger); border: 1px solid rgba(230,57,70,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Cancel</button>
                    <?php endif; ?>
                    
                    <?php if($order['status'] === 'shipped'): ?>
                    <button type="submit" name="status" value="delivered" class="btn" style="background: rgba(16,185,129,0.1); color: var(--admin-success); border: 1px solid rgba(16,185,129,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Mark Delivered</button>
                    <?php endif; ?>
                  </form>
                  
                  <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                    <button type="submit" class="action-btn" title="Delete" style="color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
            <tr><td colspan="6" style="text-align:center; padding: 20px;">No orders have been placed yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
