<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/api/config/constants.php';
require_once __DIR__ . '/api/config/database.php';

$db = (new Database())->getConnection();
$stmt = $db->query("SELECT lock_message FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$message = $settings['lock_message'] ?? "System disabled. Please contact the SaaS provider.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Locked | <?php echo htmlspecialchars(APP_NAME); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
        margin: 0;
        padding: 0;
        background: #121212;
        color: #fff;
        font-family: 'Hanken Grotesk', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        text-align: center;
    }
    .lock-container {
        background: #1e1e1e;
        padding: 40px;
        border-radius: 12px;
        border: 1px solid #333;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }
    .icon {
        color: #ef4444;
        margin-bottom: 20px;
    }
    h1 {
        font-family: 'Montserrat', sans-serif;
        margin: 0 0 16px 0;
        font-size: 24px;
        color: #f87171;
    }
    p {
        color: #9ca3af;
        line-height: 1.6;
        margin-bottom: 24px;
    }
    .btn {
        display: inline-block;
        padding: 12px 24px;
        background: #374151;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.2s;
    }
    .btn:hover {
        background: #4b5563;
    }
  </style>
</head>
<body>

  <div class="lock-container">
    <div class="icon">
        <svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>
    <h1>System Suspended</h1>
    <p><?php echo htmlspecialchars($message); ?></p>
    <a href="logout.php" class="btn">Logout</a>
  </div>

</body>
</html>
