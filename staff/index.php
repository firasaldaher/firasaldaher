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
        $role = $_POST['role'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        
        $stmt = $db->prepare("INSERT INTO staff (name, role, phone, email) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $role, $phone, $email])) {
            $message = "<div style='color: green; margin-bottom:15px;'>Staff member added successfully!</div>";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM staff WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "<div style='color: green; margin-bottom:15px;'>Staff member removed successfully!</div>";
        }
    } elseif ($action === 'toggle_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 0;
        $stmt = $db->prepare("UPDATE staff SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $message = "<div style='color: green; margin-bottom:15px;'>Staff status updated!</div>";
    }
}

// Fetch all staff
$stmt = $db->query("SELECT * FROM staff ORDER BY created_at DESC");
$staffMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Staff | Admin | 33° NORTH</title>
  
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
          <h1 class="page-title">Staff Management</h1>
          <div class="page-subtitle">Manage team members and their status.</div>
        </div>
      </div>

      <?php echo $message; ?>

      <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        
        <!-- Staff List -->
        <div class="card-panel" style="flex: 2; min-width: 300px;">
          <div class="card-panel-header">
            <div class="card-panel-title">Team Directory</div>
          </div>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 25%;">Name</th>
                  <th style="width: 20%;">Role</th>
                  <th style="width: 25%;">Contact</th>
                  <th style="width: 15%;">Status</th>
                  <th style="width: 15%; text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($staffMembers as $staff): ?>
                <tr>
                  <td>
                    <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($staff['name']); ?></div>
                  </td>
                  <td style="font-size: 14px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($staff['role']); ?></td>
                  <td>
                    <div style="font-size: 14px; color: var(--admin-text); font-weight: 500; margin-bottom: 2px;"><?php echo htmlspecialchars($staff['phone'] ?? '-'); ?></div>
                    <div style="font-size: 13px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($staff['email'] ?? '-'); ?></div>
                  </td>
                  <td>
                    <span class="badge <?php echo $staff['is_active'] ? 'badge-success' : 'badge-warning'; ?>" style="border-radius: 20px; padding: 6px 12px;">
                      <?php echo $staff['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                  </td>
                  <td>
                    <div style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                      <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                        <input type="hidden" name="status" value="<?php echo $staff['is_active'] ? 0 : 1; ?>">
                        <button type="submit" class="action-btn" title="<?php echo $staff['is_active'] ? 'Deactivate' : 'Activate'; ?>" style="color: <?php echo $staff['is_active'] ? 'var(--admin-warning)' : 'var(--admin-success)'; ?>; background: <?php echo $staff['is_active'] ? 'rgba(245,158,11,0.05)' : 'rgba(16,185,129,0.05)'; ?>; border: 1px solid <?php echo $staff['is_active'] ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.1)'; ?>;">
                          <?php if($staff['is_active']): ?>
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                          <?php else: ?>
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                          <?php endif; ?>
                        </button>
                      </form>
                      
                      <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to remove this staff member?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                        <button type="submit" class="action-btn" title="Delete" style="color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($staffMembers)): ?>
                <tr><td colspan="5" style="text-align:center; padding: 40px 20px; color: var(--admin-text-muted);">No staff members found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Add Staff Form -->
        <div class="card-panel" style="flex: 1; min-width: 300px; padding: 24px;">
          <h2 style="margin-top: 0; font-size: 1.2rem; margin-bottom: 20px; font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            Add New Staff
          </h2>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Role / Position</label>
              <input type="text" name="role" class="form-control" placeholder="e.g. Senior Barber" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Add Staff Member</button>
          </form>
        </div>

      </div>
    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
