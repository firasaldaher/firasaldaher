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
        $stmt = $db->prepare("UPDATE academy_enrollments SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $message = "<div style='color: green; margin-bottom:15px; padding:10px; background:#e8f8f5; border-radius:6px;'>Application status updated to " . htmlspecialchars($status) . ".</div>";
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $db->prepare("DELETE FROM academy_enrollments WHERE id = ?");
            $stmt->execute([$id]);
            $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Application deleted successfully!</div>";
        } catch (PDOException $e) {
            $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all enrollments
$stmt = $db->query("SELECT * FROM academy_enrollments ORDER BY created_at DESC");
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Academy | Admin | 33° NORTH</title>
  
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
          <h1 class="page-title">Academy Applications</h1>
          <div class="page-subtitle">Review student enrollment requests.</div>
        </div>
      </div>

      <?php echo $message; ?>

      <div class="card-panel" style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Applicant Name</th>
              <th>Contact Info</th>
              <th>Course Applied</th>
              <th>Date Applied</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($enrollments as $req): ?>
            <tr>
              <td><div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($req['name']); ?></div></td>
              <td>
                <div style="font-size: 14px; font-weight: 500; color: var(--admin-text); margin-bottom: 2px;"><?php echo htmlspecialchars($req['phone']); ?></div>
                <div style="font-size: 13px; color: var(--admin-text-muted);"><?php echo htmlspecialchars($req['email']); ?></div>
              </td>
              <td><div style="font-size: 14px; color: var(--admin-text);"><?php echo htmlspecialchars($req['course_name']); ?></div></td>
              <td style="font-size: 14px; color: var(--admin-text-muted);"><?php echo date('M d, Y h:i A', strtotime($req['created_at'])); ?></td>
              <td>
                <?php
                  $sClass = 'badge-warning';
                  if ($req['status'] === 'approved') $sClass = 'badge-success';
                  if ($req['status'] === 'rejected') $sClass = 'badge-danger';
                ?>
                <span class="badge <?php echo $sClass; ?>">
                  <?php echo htmlspecialchars(ucfirst($req['status'])); ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                  <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                    
                    <?php if($req['status'] !== 'approved'): ?>
                    <button type="submit" name="status" value="approved" class="btn" style="background: rgba(16,185,129,0.1); color: var(--admin-success); border: 1px solid rgba(16,185,129,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Approve</button>
                    <?php endif; ?>
                    
                    <?php if($req['status'] !== 'rejected'): ?>
                    <button type="submit" name="status" value="rejected" class="btn" style="background: rgba(230,57,70,0.1); color: var(--admin-danger); border: 1px solid rgba(230,57,70,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Reject</button>
                    <?php endif; ?>
                    
                    <?php if($req['status'] !== 'pending'): ?>
                    <button type="submit" name="status" value="pending" class="btn" style="background: rgba(245,158,11,0.1); color: var(--admin-warning); border: 1px solid rgba(245,158,11,0.2); padding: 4px 10px; font-size: 12px; height: auto;">Pending</button>
                    <?php endif; ?>
                  </form>
                  
                  <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this application?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                    <button type="submit" class="action-btn" title="Delete" style="color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($enrollments)): ?>
            <tr><td colspan="6" style="text-align:center; padding: 20px;">No academy applications received yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
