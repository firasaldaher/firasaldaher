<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../api/config/constants.php';
require_once __DIR__ . '/../api/config/database.php';

$database = new Database();
$db = $database->getConnection();

$categories = [];
$products = [];

if ($db) {
    try {
        $categories = $db->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        $productsQuery = "
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC
        ";
        $products = $db->query($productsQuery)->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error fetching data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    body { font-family: 'Cairo', 'Hanken Grotesk', sans-serif; }
    .page-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .grid-container { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
    @media (max-width: 768px) { .grid-container { grid-template-columns: 1fr; } }
    .form-group { margin-bottom: 15px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid var(--admin-border); border-radius: 8px; font-family: inherit; }
    .btn-sm { padding: 6px 12px; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: right; border-bottom: 1px solid var(--admin-border); }
    th { color: var(--admin-text-muted); font-weight: 600; font-size: 14px; }
    td { color: var(--admin-text); font-size: 14px; }
  </style>
</head>
<body class="admin-body">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <main class="admin-main">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="admin-content">
      <div class="page-title">
        <h1 style="font-family: 'Cairo', sans-serif; font-size: 24px; color: var(--admin-text); font-weight: 700;">إدارة الأقسام والمنتجات</h1>
      </div>

      <?php if (isset($error)): ?>
        <div style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <div class="grid-container">
        
        <!-- Categories Section -->
        <div>
          <div class="card-panel" style="margin-bottom: 24px;">
            <h2 style="font-family: 'Cairo', sans-serif; font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">إضافة قسم جديد</h2>
            <form action="action.php" method="POST">
              <input type="hidden" name="action" value="add_category">
              <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="اسم القسم (مثال: عصائر طبيعية)" required>
              </div>
              <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">إضافة القسم</button>
            </form>
          </div>

          <div class="card-panel">
            <h2 style="font-family: 'Cairo', sans-serif; font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">الأقسام الحالية</h2>
            <div style="overflow-x: auto;">
              <table>
                <thead>
                  <tr>
                    <th>القسم</th>
                    <th style="width: 80px;">إجراء</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($categories as $cat): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td>
                      <form action="action.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none;">حذف</button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($categories)): ?>
                  <tr><td colspan="2" style="text-align: center;">لا توجد أقسام بعد.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Products Section -->
        <div>
          <div class="card-panel" style="margin-bottom: 24px;">
            <h2 style="font-family: 'Cairo', sans-serif; font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">إضافة منتج جديد</h2>
            <form action="action.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
              <input type="hidden" name="action" value="add_product">
              
              <div class="form-group" style="grid-column: span 2;">
                <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">اسم المنتج</label>
                <input type="text" name="name" class="form-control" placeholder="اسم المنتج (مثال: قهوة تركية)" required>
              </div>

              <div class="form-group">
                <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">القسم</label>
                <select name="category_id" class="form-control" required>
                  <option value="" disabled selected>اختر القسم...</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label style="display:block; margin-bottom:8px; font-size:14px; color:var(--admin-text-muted);">السعر (للزبون)</label>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
              </div>

              <button type="submit" class="btn btn-primary" style="grid-column: span 2; justify-content: center;">إضافة المنتج</button>
            </form>
          </div>

          <div class="card-panel">
            <h2 style="font-family: 'Cairo', sans-serif; font-size: 18px; color: var(--admin-text); font-weight: 700; margin-bottom: 16px;">المنتجات الحالية</h2>
            <div style="overflow-x: auto;">
              <table>
                <thead>
                  <tr>
                    <th>المنتج</th>
                    <th>القسم</th>
                    <th>السعر</th>
                    <th style="width: 80px;">إجراء</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $prod): ?>
                  <tr>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($prod['name']); ?></td>
                    <td>
                        <span style="background: var(--admin-bg); padding: 4px 8px; border-radius: 12px; font-size: 12px; color: var(--admin-text-muted);">
                            <?php echo htmlspecialchars($prod['category_name'] ?? 'بدون قسم'); ?>
                        </span>
                    </td>
                    <td style="color: var(--admin-success); font-weight: 600;"><?php echo number_format($prod['price'], 2); ?></td>
                    <td>
                      <form action="action.php" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                        <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none;">حذف</button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($products)): ?>
                  <tr><td colspan="4" style="text-align: center;">لا توجد منتجات بعد.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
</body>
</html>
