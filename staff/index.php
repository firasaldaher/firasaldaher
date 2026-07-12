<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/constants.php';
require_once __DIR__ . '/../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Check if user is super_admin or admin
// Optional: If you only want super_admin to manage staff, you can add a check here.

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role = $_POST['role'] ?? 'cashier';
        
        if (!empty($username) && !empty($password) && !empty($full_name)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $stmt = $db->prepare("INSERT INTO users (username, password, full_name, role, status) VALUES (?, ?, ?, ?, 'active')");
                if ($stmt->execute([$username, $hashed_password, $full_name, $role])) {
                    $message = "<div style='background: rgba(16,185,129,0.1); color: var(--admin-success); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Staff member added successfully!</div>";
                }
            } catch(PDOException $e) {
                // If username is duplicate
                if ($e->getCode() == 23000) {
                    $message = "<div style='background: rgba(239,68,68,0.1); color: var(--admin-danger); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Username already exists. Please choose another.</div>";
                } else {
                    $message = "<div style='background: rgba(239,68,68,0.1); color: var(--admin-danger); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
        } else {
            $message = "<div style='background: rgba(239,68,68,0.1); color: var(--admin-danger); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Please fill all required fields.</div>";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        if ($id != $_SESSION['admin_id']) { // Prevent deleting oneself
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = "<div style='background: rgba(16,185,129,0.1); color: var(--admin-success); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Staff member removed successfully!</div>";
            }
        } else {
            $message = "<div style='background: rgba(239,68,68,0.1); color: var(--admin-danger); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>You cannot delete your own account.</div>";
        }
    } elseif ($action === 'toggle_status') {
        $id = $_POST['id'] ?? 0;
        $status = $_POST['status'] ?? 'active';
        
        if ($id != $_SESSION['admin_id']) { // Prevent deactivating oneself
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $message = "<div style='background: rgba(16,185,129,0.1); color: var(--admin-success); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>Staff status updated!</div>";
        } else {
            $message = "<div style='background: rgba(239,68,68,0.1); color: var(--admin-danger); padding: 12px; border-radius: 8px; margin-bottom: 20px;'>You cannot deactivate your own account.</div>";
        }
    }
}

// Fetch all staff (users)
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$staffMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Staff Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .form-group { margin-bottom: 15px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid var(--admin-border); border-radius: 8px; font-family: inherit; }
  </style>
</head>
<body class="admin-body">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="admin-content">
      <div class="page-title" style="margin-bottom: 24px;">
        <h1 style="font-family: var(--font-head); font-size: 24px; color: var(--admin-text); font-weight: 700;">Staff Management</h1>
        <div style="color: var(--admin-text-muted); font-size: 14px; margin-top: 4px;">Manage team members and system access.</div>
      </div>

      <?php echo $message; ?>

      <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        
        <!-- Staff List -->
        <div class="card-panel" style="flex: 2; min-width: 300px;">
          <h2 style="font-family: var(--font-head); font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">Team Directory</h2>
          <div style="overflow-x:auto;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid var(--admin-border); color: var(--admin-text-muted); font-size: 14px;">Full Name</th>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid var(--admin-border); color: var(--admin-text-muted); font-size: 14px;">Username</th>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid var(--admin-border); color: var(--admin-text-muted); font-size: 14px;">Role</th>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid var(--admin-border); color: var(--admin-text-muted); font-size: 14px;">Status</th>
                  <th style="padding: 12px; text-align: center; border-bottom: 1px solid var(--admin-border); color: var(--admin-text-muted); font-size: 14px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($staffMembers as $staff): ?>
                <tr>
                  <td style="padding: 12px; border-bottom: 1px solid var(--admin-border);">
                    <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($staff['full_name']); ?></div>
                  </td>
                  <td style="padding: 12px; border-bottom: 1px solid var(--admin-border); font-size: 14px; color: var(--admin-text-muted);">
                    <?php echo htmlspecialchars($staff['username']); ?>
                  </td>
                  <td style="padding: 12px; border-bottom: 1px solid var(--admin-border); font-size: 14px;">
                    <span style="background: var(--admin-bg); padding: 4px 8px; border-radius: 12px; font-size: 12px; color: var(--admin-text-muted);">
                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $staff['role']))); ?>
                    </span>
                  </td>
                  <td style="padding: 12px; border-bottom: 1px solid var(--admin-border);">
                    <?php if($staff['status'] === 'active'): ?>
                        <span style="color: var(--admin-success); font-weight: 600; font-size: 13px;">Active</span>
                    <?php else: ?>
                        <span style="color: var(--admin-danger); font-weight: 600; font-size: 13px;">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 12px; border-bottom: 1px solid var(--admin-border);">
                    <div style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                      <?php if($staff['id'] != $_SESSION['admin_id']): ?>
                      <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                        <input type="hidden" name="status" value="<?php echo $staff['status'] === 'active' ? 'inactive' : 'active'; ?>">
                        <button type="submit" title="<?php echo $staff['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; border: none; font-size: 12px; font-weight: bold; background: <?php echo $staff['status'] === 'active' ? 'rgba(245,158,11,0.1)' : 'rgba(16,185,129,0.1)'; ?>; color: <?php echo $staff['status'] === 'active' ? 'var(--admin-warning)' : 'var(--admin-success)'; ?>;">
                            <?php echo $staff['status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                        </button>
                      </form>
                      
                      <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to remove this staff member permanently?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $staff['id']; ?>">
                        <button type="submit" title="Delete" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; border: none; font-size: 12px; font-weight: bold; background: rgba(239,68,68,0.1); color: var(--admin-danger);">
                          Delete
                        </button>
                      </form>
                      <?php else: ?>
                        <span style="font-size: 12px; color: var(--admin-text-muted);">You</span>
                      <?php endif; ?>
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
        <div class="card-panel" style="flex: 1; min-width: 300px;">
          <h2 style="font-family: var(--font-head); font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">Add New User</h2>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">Full Name</label>
              <input type="text" name="full_name" class="form-control" placeholder="e.g. John Doe" required>
            </div>
            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">System Role</label>
              <select name="role" class="form-control" required>
                  <option value="cashier">Cashier</option>
                  <option value="admin">Admin</option>
                  <option value="super_admin">Super Admin</option>
              </select>
            </div>
            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">Login Username</label>
              <input type="text" name="username" class="form-control" placeholder="e.g. john_cashier" required>
            </div>
            <div class="form-group">
              <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">Login Password</label>
              <input type="password" name="password" class="form-control" placeholder="Enter a secure password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 8px;">Create User</button>
          </form>
        </div>

      </div>
    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
