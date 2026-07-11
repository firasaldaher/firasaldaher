<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $duration = $_POST['duration'] ?? '';
        $price = $_POST['price'] ?? 0;
        
        try {
            $stmt = $db->prepare("INSERT INTO services (name, category, duration, price, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$name, $category, $duration, $price]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Service added successfully!</div>";
        } catch (PDOException $e) {
            // Attempt to insert without duration if it fails (schema mismatch fallback)
            try {
                $stmt = $db->prepare("INSERT INTO services (name, category, price, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute([$name, $category, $price]);
                $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Service added successfully!</div>";
            } catch (PDOException $e2) {
                $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Service removed successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif ($action === 'toggle_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 0;
        try {
            $stmt = $db->prepare("UPDATE services SET is_active = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Service status updated!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all services
try {
    $stmt = $db->query("SELECT * FROM services ORDER BY id DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $services = [];
    $message = "<div style='color: red; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Database Error: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Services | Admin | 33° NORTH</title>
  
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
          <h1 class="page-title">Services Portfolio</h1>
          <div class="page-subtitle">Manage salon services, pricing, and duration.</div>
        </div>
      </div>
      
      <?php if (!empty($message)) echo $message; ?>

      <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        <div class="card-panel" style="flex: 2; min-width: 300px;">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 25%;">Service Name</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 15%;">Duration</th>
                <th style="width: 15%;">Price</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%; text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($services as $service): ?>
              <tr>
                <td>
                  <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($service['name'] ?? 'Unknown'); ?></div>
                </td>
                <td style="font-size: 14px; color: var(--admin-text-muted); text-transform: capitalize;"><?php echo htmlspecialchars($service['category'] ?? '-'); ?></td>
                <td style="font-size: 14px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($service['duration'] ?? '-'); ?> <small>min</small></td>
                <td>
                  <div style="font-family: var(--font-head); font-size: 16px; font-weight: 700; color: var(--admin-primary);">
                    $<?php echo number_format($service['price'] ?? 0, 2); ?>
                  </div>
                </td>
                <td>
                  <span class="badge <?php echo (!empty($service['is_active'])) ? 'badge-success' : 'badge-warning'; ?>" style="border-radius: 20px; padding: 6px 12px;">
                    <?php echo (!empty($service['is_active'])) ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                    <form method="POST" style="margin: 0;">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                      <input type="hidden" name="status" value="<?php echo (!empty($service['is_active'])) ? 0 : 1; ?>">
                      <button type="submit" class="action-btn" title="<?php echo (!empty($service['is_active'])) ? 'Deactivate' : 'Activate'; ?>" style="color: <?php echo (!empty($service['is_active'])) ? 'var(--admin-warning)' : 'var(--admin-success)'; ?>; background: <?php echo (!empty($service['is_active'])) ? 'rgba(245,158,11,0.05)' : 'rgba(16,185,129,0.05)'; ?>; border: 1px solid <?php echo (!empty($service['is_active'])) ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.1)'; ?>;">
                        <?php if(!empty($service['is_active'])): ?>
                          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        <?php else: ?>
                          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                        <?php endif; ?>
                      </button>
                    </form>
                    
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this service?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                      <button type="submit" class="action-btn" title="Delete" style="color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($services)): ?>
              <tr><td colspan="6" style="text-align:center; padding: 40px 20px; color: var(--admin-text-muted);">No services found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="card-panel" style="flex: 1; min-width: 300px; padding: 24px;">
          <h2 style="margin-top: 0; font-size: 1.2rem; margin-bottom: 20px; font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            Add Service
          </h2>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label>Service Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Category (e.g., men, women)</label>
              <input type="text" name="category" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Duration (e.g., 45 min)</label>
              <input type="text" name="duration" class="form-control">
            </div>
            <div class="form-group">
              <label>Price ($)</label>
              <input type="number" step="0.01" name="price" class="form-control" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Save Service</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
</body>
</html>
