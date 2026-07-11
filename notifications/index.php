<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../api/config/database.php';

$db = (new Database())->getConnection();

// Fetch all registered devices with their associated client info (if any)
try {
    $query = "SELECT d.token, d.created_at, c.name, c.email 
              FROM device_tokens d 
              LEFT JOIN clients c ON d.client_id = c.id 
              ORDER BY d.created_at DESC";
    $stmt = $db->query($query);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $devices = [];
}

$page_title = 'Push Notifications';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Push Notifications | Admin | 33° NORTH</title>
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
          <h1 class="page-title">Push Notifications</h1>
          <div class="page-subtitle">Send Firebase Cloud Messaging (FCM) alerts to specific clients or everyone.</div>
        </div>
      </div>

      <div style="display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap;">
        <!-- Left: Form -->
        <div class="card-panel" style="flex: 1; min-width: 300px; padding: 32px;">
          <h2 style="margin-top: 0; font-size: 1.2rem; margin-bottom: 24px; font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            Compose Notification
          </h2>
          <form id="pushForm">
            <div class="form-group">
              <label>Target Audience</label>
              <select id="target_audience" class="form-control" onchange="toggleUserList()">
                <option value="all">Broadcast to All Users</option>
                <option value="selected">Send to Selected Users</option>
              </select>
            </div>

            <div id="user_list_container" style="display: none; margin-bottom: 20px; max-height: 200px; overflow-y: auto; background: rgba(255,255,255,0.4); backdrop-filter: blur(4px); border: 1px solid var(--admin-border); border-radius: 8px; padding: 12px;">
              <?php if(empty($devices)): ?>
                <div style="font-size: 13px; color: var(--admin-text-muted);">No devices registered yet.</div>
              <?php else: ?>
                <?php foreach($devices as $index => $device): ?>
                  <label style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.05); cursor: pointer;">
                    <input type="checkbox" class="user-checkbox" value="<?php echo htmlspecialchars($device['token']); ?>" style="width: 16px; height: 16px;">
                    <div>
                      <div style="font-weight: 600; font-size: 14px; color: var(--admin-text);">
                        <?php echo $device['name'] ? htmlspecialchars($device['name']) : 'Guest Device'; ?>
                      </div>
                      <div style="font-size: 11px; color: var(--admin-text-muted);">
                        <?php echo $device['email'] ? htmlspecialchars($device['email']) : 'Token: ' . substr($device['token'], 0, 20) . '...'; ?>
                      </div>
                    </div>
                  </label>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label>Notification Title</label>
              <input type="text" id="title" class="form-control" required placeholder="e.g. Special Offer!">
            </div>
            
            <div class="form-group">
              <label>Notification Message</label>
              <textarea id="body" rows="4" class="form-control" required placeholder="e.g. Get 20% off your next haircut..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">Send Push Notification</button>
          </form>
        </div>

      <!-- Toast container for responses -->
      <div id="responseBox" class="admin-toast-container"></div>
    </div>
  </main>

  <script>
    function toggleUserList() {
      const val = document.getElementById('target_audience').value;
      document.getElementById('user_list_container').style.display = (val === 'selected') ? 'block' : 'none';
    }

    document.getElementById('pushForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = e.target.querySelector('button');
      const box = document.getElementById('responseBox');
      
      const targetAudience = document.getElementById('target_audience').value;
      let selectedTokens = [];

      if (targetAudience === 'selected') {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        checkboxes.forEach(cb => selectedTokens.push(cb.value));
        
        if (selectedTokens.length === 0) {
          box.innerHTML = `<div class='admin-toast show admin-toast-warning' style='border-left-color: var(--admin-warning);'><div class='admin-toast-icon'>⚠️</div><div class='admin-toast-msg'>Please select at least one user.</div></div>`;
          return;
        }
      }

      btn.innerText = 'Sending...';
      btn.disabled = true;
      box.innerHTML = '';

      const data = {
        title: document.getElementById('title').value,
        body: document.getElementById('body').value,
        tokens: selectedTokens // empty means broadcast if target_audience is all
      };

      try {
        const res = await fetch('../../api/controllers/NotificationController.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        const json = await res.json();
        
        if (res.ok) {
          box.innerHTML = `<div class='admin-toast show admin-toast-success' style='border-left-color: var(--admin-success);'><div class='admin-toast-icon'>✅</div><div class='admin-toast-msg'>Success: ${json.success} sent, ${json.failed} failed.</div></div>`;
          e.target.reset();
          toggleUserList();
        } else {
          box.innerHTML = `<div class='admin-toast show admin-toast-danger' style='border-left-color: var(--admin-danger);'><div class='admin-toast-icon'>❌</div><div class='admin-toast-msg'>Error: ${json.message}</div></div>`;
        }
      } catch (err) {
        box.innerHTML = `<div class='admin-toast show admin-toast-danger' style='border-left-color: var(--admin-danger);'><div class='admin-toast-icon'>❌</div><div class='admin-toast-msg'>Network error occurred.</div></div>`;
      } finally {
        btn.innerText = 'Send Push Notification';
        btn.disabled = false;
        
        setTimeout(() => {
          const toast = box.querySelector('.admin-toast');
          if (toast) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
          }
        }, 5000);
      }
    });
  </script>
</body>
</html>
