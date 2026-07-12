<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/api/config/constants.php';
require_once __DIR__ . '/api/config/database.php';

try {
  $database = new Database();
  $db = $database->getConnection();

  if ($db) {
    // Today's Orders
    $stmt1 = $db->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
    $todayOrders = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Today's Revenue
    $stmt2 = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
    $todayRevenue = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Total Products
    $stmt3 = $db->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Low Stock Items
    $stmt4 = $db->query("SELECT COUNT(*) as total FROM inventory WHERE quantity <= low_stock_threshold");
    $lowStock = $stmt4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Today's Expenses
    $stmt5 = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE(created_at) = CURDATE()");
    $todayExpenses = $stmt5->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    try {
        $recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { $recentOrders = []; }

  } else {
    $todayOrders = $todayRevenue = $totalProducts = $lowStock = $todayExpenses = 0;
    $recentOrders = [];
    $error = "Database connection failed.";
  }
} catch (PDOException $e) {
  $todayOrders = $todayRevenue = $totalProducts = $lowStock = $todayExpenses = 0;
  $recentOrders = [];
  $error = "DB Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Admin Dashboard | 33° NORTH</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap"
    rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>

  <!-- Placeholders for Includes -->
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Dashboard Overview</h1>
          <div class="page-subtitle">Welcome back! Here's what's happening today.</div>
        </div>
        <div>
          <button class="btn btn-primary" onclick="alert('Export functionality is available in the Premium version.')"
            title="Premium Feature">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export (Premium)
          </button>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-title">Today's Orders</div>
          <div class="stat-card-value"><?php echo $todayOrders; ?></div>
          <div class="stat-card-trend trend-up">Today</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Today's Revenue</div>
          <div class="stat-card-value">$<?php echo number_format($todayRevenue, 2); ?></div>
          <div class="stat-card-trend trend-up">Today</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Today's Expenses</div>
          <div class="stat-card-value">$<?php echo number_format($todayExpenses, 2); ?></div>
          <div class="stat-card-trend trend-down">Today</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Total Menu Items</div>
          <div class="stat-card-value"><?php echo $totalProducts; ?></div>
          <div class="stat-card-trend trend-up">Active Products</div>
        </div>
        <div class="stat-card">
          <div class="stat-card-title">Low Stock Alerts</div>
          <div class="stat-card-value"><?php echo $lowStock; ?></div>
          <div class="stat-card-trend <?php echo $lowStock > 0 ? 'trend-down' : 'trend-up'; ?>">Inventory</div>
        </div>
      </div>

      <!-- 33 North Ads -->
      <div style="margin-top: 32px; margin-bottom: 32px;">
        <h2
          style="font-family: var(--font-head); font-size: 18px; margin-bottom: 16px; color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
          </svg>
          Grow Your Business
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">

          <!-- Ad 1: App -->
          <div class="card-panel"
            style="background: linear-gradient(145deg, #111 0%, #222 100%); border: 1px solid rgba(212, 175, 55, 0.3); box-shadow: 0 4px 15px rgba(212,175,55,0.05); color: #fff; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div
                style="width: 48px; height: 48px; background: rgba(212, 175, 55, 0.15); color: var(--admin-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                  <line x1="12" y1="18" x2="12.01" y2="18"></line>
                </svg>
              </div>
              <h3 style="font-size: 18px; margin-bottom: 8px; color: #fff; font-family: var(--font-head);">Create Mobile
                App</h3>
              <p style="font-size: 13px; color: #aaa; line-height: 1.5; margin-bottom: 24px;">Launch your own custom iOS
                & Android application with cutting-edge UI/UX designed specifically for your salon.</p>
            </div>
            <a href="https://wa.me/96181340801" target="_blank" class="btn btn-primary"
              style="width: 100%; justify-content: center; border: none; color: #111; font-weight: 700;">Request
              Quote</a>
          </div>

          <!-- Ad 2: Web -->
          <div class="card-panel"
            style="background: linear-gradient(145deg, #111 0%, #222 100%); border: 1px solid rgba(16, 185, 129, 0.3); box-shadow: 0 4px 15px rgba(16,185,129,0.05); color: #fff; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div
                style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.15); color: var(--admin-success); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="16 18 22 12 16 6"></polyline>
                  <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
              </div>
              <h3 style="font-size: 18px; margin-bottom: 8px; color: #fff; font-family: var(--font-head);">Create Full
                Project</h3>
              <p style="font-size: 13px; color: #aaa; line-height: 1.5; margin-bottom: 24px;">End-to-end software
                development, from high-performance web platforms to complex enterprise POS systems.</p>
            </div>
            <a href="https://wa.me/96181340801" target="_blank" class="btn"
              style="width: 100%; justify-content: center; background: rgba(16,185,129,0.15); color: var(--admin-success); border: 1px solid rgba(16,185,129,0.3); font-weight: 700;">Learn
              More</a>
          </div>

          <!-- Ad 3: Partnership -->
          <div class="card-panel"
            style="background: linear-gradient(145deg, #111 0%, #222 100%); border: 1px solid rgba(59, 130, 246, 0.3); box-shadow: 0 4px 15px rgba(59,130,246,0.05); color: #fff; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div
                style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.15); color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
              </div>
              <h3 style="font-size: 18px; margin-bottom: 8px; color: #fff; font-family: var(--font-head);">Partner With
                Us</h3>
              <p style="font-size: 13px; color: #aaa; line-height: 1.5; margin-bottom: 24px;">Be our partner in building
                an innovative tech solution. Let's collaborate to bring visionary ideas to life.</p>
            </div>
            <a href="https://wa.me/96181340801" target="_blank" class="btn"
              style="width: 100%; justify-content: center; background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); font-weight: 700;">Become
              a Partner</a>
          </div>

        </div>
      </div>

      <!-- Analytics & Premium Features -->
      <div style="display: flex; flex-wrap: wrap; gap: 24px; margin-bottom: 32px;">

        <!-- Analytics Chart Placeholder (Premium) -->
        <div class="card-panel" style="flex: 1 1 600px; display: flex; flex-direction: column;">
          <div class="card-panel-header">
            <div class="card-panel-title">Analytics & Trends</div>
            <span class="badge badge-warning">Premium Feature</span>
          </div>
          <div
            style="padding: 60px 24px; text-align: center; color: var(--admin-text-muted); background: rgba(255,255,255,0.6); flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(10px);">
            <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none"
              style="margin-bottom: 16px; opacity: 0.5;">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="3" y1="9" x2="21" y2="9"></line>
              <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            <h3 style="font-size: 16px; color: var(--admin-text); margin-bottom: 8px;">Advanced Chart Analytics</h3>
            <p style="font-size: 14px; max-width: 400px; margin: 0 auto;">Upgrade to the premium version to unlock
              detailed visual charts, historical data comparisons, and exportable reports.</p>
            <button class="btn btn-outline" style="margin-top: 20px;"
              onclick="window.location.href='settings/checkout.php?service=Advanced Chart Analytics&price=80'">Upgrade
              Now</button>
          </div>
        </div>

        <!-- Premium Support Service -->
        <div class="card-panel"
          style="flex: 1 1 300px; background: linear-gradient(145deg, #111 0%, #222 100%); color: #fff; border: 1px solid #333; display: flex; flex-direction: column;">
          <div class="card-panel-header"
            style="border-bottom: 1px solid rgba(255,255,255,0.1); background: transparent;">
            <div class="card-panel-title" style="color: #fff;">Priority Technical Support</div>
          </div>
          <div
            style="padding: 40px 24px; text-align: center; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <div style="color: var(--admin-primary); margin-bottom: 16px;">
              <svg viewBox="0 0 24 24" width="48" height="48" stroke="currentColor" stroke-width="1.5" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                <line x1="9" y1="10" x2="15" y2="10"></line>
                <line x1="12" y1="7" x2="12" y2="13"></line>
              </svg>
            </div>
            <h3 style="font-size: 28px; font-family: var(--font-head); margin-bottom: 4px; color: #fff;">$200<span
                style="font-size: 14px; color: #aaa; font-weight: 500; font-family: var(--font-body);"> / month</span>
            </h3>
            <p style="font-size: 14px; color: #aaa; margin-bottom: 24px; line-height: 1.6; max-width: 250px;">Get 24/7
              technical support, a dedicated account manager, and priority updates.</p>
            <button class="btn btn-primary" style="width: 100%; justify-content: center;"
              onclick="window.location.href='settings/checkout.php?service=Priority Technical Support&price=200'">Subscribe
              Now</button>
          </div>
        </div>

      </div>

      <!-- Recent Appointments -->
      <div class="card-panel">
        <div class="card-panel-header">
          <div class="card-panel-title"
            style="font-family: var(--font-head); color: var(--admin-primary); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Recent Orders
          </div>
          <a href="orders/" class="btn btn-outline">View All</a>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Total Amount</th>
              <th>Date & Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($recentOrders) > 0): ?>
              <?php foreach ($recentOrders as $order): ?>
                <tr>
                  <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                  <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                  <td>
                    <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                  </td>
                  <td>
                    <?php
                    $status_class = 'badge-warning';
                    if ($order['status'] === 'completed')
                      $status_class = 'badge-success';
                    if ($order['status'] === 'cancelled')
                      $status_class = 'badge-danger';
                    ?>
                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">No recent orders.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Recommended Upgrades (Upsell Section) -->
      <div style="margin-top: 40px; margin-bottom: 20px;">
        <h2 style="font-family: var(--font-head); font-size: 20px; color: var(--admin-text); margin-bottom: 8px;">Unlock
          More Power</h2>
        <p style="font-size: 14px; color: var(--admin-text-muted); margin-bottom: 24px;">Premium add-ons designed to
          protect and scale your business.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">

          <!-- Security -->
          <div class="card-panel"
            style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(16, 185, 129, 0.2); box-shadow: 0 4px 12px rgba(16,185,129,0.05); transition: transform 0.2s;">
            <div style="padding: 24px; flex-grow: 1;">
              <div
                style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: var(--admin-success); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
              </div>
              <h3 style="font-size: 16px; margin-bottom: 8px; color: var(--admin-text);">Advanced Security Shield</h3>
              <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 16px;">Protect
                your business from cyber threats. Includes Web Application Firewall, DDoS protection, and daily malware
                scans.</p>
              <div style="font-size: 20px; font-weight: 700; color: var(--admin-text);">$50<span
                  style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);"> / month</span></div>
            </div>
            <div
              style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(10px);">
              <button class="btn"
                style="width: 100%; justify-content: center; background: rgba(16,185,129,0.1); color: var(--admin-success); font-weight: 700;"
                onclick="window.location.href='settings/checkout.php?service=Advanced Security Shield&price=50'">Activate
                Protection</button>
            </div>
          </div>

          <!-- Backups -->
          <div class="card-panel"
            style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(59, 130, 246, 0.2); box-shadow: 0 4px 12px rgba(59,130,246,0.05); transition: transform 0.2s;">
            <div style="padding: 24px; flex-grow: 1;">
              <div
                style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
              </div>
              <h3 style="font-size: 16px; margin-bottom: 8px; color: var(--admin-text);">Automated Cloud Backups</h3>
              <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 16px;">Never
                lose a booking or client record. We automatically backup your entire database and files to secure cloud
                servers daily.</p>
              <div style="font-size: 20px; font-weight: 700; color: var(--admin-text);">$30<span
                  style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);"> / month</span></div>
            </div>
            <div
              style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(10px);">
              <button class="btn"
                style="width: 100%; justify-content: center; background: rgba(59,130,246,0.1); color: #3b82f6; font-weight: 700;"
                onclick="window.location.href='settings/checkout.php?service=Automated Cloud Backups&price=30'">Enable
                Backups</button>
            </div>
          </div>

          <!-- Marketing & SEO -->
          <div class="card-panel"
            style="display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(139, 92, 246, 0.2); box-shadow: 0 4px 12px rgba(139,92,246,0.05); transition: transform 0.2s;">
            <div style="padding: 24px; flex-grow: 1;">
              <div
                style="width: 48px; height: 48px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none">
                  <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                  <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
              </div>
              <h3 style="font-size: 16px; margin-bottom: 8px; color: var(--admin-text);">SEO & Marketing Suite</h3>
              <p style="font-size: 13px; color: var(--admin-text-muted); line-height: 1.5; margin-bottom: 16px;">Boost
                your salon's visibility. Includes automated email campaigns, SMS booking reminders, and advanced Google
                SEO tools.</p>
              <div style="font-size: 20px; font-weight: 700; color: var(--admin-text);">$100<span
                  style="font-size: 13px; font-weight: 400; color: var(--admin-text-muted);"> / month</span></div>
            </div>
            <div
              style="padding: 16px 24px; border-top: 1px solid var(--admin-border); background: rgba(255,255,255,0.4); backdrop-filter: blur(10px);">
              <button class="btn"
                style="width: 100%; justify-content: center; background: rgba(139,92,246,0.1); color: #8b5cf6; font-weight: 700;"
                onclick="window.location.href='settings/checkout.php?service=SEO %26 Marketing Suite&price=100'">Boost
                My Sales</button>
            </div>
          </div>

        </div>
      </div>

    </div>
  </main>

  <script src="assets/js/admin.js"></script>
</body>
