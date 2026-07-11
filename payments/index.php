<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Ensure table exists just in case
try {
    $db->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NULL,
        client_name VARCHAR(100),
        amount DECIMAL(10,2),
        payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
        status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? 0;

    if ($action === 'update_status') {
        $status = $_POST['status'] ?? 'pending';
        $stmt = $db->prepare("UPDATE payments SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $message = "<div class='admin-toast show admin-toast-success' style='border-left-color: var(--admin-success);'><div class='admin-toast-icon'>✅</div><div class='admin-toast-msg'>Payment marked as " . htmlspecialchars($status) . ".</div></div>";
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $db->prepare("DELETE FROM payments WHERE id = ?");
            $stmt->execute([$id]);
            $message = "<div class='admin-toast show admin-toast-success' style='border-left-color: var(--admin-success);'><div class='admin-toast-icon'>✅</div><div class='admin-toast-msg'>Record deleted.</div></div>";
        } catch (PDOException $e) {
            $message = "<div class='admin-toast show admin-toast-danger' style='border-left-color: var(--admin-danger);'><div class='admin-toast-icon'>❌</div><div class='admin-toast-msg'>" . $e->getMessage() . "</div></div>";
        }
    }
}

// Fetch Stats
$stmtTotal = $db->query("SELECT SUM(amount) as total FROM payments WHERE status = 'paid'");
$totalRevenue = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$stmtPending = $db->query("SELECT SUM(amount) as pending FROM payments WHERE status = 'pending'");
$totalPending = $stmtPending->fetch(PDO::FETCH_ASSOC)['pending'] ?? 0;

// Fetch payments
$stmt = $db->query("SELECT * FROM payments ORDER BY created_at DESC");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payments | Admin | 33° NORTH</title>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content">
      <div class="page-header" style="margin-bottom: 24px;">
        <div>
          <h1 class="page-title">Payments & Billing</h1>
          <div class="page-subtitle">Track revenue and manage pending orders.</div>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card">
          <div class="stat-card-title">Total Revenue</div>
          <div class="stat-card-value" style="color: var(--admin-success);">$<?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Pending Collection</div>
          <div class="stat-card-value" style="color: var(--admin-warning);">$<?php echo number_format($totalPending, 2); ?></div>
        </div>
      </div>

      <div class="card-panel">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID / Date</th>
              <th>Client</th>
              <th>Method</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $payment): ?>
            <tr>
              <td>
                <strong style="color: var(--admin-text);">#<?php echo str_pad($payment['id'], 4, '0', STR_PAD_LEFT); ?></strong><br>
                <small style="color: var(--admin-text-muted);"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></small>
                <?php if($payment['order_id']): ?><div style="font-size:11px; color: var(--admin-primary); margin-top: 4px;">Order #<?php echo $payment['order_id']; ?></div><?php endif; ?>
              </td>
              <td><strong style="color: var(--admin-text);"><?php echo htmlspecialchars($payment['client_name']); ?></strong></td>
              <td><span style="font-size:13px; padding: 4px 8px; background: rgba(255,255,255,0.05); border-radius: 4px; border: 1px solid var(--admin-border); color: var(--admin-text);"><?php echo htmlspecialchars($payment['payment_method']); ?></span></td>
              <td style="font-weight:700; font-size: 15px; color: var(--admin-text);">$<?php echo number_format($payment['amount'], 2); ?></td>
              <td>
                <?php
                  $sClass = 'badge-warning';
                  if ($payment['status'] === 'paid') $sClass = 'badge-success';
                  if ($payment['status'] === 'refunded') $sClass = 'badge-danger';
                ?>
                <span class="badge <?php echo $sClass; ?>">
                  <?php echo htmlspecialchars(ucfirst($payment['status'])); ?>
                </span>
              </td>
              <td>
                <form method="POST" style="display:inline-block; margin-right: 5px;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?php echo $payment['id']; ?>">
                  
                  <?php if($payment['status'] === 'pending'): ?>
                  <button type="submit" name="status" value="paid" class="btn" style="background: rgba(16,185,129,0.1); color: var(--admin-success); border: 1px solid rgba(16,185,129,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Mark Paid</button>
                  <?php endif; ?>
                  
                  <?php if($payment['status'] === 'paid'): ?>
                  <button type="submit" name="status" value="refunded" class="btn" style="background: rgba(245,158,11,0.1); color: var(--admin-warning); border: 1px solid rgba(245,158,11,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Refund</button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($payments)): ?>
            <tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--admin-text-muted);">No payment records found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  
  <?php if($message): ?>
  <div class="admin-toast-container">
    <?php echo $message; ?>
  </div>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      let toast = document.querySelector('.admin-toast');
      if(toast) {
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }
    });
  </script>
</body>
</html>
