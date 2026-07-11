<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Handle POST actions for updating points
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_points') {
        $client_id = $_POST['client_id'] ?? 0;
        $points_change = (int)($_POST['points_change'] ?? 0);
        $operation = $_POST['operation'] ?? 'add'; // 'add' or 'deduct'
        
        if ($operation === 'deduct') {
            $points_change = -$points_change;
        }

        // Fetch current points to ensure we don't go below 0 if deducting
        $stmt = $db->prepare("SELECT points FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $current_points = $stmt->fetchColumn() ?: 0;
        
        $new_points = $current_points + $points_change;
        if ($new_points < 0) $new_points = 0; // Prevent negative points

        $stmt = $db->prepare("UPDATE clients SET points = ? WHERE id = ?");
        if ($stmt->execute([$new_points, $client_id])) {
            $message = "<div style='color: green; margin-bottom:15px; padding:10px; background:#e8f8f5; border-radius:6px;'>Points updated successfully! New Balance: {$new_points}</div>";
        }
    } elseif ($action === 'delete') {
        $client_id = $_POST['client_id'] ?? 0;
        try {
            $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->execute([$client_id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Client deleted successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all clients
$stmt = $db->query("SELECT * FROM clients ORDER BY created_at DESC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getTier($points) {
    if ($points > 2000) return "VIP";
    if ($points > 500) return "Gold";
    return "Silver";
}

function getTierColor($tier) {
    if ($tier === "VIP") return "background: #000; color: #d4af37;";
    if ($tier === "Gold") return "background: #fcf3cf; color: #f1c40f;";
    return "background: #f2f3f4; color: #7f8c8d;";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Loyalty | Admin | 33° NORTH</title>
  
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
          <h1 class="page-title">Loyalty & Rewards</h1>
          <div class="page-subtitle">Manage client points and tier status.</div>
        </div>
      </div>

      <?php echo $message; ?>

      <!-- Summary Stats -->
      <?php
        $totalPts  = array_sum(array_column($clients, 'points'));
        $vipCount  = count(array_filter($clients, fn($c) => $c['points'] > 2000));
        $goldCount = count(array_filter($clients, fn($c) => $c['points'] > 500 && $c['points'] <= 2000));
        $silverCount = count($clients) - $vipCount - $goldCount;
      ?>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-title">Total Clients</div>
          <div class="stat-card-value"><?php echo count($clients); ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Total Points</div>
          <div class="stat-card-value" style="color: var(--admin-primary);"><?php echo number_format($totalPts); ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">VIP Tier</div>
          <div class="stat-card-value" style="color: var(--admin-primary);"><?php echo $vipCount; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Gold Tier</div>
          <div class="stat-card-value"><?php echo $goldCount; ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Silver Tier</div>
          <div class="stat-card-value"><?php echo $silverCount; ?></div>
        </div>
      </div>

      <div class="card-panel" style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 25%;">Client Name</th>
              <th style="width: 25%;">Contact Info</th>
              <th style="width: 15%;">Points Balance</th>
              <th style="width: 10%;">Current Tier</th>
              <th style="width: 25%;">Adjust Points & Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $client): ?>
            <?php 
              $tier = getTier($client['points']);
              $tierColor = getTierColor($tier);
            ?>
            <tr>
              <td>
                <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($client['name']); ?></div>
                <div style="font-size: 13px; color: var(--admin-text-muted); margin-top: 2px;">Joined: <?php echo date('M Y', strtotime($client['created_at'])); ?></div>
              </td>
              <td>
                <div style="font-size: 14px; color: var(--admin-text); font-weight: 500; margin-bottom: 2px;"><?php echo htmlspecialchars($client['phone']); ?></div>
                <div style="font-size: 13px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($client['email']); ?></div>
              </td>
              <td>
                <div style="font-family: var(--font-head); font-size: 20px; font-weight: 700; color: var(--admin-primary);">
                  <?php echo $client['points']; ?> <span style="font-size: 14px; font-weight: 600; color: var(--admin-text-muted);">pts</span>
                </div>
              </td>
              <td>
                <span class="badge" style="padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; <?php echo $tierColor; ?>">
                  <?php echo $tier; ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: nowrap;">
                  <form method="POST" style="display: flex; gap: 6px; align-items: center; margin: 0;">
                    <input type="hidden" name="action" value="update_points">
                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                    <select name="operation" class="form-control" style="width: auto; padding: 6px 10px; font-size: 14px; cursor: pointer;">
                      <option value="add">+</option>
                      <option value="deduct">-</option>
                    </select>
                    <input type="number" name="points_change" class="form-control" style="width: 65px; padding: 6px 10px; font-size: 14px;" placeholder="Qty" min="1" required>
                    <button type="submit" class="btn btn-primary" title="Save Points" style="padding: 6px 12px; font-size: 13px;">
                      Save
                    </button>
                  </form>
                  
                  <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                    <button type="submit" class="action-btn" title="Delete Client" style="color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($clients)): ?>
            <tr><td colspan="5" style="text-align:center; padding: 40px 20px; color: var(--admin-text-muted);">No registered clients found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
