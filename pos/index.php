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
        $catStmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        $prodStmt = $db->query("SELECT * FROM products ORDER BY name ASC");
        $products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Point of Sale | <?php echo htmlspecialchars(APP_NAME); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@300;400;500;600;700&family=Montserrat:wght@200;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .pos-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    .products-area { display: flex; flex-direction: column; gap: 16px; }
    
    .category-filters { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; }
    .category-btn { padding: 8px 16px; border-radius: 20px; border: 1px solid var(--admin-border); background: #fff; cursor: pointer; white-space: nowrap; font-family: var(--font-body); }
    .category-btn.active { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); }
    
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; }
    .product-card { background: #fff; padding: 16px; border-radius: 12px; text-align: center; cursor: pointer; border: 1px solid var(--admin-border); transition: 0.2s; user-select: none; }
    .product-card:hover { border-color: var(--admin-primary); box-shadow: 0 4px 12px rgba(212,175,55,0.1); transform: translateY(-2px); }
    .product-title { font-weight: 600; margin-bottom: 8px; font-size: 14px; }
    .product-price { color: var(--admin-primary); font-weight: 700; }
    
    .cart-area { background: #fff; border-radius: 12px; padding: 24px; border: 1px solid var(--admin-border); display: flex; flex-direction: column; height: calc(100vh - 140px); position: sticky; top: 100px; }
    .order-type-toggle { display: flex; background: var(--admin-bg); border-radius: 8px; padding: 4px; margin-bottom: 16px; }
    .order-type-btn { flex: 1; text-align: center; padding: 8px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; color: var(--admin-text-muted); }
    .order-type-btn.active { background: #fff; color: var(--admin-text); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .customer-info { margin-bottom: 16px; display: none; }
    .customer-info.show { display: block; }
    .customer-info input { width: 100%; padding: 10px; border: 1px solid var(--admin-border); border-radius: 8px; font-family: var(--font-body); }
    
    .cart-items { flex-grow: 1; overflow-y: auto; margin-bottom: 24px; display: flex; flex-direction: column; gap: 12px; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--admin-bg); border-radius: 8px; }
    .cart-item-details { flex-grow: 1; }
    .cart-item-title { font-weight: 600; font-size: 14px; }
    .cart-item-price { color: var(--admin-primary); font-size: 13px; font-weight: 600; }
    .cart-item-controls { display: flex; align-items: center; gap: 8px; }
    .qty-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--admin-border); background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: bold; }
    .qty-btn:hover { background: var(--admin-primary); color: #fff; border-color: var(--admin-primary); }
    .qty-display { font-weight: 600; min-width: 20px; text-align: center; }
    
    .cart-total { display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; margin-bottom: 16px; padding-top: 16px; border-top: 2px dashed var(--admin-border); }
    .checkout-btn { width: 100%; justify-content: center; font-size: 16px; padding: 16px; border-radius: 8px; }
    
    @media (max-width: 992px) {
        .pos-grid { grid-template-columns: 1fr; }
        .cart-area { height: auto; position: static; }
    }
  </style>
</head>
<body class="admin-body">
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="admin-main">
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <div class="admin-content">
      <div class="page-title" style="margin-bottom: 24px;">
        <h1 style="font-family: var(--font-head); font-size: 24px; color: var(--admin-text); font-weight: 700;">Point of Sale (POS)</h1>
        <div style="color: var(--admin-text-muted); font-size: 14px; margin-top: 4px;">New Order</div>
      </div>
      
      <?php if(isset($error)): ?>
        <div style="background: rgba(239,68,68,0.1); color: #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 24px;"><?= $error ?></div>
      <?php endif; ?>

      <div class="pos-grid">
        <!-- Products Area -->
        <div class="products-area">
            <div class="category-filters" id="categoryFilters">
                <button class="category-btn active" data-id="all">All</button>
                <?php foreach($categories as $cat): ?>
                    <button class="category-btn" data-id="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></button>
                <?php endforeach; ?>
            </div>
            
            <div class="products-grid" id="productsGrid">
                <?php foreach($products as $prod): ?>
                    <div class="product-card" data-id="<?= $prod['id'] ?>" data-cat="<?= $prod['category_id'] ?>" data-price="<?= $prod['price'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>">
                        <div class="product-title"><?= htmlspecialchars($prod['name']) ?></div>
                        <div class="product-price">$<?= number_format($prod['price'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($products)): ?>
                    <p style="color:var(--admin-text-muted);">No products added yet. You can add them from the Products section.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Cart Area -->
        <div class="cart-area">
            <div class="order-type-toggle">
                <div class="order-type-btn active" data-type="takeaway">Takeaway</div>
                <div class="order-type-btn" data-type="dine_in">Dine-in</div>
            </div>
            
            <div class="customer-info" id="customerInfo">
                <input type="text" id="tableNumber" placeholder="Table number or Customer name..." autocomplete="off">
            </div>

            <div class="cart-items" id="cartItems">
                <div style="text-align: center; color: var(--admin-text-muted); padding: 40px 0;">Cart is empty</div>
            </div>
            
            <div>
                <div class="cart-total">
                    <span>Total</span>
                    <span id="cartTotalDisplay">$0.00</span>
                </div>
                <button class="btn btn-primary checkout-btn" id="checkoutBtn" disabled>Complete Order</button>
            </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/js/admin.js"></script>
  <script>
      const cart = {};
      let orderType = 'takeaway';
      
      const cartItemsContainer = document.getElementById('cartItems');
      const cartTotalDisplay = document.getElementById('cartTotalDisplay');
      const checkoutBtn = document.getElementById('checkoutBtn');
      const customerInfo = document.getElementById('customerInfo');
      const tableNumberInput = document.getElementById('tableNumber');
      
      // Order Type Toggle
      document.querySelectorAll('.order-type-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              document.querySelectorAll('.order-type-btn').forEach(b => b.classList.remove('active'));
              e.target.classList.add('active');
              orderType = e.target.dataset.type;
              
              if(orderType === 'dine_in') {
                  customerInfo.classList.add('show');
              } else {
                  customerInfo.classList.remove('show');
              }
          });
      });

      // Category Filter
      document.querySelectorAll('.category-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
              e.target.classList.add('active');
              
              const catId = e.target.dataset.id;
              document.querySelectorAll('.product-card').forEach(card => {
                  if(catId === 'all' || card.dataset.cat === catId) {
                      card.style.display = 'block';
                  } else {
                      card.style.display = 'none';
                  }
              });
          });
      });

      // Add to Cart
      document.querySelectorAll('.product-card').forEach(card => {
          card.addEventListener('click', () => {
              const id = card.dataset.id;
              const name = card.dataset.name;
              const price = parseFloat(card.dataset.price);
              
              if(cart[id]) {
                  cart[id].qty++;
              } else {
                  cart[id] = { id, name, price, qty: 1 };
              }
              renderCart();
          });
      });

      function updateQty(id, change) {
          if(cart[id]) {
              cart[id].qty += change;
              if(cart[id].qty <= 0) {
                  delete cart[id];
              }
              renderCart();
          }
      }

      function renderCart() {
          let total = 0;
          cartItemsContainer.innerHTML = '';
          
          const keys = Object.keys(cart);
          if(keys.length === 0) {
              cartItemsContainer.innerHTML = '<div style="text-align: center; color: var(--admin-text-muted); padding: 40px 0;">Cart is empty</div>';
              checkoutBtn.disabled = true;
              cartTotalDisplay.innerText = '$0.00';
              return;
          }
          
          checkoutBtn.disabled = false;
          
          keys.forEach(id => {
              const item = cart[id];
              const itemTotal = item.price * item.qty;
              total += itemTotal;
              
              const div = document.createElement('div');
              div.className = 'cart-item';
              div.innerHTML = `
                  <div class="cart-item-details">
                      <div class="cart-item-title">${item.name}</div>
                      <div class="cart-item-price">$${itemTotal.toFixed(2)}</div>
                  </div>
                  <div class="cart-item-controls">
                      <button class="qty-btn" onclick="updateQty('${id}', -1)">-</button>
                      <span class="qty-display">${item.qty}</span>
                      <button class="qty-btn" onclick="updateQty('${id}', 1)">+</button>
                  </div>
              `;
              cartItemsContainer.appendChild(div);
          });
          
          cartTotalDisplay.innerText = '$' + total.toFixed(2);
      }

      // Checkout Process
      checkoutBtn.addEventListener('click', () => {
          if(Object.keys(cart).length === 0) return;
          
          const items = Object.values(cart);
          const totalText = cartTotalDisplay.innerText.replace('$', '');
          
          const payload = {
              order_type: orderType,
              customer_info: tableNumberInput.value.trim(),
              total: parseFloat(totalText),
              items: items
          };
          
          checkoutBtn.disabled = true;
          checkoutBtn.innerText = 'Processing...';
          
          fetch('../api/pos/create_order.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          })
          .then(res => res.json())
          .then(data => {
              if(data.status === 'success') {
                  alert('Order created successfully! Order #: ' + data.order_id);
                  // Reset Cart
                  for (let prop in cart) { delete cart[prop]; }
                  tableNumberInput.value = '';
                  renderCart();
              } else {
                  alert('Error: ' + data.message);
              }
          })
          .catch(err => {
              alert('Connection error with the server.');
              console.error(err);
          })
          .finally(() => {
              checkoutBtn.innerText = 'Complete Order';
              if(Object.keys(cart).length > 0) checkoutBtn.disabled = false;
          });
      });
  </script>
</body>
</html>
