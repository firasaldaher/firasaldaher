<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/database.php';

$db = (new Database())->getConnection();
$message = "";

// Ensure notes column exists in clients table
try {
    $db->exec("ALTER TABLE clients ADD COLUMN notes TEXT DEFAULT NULL");
} catch (PDOException $e) {
    // Column likely exists already, safe to ignore
}

// Handle AJAX request for Client Profile
if (isset($_GET['ajax_client_id'])) {
    header('Content-Type: application/json');
    $client_id = $_GET['ajax_client_id'];
    
    // Get client info
    $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($client) {
        // Get appointments by matching client_phone
        $stmtAppt = $db->prepare("SELECT * FROM appointments WHERE client_phone = ? ORDER BY appointment_date DESC LIMIT 10");
        $stmtAppt->execute([$client['phone']]);
        $appointments = $stmtAppt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'client' => $client,
            'appointments' => $appointments
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Client not found.']);
    }
    exit;
}

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_notes') {
        $id = $_POST['client_id'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        $stmt = $db->prepare("UPDATE clients SET notes = ? WHERE id = ?");
        if($stmt->execute([$notes, $id])) {
            $message = "<div class='admin-toast show admin-toast-success' style='border-left-color: var(--admin-success);'><div class='admin-toast-icon'>✅</div><div class='admin-toast-msg'>Notes saved successfully!</div></div>";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['client_id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
        if($stmt->execute([$id])) {
            $message = "<div class='admin-toast show admin-toast-success' style='border-left-color: var(--admin-success);'><div class='admin-toast-icon'>✅</div><div class='admin-toast-msg'>Client deleted!</div></div>";
        }
    }
}

// Fetch Clients
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $db->prepare("SELECT * FROM clients WHERE name LIKE ? OR phone LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $db->query("SELECT * FROM clients ORDER BY created_at DESC");
}
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getTier($pts) {
    if ($pts > 2000) return 'VIP';
    if ($pts > 500) return 'Gold';
    return 'Silver';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>CRM & Clients | Admin | Caraway</title>
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
      <div class="page-header" style="display:flex; flex-direction:row; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;">
        <div>
          <h1 class="page-title">Client Management (CRM)</h1>
          <div class="page-subtitle">View client profiles, appointment history, and add internal notes.</div>
        </div>
        <form method="GET" style="display: flex; gap: 8px; align-items: center;">
          <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search name or phone..." style="width: 250px; margin:0;">
          <button type="submit" class="btn btn-primary" style="margin:0;">Search</button>
          <?php if($search): ?><a href="index.php" class="btn btn-outline" style="margin:0;">Clear</a><?php endif; ?>
        </form>
      </div>

      <?php if($message) echo $message; ?>

      <div class="card-panel">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 25%;">Client Info</th>
              <th style="width: 25%;">Contact</th>
              <th style="width: 20%;">Tier / Points</th>
              <th style="width: 15%;">Joined Date</th>
              <th style="width: 15%; text-align: center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($clients as $client): ?>
            <tr>
              <td>
                <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($client['name']); ?></div>
              </td>
              <td>
                <div style="font-weight: 500; font-size: 14px; margin-bottom: 2px; color: var(--admin-text);"><?php echo htmlspecialchars($client['phone']); ?></div>
                <div style="color: var(--admin-text-muted); font-size: 13px;"><?php echo htmlspecialchars($client['email']); ?></div>
              </td>
              <td>
                <?php 
                  $t = getTier($client['points']); 
                  $color = ($t=='VIP') ? '#d4af37' : (($t=='Gold') ? '#F59E0B' : '#9ca3af');
                ?>
                <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: <?php echo $color; ?>20; color: <?php echo $color; ?>;">
                  <?php echo $t; ?>
                </span>
                <div style="font-size: 12px; color: var(--admin-text-muted); margin-top: 4px;"><strong><?php echo $client['points']; ?></strong> pts</div>
              </td>
              <td style="font-size: 14px; color: var(--admin-text-muted);">
                <?php echo date('M d, Y', strtotime($client['created_at'])); ?>
              </td>
              <td style="text-align: center;">
                <button type="button" class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;" onclick="openProfile(<?php echo $client['id']; ?>)">View Profile</button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($clients)): ?>
            <tr><td colspan="5" style="text-align:center; padding:40px; color:var(--admin-text-muted);">No clients found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Client Profile Modal -->
  <div id="profileModal" class="admin-modal-overlay">
    <div class="admin-modal" style="max-width: 650px; text-align: left;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid var(--admin-border); padding-bottom: 15px;">
        <h3 class="admin-modal-title" style="margin:0; font-size: 20px; display: flex; align-items: center; gap: 8px; color: var(--admin-primary);">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          Client Profile
        </h3>
        <button type="button" onclick="closeProfile()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--admin-text-muted); padding: 0;">&times;</button>
      </div>
      
      <div style="display: flex; gap: 32px; flex-wrap: wrap;">
        <!-- Left Column: Info & Notes -->
        <div style="flex: 1; min-width: 250px;">
          <div style="margin-bottom: 24px;">
            <h4 id="profName" style="font-family: var(--font-head); font-size: 22px; font-weight: 700; margin-bottom: 8px; color: var(--admin-primary);"></h4>
            <div id="profPhone" style="font-size: 15px; color: var(--admin-text); font-weight: 600; margin-bottom: 4px;"></div>
            <div id="profEmail" style="font-size: 14px; color: var(--admin-text-muted); margin-bottom: 12px;"></div>
            <div id="profJoined" style="font-size: 13px; color: var(--admin-text-muted);"></div>
          </div>

          <form method="POST" style="margin-bottom: 24px;">
            <input type="hidden" name="action" value="update_notes">
            <input type="hidden" name="client_id" id="noteClientId">
            <div class="form-group" style="margin-bottom: 12px;">
              <label>Internal Notes</label>
              <textarea name="notes" id="profNotes" class="form-control" rows="4" placeholder="e.g. VIP preferences, allergies, color formulas..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px;">Save Notes</button>
          </form>

          <form method="POST" onsubmit="return confirm('WARNING: This will permanently delete the client. Continue?');" style="margin:0;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="client_id" id="delClientId">
            <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center; padding: 10px; color: var(--admin-danger); border: 1px solid var(--admin-danger);">Delete Client</button>
          </form>
        </div>

        <!-- Right Column: Appointment History -->
        <div style="flex: 1; min-width: 250px; border-left: 1px solid var(--admin-border); padding-left: 32px;">
          <h4 style="font-size: 13px; text-transform: uppercase; color: var(--admin-text-muted); font-weight: 600; letter-spacing: 0.05em; margin-bottom: 16px;">Recent Appointments</h4>
          <div id="profHistory" style="max-height: 400px; overflow-y: auto; padding-right: 8px;">
            <!-- Rendered via JS -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if($message): ?>
  <div class="admin-toast-container">
    <?php echo $message; ?>
  </div>
  <?php endif; ?>

  <script>
    function openProfile(id) {
      document.getElementById('profHistory').innerHTML = '<div style="color:var(--admin-text-muted); font-size:14px; text-align:center; padding: 20px;">Loading history...</div>';
      document.getElementById('profileModal').classList.add('show');
      
      fetch('?ajax_client_id=' + id)
        .then(res => res.json())
        .then(data => {
          if(data.success) {
            let c = data.client;
            document.getElementById('profName').innerText = c.name;
            document.getElementById('profPhone').innerText = c.phone || 'No phone';
            document.getElementById('profEmail').innerText = c.email || 'No email';
            document.getElementById('profJoined').innerText = 'Joined: ' + new Date(c.created_at).toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'});
            document.getElementById('profNotes').value = c.notes || '';
            document.getElementById('noteClientId').value = c.id;
            document.getElementById('delClientId').value = c.id;

            let html = '';
            if(data.appointments.length > 0) {
              data.appointments.forEach(app => {
                let badgeClass = (app.status === 'completed' || app.status === 'confirmed') ? 'badge-success' : (app.status === 'cancelled' ? 'badge-danger' : 'badge-warning');
                html += `
                  <div style="background: rgba(255,255,255,0.4); backdrop-filter: blur(4px); border-radius: 8px; padding: 16px; margin-bottom: 12px; border: 1px solid var(--admin-border); transition: transform 0.2s;">
                    <div style="display:flex; justify-content:space-between; align-items: flex-start; margin-bottom:8px;">
                      <strong style="font-size:15px; color:var(--admin-text); line-height: 1.3;">${app.service}</strong>
                      <span class="badge ${badgeClass}" style="font-size:10px; padding: 4px 8px; margin-left: 8px;">${app.status}</span>
                    </div>
                    <div style="font-size:13px; color:var(--admin-text-muted); display:flex; align-items:center; gap:6px;">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                      ${new Date(app.appointment_date).toLocaleDateString()} at ${app.appointment_time}
                    </div>
                  </div>
                `;
              });
            } else {
              html = '<div style="color:var(--admin-text-muted); font-size:14px; padding: 20px; background: rgba(255,255,255,0.4); backdrop-filter: blur(4px); border-radius: 8px; text-align:center;">No past appointments found.</div>';
            }
            document.getElementById('profHistory').innerHTML = html;
          } else {
            document.getElementById('profHistory').innerHTML = '<div style="color:var(--admin-danger); font-size:13px;">Error loading data.</div>';
          }
        }).catch(err => {
            document.getElementById('profHistory').innerHTML = '<div style="color:var(--admin-danger); font-size:13px;">Network error.</div>';
        });
    }

    function closeProfile() {
      document.getElementById('profileModal').classList.remove('show');
    }

    // Auto-hide toasts
    document.addEventListener('DOMContentLoaded', () => {
      let toast = document.querySelector('.admin-toast');
      if(toast) {
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }
    });
  </script>
</body>
</html>
