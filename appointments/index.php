<?php 
require_once __DIR__ . '/../includes/auth.php'; 
require_once __DIR__ . '/../../api/config/constants.php';
require_once __DIR__ . '/../../api/config/database.php';

$message = "";
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $client_name = $_POST['client_name'] ?? '';
            $client_phone = $_POST['client_phone'] ?? '';
            $service = $_POST['service'] ?? '';
            $appointment_date = $_POST['appointment_date'] ?? '';
            $appointment_time = $_POST['appointment_time'] ?? '';
            $status = $_POST['status'] ?? 'pending';
            
            try {
                $stmt = $db->prepare("INSERT INTO appointments (client_name, client_phone, service, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_name, $client_phone, $service, $appointment_date, $appointment_time, $status]);
                $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Appointment added successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            try {
                $stmt = $db->prepare("DELETE FROM appointments WHERE id = ?");
                $stmt->execute([$id]);
                $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Appointment deleted successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
            }
        } elseif ($action === 'update_status') {
            $id = $_POST['id'] ?? 0;
            $status = $_POST['status'] ?? 'pending';
            try {
                $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                $message = "<div style='color: green; margin-bottom:15px; padding: 10px; background: #e8f8f5; border-radius: 4px;'>Status updated successfully!</div>";
            } catch (PDOException $e) {
                $message = "<div style='color: red; margin-bottom:15px; padding: 10px; background: #fdf2e9; border-radius: 4px;'>Error: " . $e->getMessage() . "</div>";
            }
        } elseif ($action === 'ajax_update_date') {
            header('Content-Type: application/json');
            $id = $_POST['id'] ?? 0;
            $new_date = $_POST['appointment_date'] ?? '';
            $new_time = $_POST['appointment_time'] ?? '';
            try {
                $stmt = $db->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ? WHERE id = ?");
                $stmt->execute([$new_date, $new_time, $id]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
    }

    if ($db) {
        $query = "SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC";
        $stmt = $db->query($query);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $appointments = [];
        $message = "<div style='color: red; margin-bottom: 15px;'>Database connection failed.</div>";
    }
} catch (PDOException $e) {
    $appointments = [];
    $message = "<div style='color: red; margin-bottom: 15px;'>Error fetching appointments: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Appointments | Admin | 33° NORTH</title>
  
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
          <h1 class="page-title">Appointments</h1>
          <div class="page-subtitle">Manage all upcoming and past bookings.</div>
        </div>
      </div>

      <div class="view-tabs">
        <div class="view-tab active" onclick="switchView('list')">List View</div>
        <div class="view-tab" onclick="switchView('calendar')">Calendar View</div>
      </div>

      <?php if (!empty($message)) echo $message; ?>

      <!-- Calendar Panel -->
      <div id="calendar-panel" class="view-panel">
        <div class="card-panel">
          <div id="calendar"></div>
        </div>
      </div>

      <!-- List Panel -->
      <div id="list-panel" class="view-panel active">
        <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
        <!-- Table Section -->
        <div class="card-panel" style="flex: 2; min-width: 300px;">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 20%;">Client Name</th>
                  <th style="width: 15%;">Phone</th>
                  <th style="width: 20%;">Service</th>
                  <th style="width: 20%;">Date & Time</th>
                  <th style="width: 15%;">Status</th>
                  <th style="width: 10%; text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($appointments) > 0): ?>
                  <?php foreach ($appointments as $appt): ?>
                  <tr>
                    <td>
                      <div style="font-weight: 600; color: var(--admin-text);"><?php echo htmlspecialchars($appt['client_name']); ?></div>
                    </td>
                    <td style="color: var(--admin-text-muted); font-size: 14px;"><?php echo htmlspecialchars($appt['client_phone']); ?></td>
                    <td style="font-size: 14px;"><?php echo htmlspecialchars($appt['service']); ?></td>
                    <td style="white-space: nowrap;">
                      <div style="font-weight: 600; font-size: 14px; color: var(--admin-text);"><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></div>
                      <div style="color: var(--admin-text-muted); font-size: 13px; margin-top: 2px;"><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></div>
                    </td>
                    <td>
                      <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                        <select name="status" onchange="this.form.submit()" 
                          style="padding: 6px 28px 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; border: none; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23333%22%20stroke-width%3D%223%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; <?php 
                            if ($appt['status'] === 'confirmed' || $appt['status'] === 'completed') echo 'background-color:rgba(16, 185, 129, 0.1); color:var(--admin-success);';
                            elseif ($appt['status'] === 'cancelled') echo 'background-color:rgba(230, 57, 70, 0.1); color:var(--admin-danger);';
                            else echo 'background-color:rgba(245, 158, 11, 0.1); color:var(--admin-warning);';
                          ?>">
                          <option value="pending" <?php if($appt['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                          <option value="confirmed" <?php if($appt['status'] === 'confirmed') echo 'selected'; ?>>Confirmed</option>
                          <option value="completed" <?php if($appt['status'] === 'completed') echo 'selected'; ?>>Completed</option>
                          <option value="cancelled" <?php if($appt['status'] === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                      </form>
                    </td>
                    <td style="text-align: center;">
                      <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                        <button type="submit" class="action-btn" title="Delete" style="margin: 0 auto; color: var(--admin-danger); background: rgba(230, 57, 70, 0.05); border: 1px solid rgba(230, 57, 70, 0.1);">
                          <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="6" style="text-align: center; padding: 40px 20px; color: var(--admin-text-muted);">No appointments found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Add Form Section -->
        <div class="card-panel" style="flex: 1; min-width: 300px; padding: 24px;">
          <h2 style="margin-top: 0; font-size: 1.2rem; margin-bottom: 20px; font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            New Appointment
          </h2>
          <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
              <label>Client Name</label>
              <input type="text" name="client_name" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="client_phone" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Service</label>
              <input type="text" name="service" class="form-control" placeholder="e.g. Hair Cut" required>
            </div>
            <div class="form-group">
              <label>Date</label>
              <input type="date" name="appointment_date" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Time</label>
              <input type="time" name="appointment_time" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Book Appointment</button>
          </form>
        </div>

        </div>
      </div>
      <!-- End List Panel -->

    </div>
  </main>

  <div id="eventModal" class="admin-modal-overlay">
    <div class="admin-modal" style="max-width: 450px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--admin-border); padding-bottom: 15px;">
        <h3 class="admin-modal-title" style="margin:0; font-size: 18px;">Appointment Details</h3>
        <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--admin-text-muted); padding: 0;">&times;</button>
      </div>
      <div style="text-align: left; font-size: 15px;">
        <div style="margin-bottom: 12px;"><strong style="color:var(--admin-text-muted); display:inline-block; width: 70px;">Client:</strong> <span id="modalClient" style="font-weight: 600; color: var(--admin-text);"></span></div>
        <div style="margin-bottom: 12px;"><strong style="color:var(--admin-text-muted); display:inline-block; width: 70px;">Phone:</strong> <span id="modalPhone"></span></div>
        <div style="margin-bottom: 12px;"><strong style="color:var(--admin-text-muted); display:inline-block; width: 70px;">Service:</strong> <span id="modalService"></span></div>
        <div style="margin-bottom: 12px;"><strong style="color:var(--admin-text-muted); display:inline-block; width: 70px;">Time:</strong> <span id="modalTime" style="font-weight: 600;"></span></div>
        <div style="margin-bottom: 24px;"><strong style="color:var(--admin-text-muted); display:inline-block; width: 70px;">Status:</strong> <span id="modalStatusBadge" class="badge"></span></div>
      </div>
      <div class="admin-modal-actions">
        <form method="POST" style="margin: 0; flex: 1;">
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" name="id" id="modalStatusId">
          <input type="hidden" name="status" value="completed">
          <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--admin-success); border-color: var(--admin-success);">Complete</button>
        </form>
        <form method="POST" style="margin: 0; flex: 1;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" id="modalDeleteId">
          <button type="submit" class="btn btn-outline" style="width: 100%; color: var(--admin-danger); border: 1px solid var(--admin-danger);">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <script>
    const appointmentsData = <?php echo json_encode(array_map(function($appt) {
      $className = 'event-' . strtolower($appt['status']);
      return [
        'id' => $appt['id'],
        'title' => $appt['service'] . ' - ' . $appt['client_name'],
        'start' => $appt['appointment_date'] . 'T' . $appt['appointment_time'],
        'className' => $className,
        'extendedProps' => [
          'client' => $appt['client_name'],
          'phone' => $appt['client_phone'],
          'status' => $appt['status']
        ]
      ];
    }, $appointments)); ?>;

    let calendar;

    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const isCalendarView = urlParams.get('view') === 'calendar';
      
      var calendarEl = document.getElementById('calendar');
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: appointmentsData,
        editable: true,
        eventDrop: function(info) {
          const newDate = info.event.start;
          const year = newDate.getFullYear();
          const month = String(newDate.getMonth() + 1).padStart(2, '0');
          const day = String(newDate.getDate()).padStart(2, '0');
          const dateStr = `${year}-${month}-${day}`;
          
          const hours = String(newDate.getHours()).padStart(2, '0');
          const mins = String(newDate.getMinutes()).padStart(2, '0');
          const timeStr = `${hours}:${mins}:00`;

          const formData = new FormData();
          formData.append('action', 'ajax_update_date');
          formData.append('id', info.event.id);
          formData.append('appointment_date', dateStr);
          formData.append('appointment_time', timeStr);

          fetch('', {
            method: 'POST',
            body: formData
          }).then(response => response.json())
            .then(data => {
              if(!data.success) {
                alert('Failed to update date: ' + data.error);
                info.revert();
              }
            }).catch(err => {
              alert('Network error.');
              info.revert();
            });
        },
        eventContent: function(arg) {
          let timeText = arg.timeText ? `<b style="margin-right: 4px;">${arg.timeText}</b>` : '';
          let titleHtml = `<span>${arg.event.title}</span>`;
          return { html: `<div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${timeText}${titleHtml}</div>` };
        },
        eventClick: function(info) {
           document.getElementById('modalClient').innerText = info.event.extendedProps.client;
           document.getElementById('modalPhone').innerText = info.event.extendedProps.phone;
           
           let serviceName = info.event.title.split(' - ')[0];
           document.getElementById('modalService').innerText = serviceName;
           
           let start = info.event.start;
           const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
           document.getElementById('modalTime').innerText = start.toLocaleString('en-US', options);
           
           let status = info.event.extendedProps.status;
           let badge = document.getElementById('modalStatusBadge');
           badge.innerText = status;
           badge.className = 'badge'; 
           if(status === 'confirmed' || status === 'completed') badge.classList.add('badge-success');
           else if(status === 'cancelled') badge.classList.add('badge-danger');
           else badge.classList.add('badge-warning');

           document.getElementById('modalStatusId').value = info.event.id;
           document.getElementById('modalDeleteId').value = info.event.id;

           document.getElementById('eventModal').classList.add('show');
        },
        height: 'auto'
      });
      calendar.render();
      
      if (isCalendarView) {
        setTimeout(() => switchView('calendar'), 50);
      }
    });

    function closeModal() {
      document.getElementById('eventModal').classList.remove('show');
    }

    function switchView(view) {
      document.querySelectorAll('.view-tab').forEach(tab => tab.classList.remove('active'));
      document.querySelectorAll('.view-panel').forEach(panel => panel.classList.remove('active'));
      
      if (view === 'list') {
        document.querySelectorAll('.view-tab')[0].classList.add('active');
        document.getElementById('list-panel').classList.add('active');
      } else {
        document.querySelectorAll('.view-tab')[1].classList.add('active');
        document.getElementById('calendar-panel').classList.add('active');
        setTimeout(() => { calendar.render(); }, 10);
      }
    }
  </script>
  <script src="../assets/js/admin.js"></script>
</body>
</html>
