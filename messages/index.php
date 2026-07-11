<?php 
require_once __DIR__ . '/../includes/auth.php'; 
require_once __DIR__ . '/../../api/config/constants.php';
require_once __DIR__ . '/../../api/config/database.php';

$message_out = "";
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = $_POST['id'] ?? 0;
        
        if ($action === 'update_status') {
            $status = $_POST['status'] ?? 'read';
            $stmt = $db->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $id])) {
                $message_out = "<div style='color: green; margin-bottom:15px; padding:10px; background:#e8f8f5; border-radius:6px;'>Message marked as " . htmlspecialchars($status) . ".</div>";
            }
        } elseif ($action === 'delete') {
            try {
                $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$id]);
                $message_out = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Message deleted successfully!</div>";
            } catch (PDOException $e) {
                $message_out = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }

    if ($db) {
        $query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
        $stmt = $db->query($query);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $messages = [];
        $error = "Database connection failed.";
    }
} catch (PDOException $e) {
    $messages = [];
    $error = "Error fetching messages: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Messages | Admin | 33° NORTH</title>
  
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
          <h1 class="page-title">Contact Messages</h1>
          <div class="page-subtitle">View inquiries from the contact form.</div>
        </div>
      </div>

      <?php if (!empty($message_out)): ?>
        <?php echo $message_out; ?>
      <?php endif; ?>

      <?php if (isset($error)): ?>
        <div style="color: red; margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <div class="card-panel">
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($messages) > 0): ?>
              <?php foreach ($messages as $msg): ?>
              <tr>
                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                <td><?php echo nl2br(htmlspecialchars(substr($msg['message'], 0, 50) . (strlen($msg['message']) > 50 ? '...' : ''))); ?></td>
                <td><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></td>
                <td>
                  <?php 
                    $status_class = $msg['status'] === 'unread' ? 'badge-danger' : 'badge-success';
                  ?>
                  <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($msg['status']); ?></span>
                </td>
                <td>
                  <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                    <?php if($msg['status'] === 'unread'): ?>
                    <button type="submit" name="status" value="read" style="background:#3498db; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">Mark Read</button>
                    <?php endif; ?>
                  </form>
                  <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this message?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                    <button type="submit" style="background:#e74c3c; color:#fff; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" style="text-align: center; padding: 20px;">No messages found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
</body>
</html>
