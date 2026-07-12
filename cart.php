<?php
/*
 * File: cart.php
 * Author: [Deema AlGhamdi]
 * Group: [3]

 */

// Cart page - handles adding, removing, updating items, and displaying the cart
// The cart is stored in the PHP session as an array

// IMPORTANT: db.php must be included FIRST before any output
// This is because cart actions use header() to redirect,
// and header() cannot be called after any HTML has been sent
require_once 'includes/db.php';

// --- HANDLE CART ACTIONS ---
// All redirects happen HERE before header.php outputs any HTML

$action = $_REQUEST['action'] ?? '';

if ($action === 'add') {
    $id  = intval($_REQUEST['id'] ?? 0);
    $qty = intval($_REQUEST['qty'] ?? 1);

    if ($id > 0) {
        // Check the product exists and get its stock from the database
        $stmt = $conn->prepare("SELECT id, name, price, picture, stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($p) {
            // Check how many are already in the cart
            $alreadyInCart  = $_SESSION['cart'][$id]['qty'] ?? 0;
            $totalRequested = $alreadyInCart + $qty;

            // Make sure the total doesn't exceed available stock
            if ($totalRequested > $p['stock']) {
                $_SESSION['cart_error'] = "Sorry, only " . $p['stock'] . " item(s) of \"" . $p['name'] . "\" are available in stock.";
            } else {
                if (isset($_SESSION['cart'][$id])) {
                    // Product already in cart - increase quantity
                    $_SESSION['cart'][$id]['qty'] += $qty;
                } else {
                    // New item - add to cart
                    $_SESSION['cart'][$id] = [
                        'name'    => $p['name'],
                        'price'   => $p['price'],
                        'picture' => $p['picture'],
                        'qty'     => $qty
                    ];
                }
            }
        }
    }
    $redirect = $_REQUEST['redirect'] ?? 'cart.php';
	header("Location: " . $redirect);
	exit;
}

if ($action === 'remove') {
    $id = intval($_POST['id'] ?? 0);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

if ($action === 'update_and_checkout') {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id  = intval($id);
            $qty = intval($qty);
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } elseif (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] = $qty;
            }
        }
    }
    header("Location: checkout.php");
    exit;
}
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// --- CALCULATE TOTALS ---
// Only runs if no redirect happened above
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['qty'];
}

// Now it is safe to output HTML
$pageTitle  = "Your Cart";
$activePage = "";
require_once 'includes/header.php';
?>

