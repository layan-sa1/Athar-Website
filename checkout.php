<?php
/*
 * File: checkout.php
 * Author: [Deema AlGhamdi]
 * Group: [3]

 */

// Checkout page - collects customer info and places the order into the database
// IMPORTANT: db.php included first so session starts before any HTML output

require_once 'includes/db.php';

// Redirect to cart if cart is empty - must happen before any HTML
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// --- HANDLE FORM SUBMISSION ---
// Process the order BEFORE including header.php (which outputs HTML)
$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email']     ?? '');
    $phone    = trim($_POST['phone']     ?? '');

if (!$fullName || !$phone) {
    $error = "Please fill in your name and phone number.";
} else {

    // Re-check stock before placing the order
    foreach ($_SESSION['cart'] as $productId => $item) {

        $stmt = $conn->prepare("SELECT name, stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Product removed or stock not enough
        if (!$product || $item['qty'] > $product['stock']) {
            $error = 'Sorry, "' . ($product['name'] ?? 'Product') . '" is no longer available in the requested quantity.';
            break;
        }
    }

    // Only continue if stock is still available
    if (!$error) {

	// Calculate total
	$cartTotal = 0;
	foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['qty'];
}

	// Reduce stock only
	foreach ($_SESSION['cart'] as $productId => $item) {

    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $stmt->bind_param("iii", $item['qty'], $productId, $item['qty']);
    $stmt->execute();

    // If stock update failed
    if ($stmt->affected_rows === 0) {
        $error = 'Sorry, "' . $item['name'] . '" is out of stock.';
        $stmt->close();
        break;
    }

    $stmt->close();
}
	if (!$error) {
        // Save past purchases cookie
        $existingCookie = isset($_COOKIE['pastPurchases']) ? $_COOKIE['pastPurchases'] : '';
        $allOrders = $existingCookie ? explode('|', $existingCookie) : [];

        foreach ($_SESSION['cart'] as $item) {
            $allOrders[] = $item['name'] . '::' . number_format($item['price'], 2) . ' SAR::' . $item['qty'] . '::' . date('Y-m-d');
        }

        // Keep only last 5 purchases
        $allOrders = array_slice($allOrders, -5);

        setcookie('pastPurchases', implode('|', $allOrders), time() + (30 * 24 * 60 * 60), '/');

        // Clear the cart
        $_SESSION['cart'] = [];
        $success = true;
    }
}
}
}
// Calculate total for the summary (only if not already calculated above)
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['qty'];
}

// Now safe to output HTML
$pageTitle  = "Checkout";
$activePage = "";
require_once 'includes/header.php';
?>

<style>
  .page-title{text-align:center;font-size:36px;margin:36px 0 30px;color:#2F6B3A;font-weight:700;}
  .checkout-wrap{max-width:1100px;margin:0 auto;padding:0 40px 60px;display:grid;grid-template-columns:2fr 1fr;gap:28px;align-items:start;}
  .checkout-form{background:white;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:30px;}
  .checkout-form h2{color:#2F6B3A;font-size:20px;font-weight:700;margin-bottom:20px;}
  .form-group{margin-bottom:16px;}
  .form-group label{display:block;font-weight:600;font-size:14px;color:#2F6B3A;margin-bottom:6px;}
  .form-group input{width:100%;padding:11px 14px;border:1.5px solid #d0d8d0;border-radius:8px;font-size:15px;color:#333;}
  .form-group input:focus{outline:none;border-color:#4CAF50;}
  .co-summary{background:white;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:24px;}
  .co-item{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f0f0;font-size:15px;color:#555;}
  .summary-total{display:flex;justify-content:space-between;padding:14px 0 8px;font-size:18px;font-weight:700;color:#2F6B3A;}
  .btn-buy{display:block;width:100%;text-align:center;background:#2F6B3A;color:white;padding:15px;border-radius:10px;font-size:16px;font-weight:700;border:none;cursor:pointer;margin-top:14px;}
  .btn-buy:hover{background:#1d4a25;}
  .success-box{text-align:center;padding:60px 20px;}
  @media(max-width:768px){.checkout-wrap{grid-template-columns:1fr;padding:0 20px 40px;}}
</style>

<main id="main-content">
  <h1 class="page-title">Checkout</h1>

  <?php if ($success) : ?>
    <div class="success-box">
      <div style="font-size:60px;margin-bottom:20px;">🎉</div>
      <h2 style="color:#2F6B3A;font-size:28px;margin-bottom:14px;">Order Placed Successfully!</h2>
      <p style="color:#555;font-size:17px;margin-bottom:30px;">Thank you for shopping with Athar. Your eco-friendly order is on its way!</p>
      <a href="home.php" class="btn">Back to Home</a>
    </div>

  <?php else : ?>

    <?php if ($error) : ?>
      <div class="flash flash-error" style="max-width:800px;margin:0 auto 10px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="checkout-wrap">

      <!-- DELIVERY FORM -->
      <div class="checkout-form">
        <h2>📦 Delivery Information</h2>
        <form method="POST" action="checkout.php" id="checkoutForm">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" placeholder="Your full name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Phone Number *</label>
            <input type="tel" name="phone" id="phone" placeholder="+966 5XX XXX XXXX" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Delivery Address</label>
            <input type="text" name="address" placeholder="Street, City, Region" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
          </div>
          <button class="btn-buy" type="submit">✅ Place Order</button>
          <a href="cart.php" style="display:block;text-align:center;background:#8FBF8F;color:white;padding:12px;border-radius:10px;font-size:15px;margin-top:10px;text-decoration:none;">← Back to Cart</a>
        </form>
      </div>

      <!-- ORDER SUMMARY -->
      <div class="co-summary">
        <h2 style="color:#2F6B3A;font-size:20px;font-weight:700;margin-bottom:18px;">Order Summary</h2>
        <?php foreach ($_SESSION['cart'] as $item) : ?>
          <div class="co-item">
            <span><?= htmlspecialchars($item['name']) ?> &times; <?= $item['qty'] ?></span>
            <span><?= number_format($item['price'] * $item['qty'], 2) ?> SAR</span>
          </div>
        <?php endforeach; ?>
        <div class="co-item"><span>Shipping</span><span style="color:#4CAF50;font-weight:700;">Free</span></div>
        <div class="summary-total"><span>Total</span><span><?= number_format($cartTotal, 2) ?> SAR</span></div>
        <p style="color:#888;font-size:13px;margin-top:10px;">⚠️ Once placed, orders cannot be modified.</p>
      </div>

    </div>
  <?php endif; ?>
</main>

<script>
// JS validation for checkout form (Task 13)
document.getElementById('checkoutForm') && document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    var name  = this.querySelector('[name="full_name"]').value.trim();
    var phone = document.getElementById('phone').value.trim();

    if (!name) {
        e.preventDefault();
        alert('Please enter your full name.');
        return;
    }
    if (!phone) {
        e.preventDefault();
        alert('Please enter your phone number.');
        return;
    }
    var phonePattern = /^[\d\s\+\-\(\)]{7,20}$/;
    if (!phonePattern.test(phone)) {
        e.preventDefault();
        alert('Please enter a valid phone number (e.g. +966 5XX XXX XXXX).');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
