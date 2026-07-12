<?php
/*
 * File: product_details.php
 * Author: [Layan AlTamimi]
 * Group: [3]
 */

// Product details page - shows full info for one product
// The product ID comes from the URL like: product_details.php?id=3

require_once 'includes/db.php';

// Get the product ID from the URL and make sure it's a number
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID was given, send the user back to the shop
if ($productId <= 0) {
    header("Location: products.php");
    exit;
}

// Fetch the product from the database using the ID
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// If no product was found with that ID, redirect to the shop
if (!$product) {
    header("Location: products.php");
    exit;
}

// Set page title using the actual product name
$pageTitle  = $product['name'];
$activePage = "products";
require_once 'includes/header.php';

// Split the features string into an array (they are separated by | in the database)
$features = explode("|", $product['features']);
?>

<style>
  .pd-title{font-size:32px;font-weight:700;color:#2F6B3A;text-align:center;margin:30px 0 8px;}
  .pd-breadcrumb{text-align:center;font-size:14px;color:#666;margin-bottom:24px;}
  .pd-breadcrumb a{color:#4CAF50;}
  .pd-wrapper{max-width:1100px;margin:0 auto;padding:0 40px 60px;}
  .pd-top{display:flex;gap:40px;align-items:flex-start;flex-wrap:wrap;margin-bottom:36px;}
  .pd-img{flex:1;min-width:280px;max-width:420px;}
  .pd-img img{width:100%;border-radius:14px;object-fit:cover;box-shadow:0 6px 18px rgba(0,0,0,.12);}
  .pd-info{flex:1;min-width:280px;}
  .pd-name{font-size:26px;font-weight:700;color:#2F6B3A;margin-bottom:8px;}
  .pd-short{font-size:15px;color:#555;line-height:1.7;margin-bottom:20px;}
  .pd-box{background:white;border-radius:12px;padding:22px;box-shadow:0 4px 16px rgba(0,0,0,.09);}
  .pd-price-row{display:flex;align-items:center;gap:14px;margin-bottom:14px;}
  .pd-price{font-size:28px;font-weight:700;color:#2F6B3A;}
  .pd-stock{border:1.5px solid #4CAF50;color:#2F6B3A;padding:4px 14px;border-radius:20px;font-size:14px;}
  .pd-qty{width:80px;padding:9px 12px;border:1.5px solid #ccc;border-radius:8px;font-size:15px;margin-bottom:16px;display:block;}
  .pd-add-btn{width:100%;background:#4CAF50;color:white;border:none;padding:14px;border-radius:10px;font-size:16px;cursor:pointer;font-weight:600;}
  .pd-add-btn:hover{background:#2F6B3A;}
  .pd-section-title{font-size:22px;font-weight:700;color:#2F6B3A;margin-bottom:6px;}
  .pd-bottom{display:flex;gap:22px;flex-wrap:wrap;}
  .pd-about{flex:1;min-width:260px;background:white;border-radius:12px;padding:24px;box-shadow:0 4px 12px rgba(0,0,0,.07);}
  .pd-features{flex:1;min-width:260px;background:#E9F2E3;border-radius:12px;padding:24px;}
  .pd-features ul{list-style:none;padding:0;display:flex;flex-direction:column;gap:9px;}
  .pd-features li::before{content:"🌿 ";}
</style>

<main id="main-content">
  <h1 class="pd-title"><?= htmlspecialchars($product['name']) ?></h1>

  <!-- Breadcrumb so users can easily go back to the shop -->
  <p class="pd-breadcrumb">
    <a href="home.php">Home</a> › <a href="products.php">Shop</a> › <?= htmlspecialchars($product['name']) ?>
  </p>

  <div class="pd-wrapper">
    <div class="pd-top">

      <!-- Product image -->
      <div class="pd-img">
        <img src="images/<?= htmlspecialchars($product['picture']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
      </div>

      <!-- Product info and add to cart -->
      <div class="pd-info">
        <h2 class="pd-name"><?= htmlspecialchars($product['name']) ?></h2>
        <p style="color:#4CAF50;font-size:20px;margin-bottom:10px;">★★★★★</p>
        <p class="pd-short"><?= htmlspecialchars($product['short_description']) ?></p>

        <div class="pd-box">
          <div class="pd-price-row">
            <span class="pd-price"><?= number_format($product['price'], 2) ?> SAR</span>
            <!-- Show stock status based on what's in the database -->
            <?php if ($product['stock'] > 0) : ?>
              <span class="pd-stock">✔ In Stock (<?= $product['stock'] ?> left)</span>
            <?php else : ?>
              <span class="pd-stock" style="border-color:#e74c3c;color:#e74c3c;">✖ Out of Stock</span>
            <?php endif; ?>
          </div>
          <hr style="border:none;border-top:1px solid #eee;margin:0 0 16px;">

          <?php if ($product['stock'] > 0) : ?>
          <!-- Add to cart form - sends product ID and quantity to cart.php -->
          <form action="cart.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
			<input type="hidden" name="redirect" value="product_details.php?id=<?= $product['id'] ?>">
            <label style="font-size:14px;color:#444;margin-bottom:6px;display:block;font-weight:600;">Select Quantity:</label>
            <!-- Max is set from the actual stock in the database -->
            <input class="pd-qty" type="number" name="qty" value="1" min="1" max="<?= $product['stock'] ?>">
            <button class="pd-add-btn" type="submit">🛒 Add to Cart</button>
          </form>
          <?php else : ?>
            <p style="color:#e74c3c;font-weight:600;text-align:center;padding:14px;">This product is currently out of stock.</p>
          <?php endif; ?>
        </div>

        <div style="display:flex;gap:12px;justify-content:center;margin-top:18px;flex-wrap:wrap;">
          <a href="products.php" style="background:#8FBF8F;color:white;padding:10px 22px;border-radius:8px;font-size:15px;text-decoration:none;">← Continue Shopping</a>
          <a href="cart.php"     style="background:#4CAF50;color:white;padding:10px 22px;border-radius:8px;font-size:15px;text-decoration:none;">View Cart →</a>
        </div>
      </div>

    </div><!-- end pd-top -->

    <!-- PRODUCT DESCRIPTION AND FEATURES -->
    <h2 class="pd-section-title">Product Details</h2>
    <hr style="border:none;border-top:2px solid #2F6B3A;margin-bottom:22px;">
    <div class="pd-bottom">
      <div class="pd-about">
        <h3 style="color:#2F6B3A;font-size:17px;margin-bottom:10px;">About this product</h3>
        <p style="color:#444;font-size:14px;line-height:1.8;"><?= htmlspecialchars($product['description']) ?></p>
        <span style="display:inline-block;margin-top:14px;background:#E9F2E3;color:#2F6B3A;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:700;">
          <?= htmlspecialchars($product['category']) ?>
        </span>
      </div>
      <div class="pd-features">
        <h3 style="color:#2F6B3A;font-size:17px;margin-bottom:10px;">Key Features</h3>
        <ul>
          <?php foreach ($features as $feature) : ?>
            <li style="color:#333;font-size:14px;"><?= htmlspecialchars(trim($feature)) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- HELP BUTTON - opens a popup window (Task 14) -->
    <div style="text-align:center;margin:32px 0;">
      <button onclick="openHelpWindow()" style="background:#E9F2E3;color:#2F6B3A;padding:10px 26px;border-radius:22px;cursor:pointer;font-size:15px;font-weight:700;border:none;">
        💬 Need Help?
      </button>
    </div>

  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
<!-- This script is added just before footer.php closes the body tag -->
<script>
// ===== HELP POPUP WINDOW (Task 14) =====
// Opens a small popup window with product help info when the user clicks "Need Help?"
function openHelpWindow() {
    // Open a small popup window - width and height are set to keep it compact
    var helpWin = window.open('', 'HelpWindow', 'width=480,height=380,scrollbars=yes,resizable=yes');

    // Write the help content directly into the popup window
    helpWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Help - Athar Eco Living</title>
            <style>
                body { font-family: Arial, sans-serif; background: #F6F4EF; color: #2F6B3A; padding: 24px; }
                h2 { font-size: 20px; margin-bottom: 16px; border-bottom: 2px solid #2F6B3A; padding-bottom: 8px; }
                .help-item { margin-bottom: 14px; }
                .help-item strong { display: block; color: #2F6B3A; margin-bottom: 4px; }
                .help-item p { color: #555; font-size: 14px; line-height: 1.6; }
                .close-btn { background: #4CAF50; color: white; border: none; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-size: 15px; margin-top: 10px; }
                .close-btn:hover { background: #2F6B3A; }
            </style>
        </head>
        <body>
            <h2>💚 Product Help</h2>
            <div class="help-item">
                <strong>How do I add a product to my cart?</strong>
                <p>Enter the quantity you want in the quantity box and click "Add to Cart". The item will be saved until you checkout.</p>
            </div>
            <div class="help-item">
                <strong>What does "In Stock" mean?</strong>
                <p>It shows how many units are currently available. You cannot order more than the available stock.</p>
            </div>
            <div class="help-item">
                <strong>Can I change my quantity later?</strong>
                <p>Yes! Go to your cart and update the quantity field for any product, then click "Update Cart".</p>
            </div>
            <div class="help-item">
                <strong>Are the products eco-certified?</strong>
                <p>All Athar products are sustainably sourced and eco-certified. Check the Key Features section for specific certifications.</p>
            </div>
            <button class="close-btn" onclick="window.close()">Close Help</button>
        </body>
        </html>
    `);
    helpWin.document.close();
}

// ===== JS FORM VALIDATION (Task 13) =====
// Validates the quantity input before submitting the Add to Cart form
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form[action="cart.php"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            var qtyInput = document.querySelector('input[name="qty"]');
            var qty = parseInt(qtyInput.value);
            var max = parseInt(qtyInput.getAttribute('max'));

            // Check quantity is a valid positive number
            if (isNaN(qty) || qty < 1) {
                e.preventDefault(); // Stop form submitting
                alert('Please enter a valid quantity (minimum 1).');
                qtyInput.focus();
                return;
            }
            // Check quantity doesn't exceed available stock
            if (qty > max) {
                e.preventDefault();
                alert('Sorry, only ' + max + ' item(s) are available in stock.');
                qtyInput.value = max;
                qtyInput.focus();
            }
        });
    }
});
</script>
