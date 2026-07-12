<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/database.php';

$db = (new Database())->getConnection();

// --- KPI QUERIES ---

// 1. Total Appointments
$stmt = $db->query("SELECT COUNT(*) FROM appointments");
$totalAppointments = $stmt->fetchColumn() ?: 0;

// 2. Pending Appointments
$stmt = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
$pendingAppointments = $stmt->fetchColumn() ?: 0;

// 3. Completed Appointments & Revenue
$stmt = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'");
$completedAppointments = $stmt->fetchColumn() ?: 0;
$estimatedRevenue = $completedAppointments * 65;

// 4. Total Clients
$stmt = $db->query("SELECT COUNT(*) FROM clients");
$totalClients = $stmt->fetchColumn() ?: 0;

// 5. Total Staff
$stmt = $db->query("SELECT COUNT(*) FROM staff WHERE is_active = 1");
$activeStaff = $stmt->fetchColumn() ?: 0;

// 6. Ecommerce Orders
$stmt = $db->query("SELECT COUNT(*) FROM ecommerce_orders");
$totalOrders = $stmt->fetchColumn() ?: 0;
$stmt = $db->query("SELECT COUNT(*) FROM ecommerce_orders WHERE status = 'pending'");
$pendingOrders = $stmt->fetchColumn() ?: 0;
$stmt = $db->query("SELECT COALESCE(SUM(total_amount),0) FROM ecommerce_orders WHERE status = 'delivered'");
$orderRevenue = $stmt->fetchColumn() ?: 0;

// 7. Academy Enrollments
$stmt = $db->query("SELECT COUNT(*) FROM academy_enrollments");
$totalEnrollments = $stmt->fetchColumn() ?: 0;
$stmt = $db->query("SELECT COUNT(*) FROM academy_enrollments WHERE status = 'pending'");
$pendingEnrollments = $stmt->fetchColumn() ?: 0;

// 8. Total Loyalty Points
$stmt = $db->query("SELECT COALESCE(SUM(points), 0) FROM clients");
$totalLoyaltyPoints = $stmt->fetchColumn() ?: 0;

// 9. Recent 5 Bookings
$stmt = $db->query("SELECT client_name, service, appointment_date, status FROM appointments ORDER BY created_at DESC LIMIT 5");
$recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 10. Appointments by status breakdown
$stmt = $db->query("SELECT status, COUNT(*) as cnt FROM appointments GROUP BY status");
$statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
$statusMap = [];
foreach ($statusBreakdown as $row) {
    $statusMap[$row['status']] = $row['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Reports & Analytics | Admin | Caraway</title>
  
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
          <h1 class="page-title">Reports & Analytics</h1>
          <div class="page-subtitle">Overview of business performance and metrics.</div>
        </div>
        <div>
          <button class="btn btn-primary" onclick="alert('Export functionality is available in the Premium version.')" title="Premium Feature">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Export (Premium)
          </button>
        </div>
      </div>

      <!-- KPI Metrics -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-title">Total Appointments</div>
          <div class="stat-card-value"><?php echo $totalAppointments; ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">Completed: <?php echo $completedAppointments; ?></small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">Pending Action</div>
          <div class="stat-card-value" style="color: var(--admin-danger);"><?php echo $pendingAppointments; ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">Appointments awaiting review</small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">Est. Revenue</div>
          <div class="stat-card-value" style="color: var(--admin-primary);">$<?php echo number_format($estimatedRevenue); ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">From completed sessions</small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">Registered Clients</div>
          <div class="stat-card-value"><?php echo $totalClients; ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">Loyalty pts: <?php echo number_format($totalLoyaltyPoints); ?></small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">Active Staff</div>
          <div class="stat-card-value" style="color: var(--admin-success);"><?php echo $activeStaff; ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">On the team</small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">E-commerce Orders</div>
          <div class="stat-card-value"><?php echo $totalOrders; ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">Pending: <?php echo $pendingOrders; ?> · Revenue: $<?php echo number_format($orderRevenue); ?></small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">Academy Applications</div>
          <div class="stat-card-value"><?php echo $totalEnrollments; ?></div>
          <small style="color: var(--admin-text-muted); font-size:0.78rem; font-weight: 600;">Pending review: <?php echo $pendingEnrollments; ?></small>
        </div>

        <div class="stat-card">
          <div class="stat-card-title">Appointment Breakdown</div>
          <div class="stat-card-value" style="font-size: 1.2rem; line-height:1.6; display: flex; flex-direction: column; gap: 4px;">
            <?php
              $confirmed = $statusMap['confirmed'] ?? 0;
              $cancelled = $statusMap['cancelled'] ?? 0;
            ?>
            <span style="color: var(--admin-success); font-size: 16px;">Done: <?php echo $completedAppointments; ?></span>
            <span style="color: var(--admin-warning); font-size: 16px;">Confirmed: <?php echo $confirmed; ?></span>
            <span style="color: var(--admin-danger); font-size: 16px;">Cancelled: <?php echo $cancelled; ?></span>
          </div>
        </div>
      </div>

      <!-- Analytics Chart Placeholder (Premium) -->
      <div class="card-panel" style="margin-bottom: 32px;">
        <div class="card-panel-header">
          <div class="card-panel-title">Analytics & Trends</div>
          <span class="badge badge-warning">Premium Feature</span>
        </div>
        <div style="padding: 60px 24px; text-align: center; color: var(--admin-text-muted); background: rgba(255,255,255,0.4); backdrop-filter: blur(8px); border-radius: 8px; border: 1px solid rgba(255,255,255,0.5);">
          <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none" style="margin-bottom: 16px; opacity: 0.5;">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="9" y1="21" x2="9" y2="9"></line>
          </svg>
          <h3 style="font-size: 16px; color: var(--admin-text); margin-bottom: 8px;">Advanced Chart Analytics</h3>
          <p style="font-size: 14px; max-width: 400px; margin: 0 auto;">Upgrade to the premium version to unlock detailed visual charts, historical data comparisons, and exportable reports.</p>
          <button class="btn btn-outline" style="margin-top: 20px;" onclick="alert('Contact support to upgrade to Premium.')">Upgrade Now</button>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card-panel">
        <h2 style="margin-top: 0; font-size: 1.2rem; margin-bottom: 20px; font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
          Recent Booking Activity
        </h2>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Client Name</th>
                <th>Service Requested</th>
                <th>Appointment Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBookings as $booking): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($booking['client_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($booking['service']); ?></td>
                <td><?php echo date('M d, Y', strtotime($booking['appointment_date'])); ?></td>
                <td>
                  <span class="badge badge-<?php 
                    echo ($booking['status'] === 'completed' || $booking['status'] === 'confirmed') ? 'success' : 
                         ($booking['status'] === 'pending' ? 'warning' : 'danger'); 
                  ?>">
                    <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($recentBookings)): ?>
              <tr><td colspan="4" style="text-align:center; padding: 20px;">No recent bookings found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
  
  <script src="../assets/js/admin.js"></script>
</body>
</html>