<style>
  .page-title{text-align:center;font-size:36px;margin:36px 0 8px;color:#2F6B3A;font-weight:700;}
  .page-subtitle{text-align:center;color:#666;margin-bottom:30px;font-size:16px;}
  .cart-wrap{max-width:1100px;margin:0 auto;padding:0 40px 60px;display:grid;grid-template-columns:2fr 1fr;gap:28px;align-items:start;}
  .cart-items{background:white;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);overflow:hidden;}
  .cart-header-row,.cart-item{display:grid;grid-template-columns:2.2fr 1fr 1fr 1fr 52px;gap:14px;align-items:center;padding:14px 20px;}
  .cart-header-row{background:#f8f8f8;font-weight:700;color:#555;font-size:13px;text-transform:uppercase;border-bottom:1px solid #eee;}
  .cart-item{border-bottom:1px solid #f0f0f0;}
  .cart-item:last-child{border-bottom:none;}
  .cart-product{display:flex;align-items:center;gap:12px;}
  .cart-product img{width:56px;height:56px;object-fit:cover;border-radius:10px;}
  .cart-product h3{font-size:16px;color:#2F6B3A;font-weight:700;}
  .qty-input{width:60px;padding:7px;border:1.5px solid #ccc;border-radius:8px;font-size:15px;text-align:center;}
  .remove-btn{background:#f1f1f1;color:#888;width:32px;height:32px;border-radius:50%;border:none;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
  .remove-btn:hover{background:#ffdddd;color:#c0392b;}
  .cart-summary{background:white;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:24px;}
  .summary-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f0f0;color:#555;font-size:15px;}
  .summary-total{display:flex;justify-content:space-between;padding:14px 0 8px;font-size:18px;font-weight:700;color:#2F6B3A;}
  .btn-checkout{
  display:block;
  width:100%;
  text-align:center;
  background:#4CAF50;
  color:white;
  padding:14px;
  border-radius:10px;
  font-size:16px;
  font-weight:700;
  margin-top:14px;
  text-decoration:none;
  border:none;
  cursor:pointer;
  box-sizing:border-box;
}

  .btn-checkout:hover{background:#1d4a25;}
  .btn-continue{display:block;text-align:center;background:#8FBF8F;color:white;padding:12px;border-radius:10px;font-size:15px;margin-top:10px;text-decoration:none;}
  @media(max-width:768px){.cart-wrap{grid-template-columns:1fr;padding:0 20px 40px;}.cart-header-row,.cart-item{grid-template-columns:1fr 1fr;}}
</style>

<main id="main-content">
  <h1 class="page-title">Your Cart</h1>
  <p class="page-subtitle">Review your selected items before checkout.</p>

  <?php
  // Show stock error if one was set during the add action
  if (isset($_SESSION['cart_error'])) {
      echo '<div class="flash flash-error" style="max-width:1100px;margin:0 auto 10px;">' . htmlspecialchars($_SESSION['cart_error']) . '</div>';
      unset($_SESSION['cart_error']);
  }
  ?>

  <?php if (empty($_SESSION['cart'])) : ?>
    <div style="text-align:center;padding:80px 20px;">
      <p style="font-size:20px;color:#666;margin-bottom:20px;">🛒 Your cart is empty.</p>
      <a href="products.php" class="btn">Start Shopping →</a>
    </div>

  <?php else : ?>
  <div class="cart-wrap">

    <!-- CART ITEMS - wrapped in a form so we can update all quantities at once -->
   <form method="POST" action="cart.php" id="updateForm">
      <input type="hidden" name="action" value="update">
      <div class="cart-items">
        <div class="cart-header-row">
          <span>Product</span><span>Price</span><span>Qty</span><span>Total</span><span></span>
        </div>

        <?php foreach ($_SESSION['cart'] as $id => $item) :
          $lineTotal = $item['price'] * $item['qty'];
        ?>
        <div class="cart-item">
          <div class="cart-product">
            <img src="images/<?= htmlspecialchars($item['picture']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div>
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <p style="font-size:12px;color:#888;">Eco-friendly product</p>
            </div>
          </div>
          <div style="font-size:15px;color:#2F6B3A;font-weight:600;"><?= number_format($item['price'], 2) ?> SAR</div>
          <!-- Quantity input - named with product ID so update knows which item to change -->
          <input class="qty-input" type="number" name="quantities[<?= $id ?>]" value="<?= $item['qty'] ?>" min="1" max="99">
          <div style="font-size:15px;color:#2F6B3A;font-weight:600;"><?= number_format($lineTotal, 2) ?> SAR</div>
          <!-- Remove button uses a separate small form to avoid interfering with the update form -->
          <form method="POST" action="cart.php" style="margin:0;">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="remove-btn">&times;</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>



    <!-- Clear cart as a separate form -->
    <form method="POST" action="cart.php" style="display:none;" id="clearForm">
      <input type="hidden" name="action" value="clear">
    </form>

    <!-- ORDER SUMMARY -->
    <div class="cart-summary">
      <h2 style="color:#2F6B3A;font-size:20px;font-weight:700;margin-bottom:18px;">Order Summary</h2>
      <div class="summary-row"><span>Subtotal</span><span><?= number_format($cartTotal, 2) ?> SAR</span></div>
      <div class="summary-row"><span>Shipping</span><span style="color:#4CAF50;font-weight:700;">Free</span></div>
      <div class="summary-total"><span>Total</span><span><?= number_format($cartTotal, 2) ?> SAR</span></div>
     <button type="submit" form="updateForm" name="action" value="update_and_checkout" class="btn-checkout">Checkout →</button>
      <a href="products.php" class="btn-continue">← Continue Shopping</a>
      <button onclick="if(confirm('Empty the cart?')) document.getElementById('clearForm').submit();"
              class="btn-continue" style="background:#e74c3c;border:none;cursor:pointer;width:100%;margin-top:8px;">
        🗑 Clear Cart
      </button>
    </div>

  </div>
  <?php endif; ?>
</main>
<script>
document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var row   = this.closest('.cart-item');
        var price = parseFloat(row.querySelectorAll('div[style*="font-weight:600"]')[0].innerText);
        var qty   = parseFloat(this.value) || 0;

        // Update line total
        row.querySelectorAll('div[style*="font-weight:600"]')[1].innerText = (price * qty).toFixed(2) + ' SAR';

        // Recalculate cart total
        var total = 0;
        document.querySelectorAll('.cart-item').forEach(function(r) {
            total += parseFloat(r.querySelectorAll('div[style*="font-weight:600"]')[1].innerText) || 0;
        });

        // Update summary
        document.querySelectorAll('.summary-row span:last-child').forEach(function(el) {
            if (el.innerText.includes('SAR')) el.innerText = total.toFixed(2) + ' SAR';
        });
        document.querySelector('.summary-total span:last-child').innerText = total.toFixed(2) + ' SAR';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>


