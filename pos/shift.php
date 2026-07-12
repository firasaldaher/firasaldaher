<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/constants.php';
require_once __DIR__ . '/../api/config/database.php';

$database = new Database();
$db = $database->getConnection();

$cashier_id = $_SESSION['admin_id'];

// Get the last shift closing time for this user, or start of today if none
$stmt = $db->prepare("SELECT created_at FROM shift_closings WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$cashier_id]);
$last_closing = $stmt->fetchColumn();

if (!$last_closing) {
    $last_closing = date('Y-m-d 00:00:00');
}

// Calculate Expected Cash since last closing
$stmtCash = $db->prepare("
    SELECT COALESCE(SUM(total_amount), 0) 
    FROM orders 
    WHERE cashier_id = ? AND status = 'completed' AND created_at > ?
");
$stmtCash->execute([$cashier_id, $last_closing]);
$expected_cash = $stmtCash->fetchColumn();

// Get items sold since last closing for inventory check
$stmtItems = $db->prepare("
    SELECT p.name, SUM(oi.quantity) as qty_sold, SUM(oi.price * oi.quantity) as total_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.cashier_id = ? AND o.status = 'completed' AND o.created_at > ?
    GROUP BY p.id
    ORDER BY qty_sold DESC
");
$stmtItems->execute([$cashier_id, $last_closing]);
$sold_items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>End of Shift | <?php echo htmlspecialchars(APP_NAME); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .shift-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media (max-width: 768px) { .shift-grid { grid-template-columns: 1fr; } }
    .stat-box { background: var(--admin-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--admin-border); text-align: center; }
    .stat-value { font-size: 32px; font-weight: 700; color: var(--admin-primary); margin-top: 8px; font-family: var(--font-head); }
    .form-group { margin-bottom: 15px; }
    .form-control { width: 100%; padding: 12px; border: 1px solid var(--admin-border); border-radius: 8px; font-family: inherit; font-size: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--admin-border); }
    th { color: var(--admin-text-muted); font-weight: 600; font-size: 14px; }
    td { color: var(--admin-text); font-size: 14px; }
  </style>
</head>
<body class="admin-body">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="admin-content">
      <div class="page-title" style="margin-bottom: 24px;">
        <h1 style="font-family: var(--font-head); font-size: 24px; color: var(--admin-text); font-weight: 700;">End of Shift / Cash Reconciliation</h1>
        <div style="color: var(--admin-text-muted); font-size: 14px; margin-top: 4px;">Compare physical cash and check sold items.</div>
      </div>

      <?php if(isset($_GET['success'])): ?>
        <div style="background: rgba(16,185,129,0.1); color: var(--admin-success); padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
          Shift closed successfully. All data recorded.
        </div>
      <?php endif; ?>

      <div class="shift-grid">
        <!-- Shift Closure Form -->
        <div class="card-panel">
          <h2 style="font-family: var(--font-head); font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 24px;">Drawer Reconciliation</h2>
          
          <div class="stat-box" style="margin-bottom: 24px;">
            <div style="color: var(--admin-text-muted); font-size: 14px;">System Expected Cash</div>
            <div class="stat-value">$<?php echo number_format($expected_cash, 2); ?></div>
            <div style="font-size: 12px; color: var(--admin-text-muted); margin-top: 4px;">Since last closing (<?php echo date('h:i A', strtotime($last_closing)); ?>)</div>
          </div>

          <form action="action_shift.php" method="POST">
            <input type="hidden" name="expected_cash" value="<?php echo $expected_cash; ?>">
            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-weight: 600;">Physical Cash in Drawer (Actual Cash)</label>
              <input type="number" step="0.01" name="actual_cash" class="form-control" placeholder="Enter counted cash..." required>
            </div>
            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-weight: 600;">Notes (Optional)</label>
              <textarea name="notes" class="form-control" rows="3" placeholder="Explain any shortage or overage..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 16px;" onclick="return confirm('Are you sure you want to close this shift?');">Close Shift & Reconcile</button>
          </form>
        </div>

        <!-- Inventory Sold -->
        <div class="card-panel">
          <h2 style="font-family: var(--font-head); font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">Products Sold (Current Shift)</h2>
          <div style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th style="text-align: center;">Qty Sold</th>
                  <th style="text-align: right;">Total Sales</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($sold_items as $item): ?>
                <tr>
                  <td style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></td>
                  <td style="text-align: center;">
                    <span style="background: rgba(16,185,129,0.1); color: var(--admin-success); padding: 4px 12px; border-radius: 20px; font-weight: 600;"><?php echo $item['qty_sold']; ?></span>
                  </td>
                  <td style="text-align: right; font-weight: 600; color: var(--admin-primary);">$<?php echo number_format($item['total_sales'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($sold_items)): ?>
                <tr>
                  <td colspan="3" style="text-align: center; color: var(--admin-text-muted); padding: 24px;">No products sold in this shift yet.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
</body>
</html>
