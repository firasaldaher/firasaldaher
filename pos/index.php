<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Point of Sale | Caravan POS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .pos-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    .products-area { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 16px; }
    .product-card { background: #fff; padding: 16px; border-radius: 12px; text-align: center; cursor: pointer; border: 1px solid var(--admin-border); transition: 0.2s; }
    .product-card:hover { border-color: var(--admin-primary); box-shadow: 0 4px 12px rgba(212,175,55,0.1); }
    .product-title { font-weight: 600; margin-bottom: 8px; }
    .product-price { color: var(--admin-primary); font-weight: 700; }
    
    .cart-area { background: #fff; border-radius: 12px; padding: 24px; border: 1px solid var(--admin-border); display: flex; flex-direction: column; height: 100%; }
    .cart-items { flex-grow: 1; overflow-y: auto; margin-bottom: 24px; }
    .cart-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed var(--admin-border); }
    .cart-total { display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; margin-bottom: 24px; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="admin-main">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Point of Sale</h1>
          <div class="page-subtitle">New Order</div>
        </div>
      </div>
      
      <div class="pos-grid">
        <!-- Products Grid -->
        <div class="products-area">
            <div class="product-card">
                <div class="product-title">Espresso</div>
                <div class="product-price">$2.50</div>
            </div>
            <div class="product-card">
                <div class="product-title">Latte</div>
                <div class="product-price">$3.50</div>
            </div>
            <div class="product-card">
                <div class="product-title">Croissant</div>
                <div class="product-price">$4.00</div>
            </div>
            <div class="product-card">
                <div class="product-title">Water</div>
                <div class="product-price">$1.00</div>
            </div>
        </div>
        
        <!-- Cart -->
        <div class="cart-area">
            <h2 style="font-family: var(--font-head); margin-bottom: 16px;">Current Order</h2>
            <div class="cart-items">
                <div style="text-align: center; color: var(--admin-text-muted); padding: 40px 0;">No items in cart</div>
            </div>
            <div>
                <div class="cart-total">
                    <span>Total</span>
                    <span>$0.00</span>
                </div>
                <button class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 16px; padding: 12px;">Checkout</button>
            </div>
        </div>
      </div>
    </div>
  </main>
  <script src="../assets/js/admin.js"></script>
</body>
</html>
