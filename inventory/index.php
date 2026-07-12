<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $stock = $_POST['stock'] ?? 0;
        $price = $_POST['price'] ?? 0;
        
        try {
            // First check if stock column exists by querying one row
            $colCheck = $db->query("SELECT * FROM products LIMIT 1");
            $hasStock = false;
            if ($colCheck) {
                $row = $colCheck->fetch(PDO::FETCH_ASSOC);
                if ($row !== false && array_key_exists('stock', $row)) {
                    $hasStock = true;
                } elseif ($row !== false && array_key_exists('stock_quantity', $row)) {
                    // It uses stock_quantity
                    $stmt = $db->prepare("INSERT INTO products (name, category, stock_quantity, price, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$name, $category, $stock, $price]);
                    $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Product added successfully!</div>";
                    $action = 'done';
                }
            }
            
            if ($action !== 'done') {
                if ($hasStock) {
                    $stmt = $db->prepare("INSERT INTO products (name, category, stock, price, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$name, $category, $stock, $price]);
                } else {
                    $stmt = $db->prepare("INSERT INTO products (name, category, price, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$name, $category, $price]);
                }
                $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Product added successfully!</div>";
            }
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Product removed successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif ($action === 'toggle_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 0;
        try {
            $stmt = $db->prepare("UPDATE products SET is_active = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Product status updated!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all products
try {
    $stmt = $db->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    $message = "<div style='color: red; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Database Error: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Inventory | Admin | Caraway</title>
  
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
          <h1 class="page-title">Inventory & Products</h1>
          <div class="page-subtitle">Track product stock, prices, and categories.</div>
        </div>
      </div>
      
      <?php if (!empty($message)) echo $message; ?>

      <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        <div class="card-panel" style="flex: 2; min-width: 300px;">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 25%;">Product Name</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 15%;">Stock</th>
                <th style="width: 15%;">Price</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%; text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): ?>
              <tr>
                <td>
                  <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($product['name'] ?? 'Unknown'); ?></div>
                </td>
                <td style="font-size: 14px; color: var(--admin-text-muted); text-transform: capitalize;"><?php echo htmlspecialchars($product['category'] ?? '-'); ?></td>
                <td>
                  <span style="font-weight: 600; color: <?php echo (($product['stock'] ?? $product['stock_quantity'] ?? 0) > 0) ? 'var(--admin-text)' : 'var(--admin-danger)'; ?>;">
                    <?php echo htmlspecialchars($product['stock'] ?? $product['stock_quantity'] ?? 0); ?> <small style="font-weight: 400; color: var(--admin-text-muted);">units</small>
                  </span>
                </td>
                <td>
                  <div style="font-family: var(--font-head); font-size: 16px; font-weight: 700; color: var(--admin-primary);">
                    $<?php echo number_format($product['price'] ?? 0, 2); ?>
                  </div>
                </td>
                <td>
                  <span class="badge <?php echo (!empty($product['is_active'])) ? 'badge-success' : 'badge-warning'; ?>" style="border-radius: 20px; padding: 6px 12px;">
                    <?php echo (!empty($product['is_active'])) ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                    <form method="POST" style="margin: 0;">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                      <input type="hidden" name="status" value="<?php echo (!empty($product['is_active'])) ? 0 : 1; ?>">
                      <button type="submit" class="action-btn" title="<?php echo (!empty($product['is_active'])) ? 'Deactivate' : 'Activate'; ?>" style="color: <?php echo (!empty($product['is_active'])) ? 'var(--admin-warning)' : 'var(--admin-success)'; ?>; background: <?php echo (!empty($product['is_active'])) ? 'rgba(245,158,11,0.05)' : 'rgba(16,185,129,0.05)'; ?>; border: 1px solid <?php echo (!empty($product['is_active'])) ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.1)'; ?>;">
                        <?php if(!empty($product['is_active'])): ?>
                          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        <?php else: ?>
                          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        <?php endif; ?>
                      </button>
                    </form>
                    
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                      <button type="submit" class="action-btn" title="Delete" style="color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($products)): ?>
              <tr><td colspan="6" style="text-align:center; padding: 40px 20px; color: var(--admin-text-muted);">No products found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="card-panel" style="flex: 1; min-width: 300px; padding: 24px;">
          <h2 style="margin-top: 0; font-size: 1.2rem; margin-bottom: 20px; font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            Add Product
          </h2>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label>Product Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Category / Brand</label>
              <input type="text" name="category" class="form-control" placeholder="e.g. Hair Care" required>
            </div>
            <div class="form-group">
              <label>Stock Quantity</label>
              <input type="number" name="stock" class="form-control" min="0" value="0">
            </div>
            <div class="form-group">
              <label>Price ($)</label>
              <input type="number" step="0.01" name="price" class="form-control" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Save Product</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
</body>
</html>
