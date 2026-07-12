<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/constants.php';
require_once __DIR__ . '/../api/config/database.php';

// Only Super Admin can access SaaS control
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'super_admin') {
    header("Location: ../index.php");
    exit;
}

$db = (new Database())->getConnection();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_lock') {
        $is_locked = isset($_POST['is_locked']) ? 1 : 0;
        $lock_message = $_POST['lock_message'] ?? 'System disabled. Please contact the SaaS provider.';
        
        $stmt = $db->prepare("UPDATE system_settings SET is_locked = ?, lock_message = ?");
        $stmt->execute([$is_locked, $lock_message]);
        
        $success = "System status updated successfully!";
    }
}

// Fetch Current Settings
$stmt = $db->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $db->query("INSERT INTO system_settings (is_locked, lock_message) VALUES (0, 'System disabled.')");
    $settings = ['is_locked' => 0, 'lock_message' => 'System disabled.'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SaaS Control | Admin | <?php echo htmlspecialchars(APP_NAME); ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .form-group { margin-bottom: 15px; }
    .form-control { width: 100%; padding: 12px; border: 1px solid var(--admin-border); border-radius: 8px; font-family: inherit; font-size: 15px; }
    .toggle-container { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding: 16px; background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; }
    .toggle-container input[type="checkbox"] { width: 24px; height: 24px; cursor: pointer; }
    .pricing-card { background: var(--admin-bg); padding: 20px; border-radius: 12px; border: 1px solid var(--admin-border); margin-bottom: 16px; }
  </style>
</head>
<body class="admin-body">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="admin-content">
      <div class="page-title" style="margin-bottom: 24px;">
        <h1 style="font-family: var(--font-head); font-size: 24px; color: var(--admin-text); font-weight: 700;">SaaS Control Panel</h1>
        <div style="color: var(--admin-text-muted); font-size: 14px; margin-top: 4px;">Manage client subscription and system access.</div>
      </div>

      <?php if(isset($success)): ?>
        <div style="background: rgba(16,185,129,0.1); color: var(--admin-success); padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        
        <!-- Main Controls -->
        <div class="card-panel">
          <h2 style="font-family: var(--font-head); font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 20px;">System Suspension Status</h2>
          
          <form method="POST">
            <input type="hidden" name="action" value="update_lock">
            
            <div class="toggle-container">
              <input type="checkbox" name="is_locked" id="is_locked" <?php echo $settings['is_locked'] ? 'checked' : ''; ?>>
              <label for="is_locked" style="font-weight: 600; color: var(--admin-danger); font-size: 16px; cursor: pointer;">
                Lock Entire System
                <div style="font-size: 13px; color: var(--admin-text-muted); font-weight: 400; margin-top: 4px;">If checked, all users (except Super Admin) will be locked out immediately.</div>
              </label>
            </div>

            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-weight: 600;">Lock Message (Shown to clients when locked)</label>
              <textarea name="lock_message" class="form-control" rows="4" required><?php echo htmlspecialchars($settings['lock_message']); ?></textarea>
              <div style="font-size: 13px; color: var(--admin-text-muted); margin-top: 8px;">Example: "Your monthly subscription of $20 is due. Please contact support."</div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="padding: 14px 24px; justify-content: center; width: 100%; font-size: 16px;">Save Settings</button>
          </form>
        </div>

        <!-- Subscription Details -->
        <div>
          <div class="card-panel">
            <h2 style="font-family: var(--font-head); font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 20px;">Pricing Structure</h2>
            
            <div class="pricing-card">
              <div style="font-size: 13px; color: var(--admin-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Monthly Subscription</div>
              <div style="font-size: 28px; font-weight: 700; color: var(--admin-primary); margin: 8px 0;">$20<span style="font-size: 14px; font-weight: 400; color: var(--admin-text-muted);">/mo</span></div>
              <div style="font-size: 13px; color: var(--admin-text-muted);">Required to keep the system active.</div>
            </div>

            <div class="pricing-card">
              <div style="font-size: 13px; color: var(--admin-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Annual Activation Fee</div>
              <div style="font-size: 28px; font-weight: 700; color: var(--admin-text); margin: 8px 0;">$50<span style="font-size: 14px; font-weight: 400; color: var(--admin-text-muted);">/yr</span></div>
              <div style="font-size: 13px; color: var(--admin-text-muted);">One-time yearly server maintenance.</div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
