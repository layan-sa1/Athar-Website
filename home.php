<?php
/*
 * File: home.php
 * Author: [Reema AlMulla]
 * Group: [3]
 
 */

// Home page - shows hero, recent purchases, categories, featured products, and about section

// Connect to the database
require_once 'includes/db.php';

// Set variables for the header (page title and which nav link to highlight)
$pageTitle  = "Home";
$activePage = "home";
require_once 'includes/header.php';

// Get the 4 most recently added products to show in the featured section
// We use LIMIT 4 so we only get 4 rows back
$featuredResult = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");

// Get past purchases from the cookie (Task 12 - Cookies)
// The cookie "pastPurchases" is set in checkout.php when an order is placed
// Each item is stored as: ProductName::Price::Qty::Date separated by |
$recentOrders = [];
if (isset($_COOKIE['pastPurchases']) && $_COOKIE['pastPurchases'] !== '') {
    $rawItems = explode('|', $_COOKIE['pastPurchases']);
    // Show newest purchases first, max 3
    $rawItems = array_reverse($rawItems);
    foreach (array_slice($rawItems, 0, 3) as $raw) {
        $parts = explode('::', $raw);
        if (count($parts) >= 2) {
            $recentOrders[] = [
                'name'       => $parts[0],
                'unit_price' => $parts[1],
                'quantity'   => $parts[2] ?? '1',
                'order_date' => $parts[3] ?? ''
            ];
        }
    }
}
?>

<main id="main-content">

<!-- HERO SECTION -->
<section class="hero" style="display:flex;justify-content:space-between;align-items:center;gap:30px;flex-wrap:wrap;background:#E9F2E3;padding:70px 60px;">
  <div class="hero-text" style="flex:1;min-width:280px;">
    <h1 style="font-size:44px;margin-bottom:18px;color:#2F6B3A;line-height:1.2;">Shop Sustainably<br>with Athar</h1>
    <p style="font-size:18px;margin-bottom:28px;color:#555;line-height:1.7;">Discover eco-friendly products that reduce waste and support a greener lifestyle.</p>
    <a href="products.php" class="btn">Shop Now →</a>
  </div>
  <img src="images/eco_products.jpg" alt="Eco-friendly products" style="width:420px;max-width:100%;border-radius:16px;object-fit:cover;box-shadow:0 8px 24px rgba(0,0,0,.12);">
</section>

<?php
// Only show the Recent Purchases banner if the user has past orders
if (!empty($recentOrders)) : ?>
<div style="padding:25px 40px 10px;">
  <div style="max-width:1200px;margin:0 auto;background:#DDECCB;border-radius:22px;padding:24px 34px;">
    <p style="font-size:16px;font-weight:700;color:#2F6B3A;margin-bottom:14px;">🕒 Welcome Back! Your Recent Purchases</p>
    <?php foreach ($recentOrders as $order) : ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(47,107,58,0.15);flex-wrap:wrap;gap:8px;">
      <span style="font-size:15px;color:#2F6B3A;">
        <?= htmlspecialchars($order['name']) ?> (x<?= htmlspecialchars($order['quantity']) ?>)
      </span>
      <span style="font-size:14px;color:#555;">
        <?= htmlspecialchars($order['unit_price']) ?> &nbsp;|&nbsp; <?= htmlspecialchars($order['order_date']) ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- CATEGORIES SECTION -->
<section style="padding:50px 40px;">
  <h2 style="text-align:center;font-size:32px;margin-bottom:30px;color:#2F6B3A;font-weight:700;">Our Categories</h2>
  <div style="display:flex;justify-content:center;gap:18px;flex-wrap:wrap;">
    <!-- Each category links to the products page with a filter in the URL -->
    <a href="products.php?category=Bamboo+Products" style="background:white;width:160px;padding:22px 12px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);font-size:15px;font-weight:700;color:#2F6B3A;display:block;transition:0.3s;" onmouseover="this.style.background='#E9F2E3'" onmouseout="this.style.background='white'"><div style="font-size:28px;margin-bottom:8px;">🎋</div>Bamboo Products</a>
    <a href="products.php?category=Organic+Skincare" style="background:white;width:160px;padding:22px 12px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);font-size:15px;font-weight:700;color:#2F6B3A;display:block;transition:0.3s;" onmouseover="this.style.background='#E9F2E3'" onmouseout="this.style.background='white'"><div style="font-size:28px;margin-bottom:8px;">🌿</div>Organic Skincare</a>
    <a href="products.php?category=Reusable+Bags"    style="background:white;width:160px;padding:22px 12px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);font-size:15px;font-weight:700;color:#2F6B3A;display:block;transition:0.3s;" onmouseover="this.style.background='#E9F2E3'" onmouseout="this.style.background='white'"><div style="font-size:28px;margin-bottom:8px;">♻️</div>Reusable Bags</a>
    <a href="products.php?category=Eco+Cleaning"    style="background:white;width:160px;padding:22px 12px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);font-size:15px;font-weight:700;color:#2F6B3A;display:block;transition:0.3s;" onmouseover="this.style.background='#E9F2E3'" onmouseout="this.style.background='white'"><div style="font-size:28px;margin-bottom:8px;">🧹</div>Eco Cleaning</a>
    <a href="products.php?category=Water+Bottles"   style="background:white;width:160px;padding:22px 12px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);font-size:15px;font-weight:700;color:#2F6B3A;display:block;transition:0.3s;" onmouseover="this.style.background='#E9F2E3'" onmouseout="this.style.background='white'"><div style="font-size:28px;margin-bottom:8px;">💧</div>Water Bottles</a>
  </div>
</section>

<!-- FEATURED PRODUCTS - pulled from the database -->
<section style="padding:10px 40px 30px;">
  <h2 style="text-align:center;font-size:32px;margin-bottom:30px;color:#2F6B3A;font-weight:700;">Featured Products</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px;">
    <?php
    // Loop through each featured product from the database
    while ($product = $featuredResult->fetch_assoc()) :
    ?>
    <div style="background:white;border-radius:14px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,.08);text-align:center;padding-bottom:20px;transition:0.3s;">
      <img src="images/<?= htmlspecialchars($product['picture']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;height:220px;object-fit:cover;">
      <h3 style="margin:14px 12px 6px;font-size:18px;color:#2F6B3A;"><?= htmlspecialchars($product['name']) ?></h3>
      <p style="color:#555;margin-bottom:14px;font-size:15px;font-weight:600;"><?= number_format($product['price'], 2) ?> SAR</p>
      <div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;padding:0 12px;">
        <!-- Pass the product ID in the URL so product_details.php knows which product to show -->
        <a href="product_details.php?id=<?= $product['id'] ?>" style="background:#8FBF8F;color:white;padding:9px 16px;border-radius:8px;font-size:14px;text-decoration:none;">View Details</a>
        <a href="cart.php?action=add&id=<?= $product['id'] ?>" style="background:#4CAF50;color:white;padding:9px 16px;border-radius:8px;font-size:14px;text-decoration:none;">Add to Cart</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<div style="text-align:center;padding:0 40px 50px;">
  <a href="products.php" class="btn">View All Products →</a>
</div>

<!-- WHY CHOOSE ATHAR SECTION -->
<section style="padding:50px 40px;">
  <h2 style="text-align:center;font-size:32px;margin-bottom:30px;color:#2F6B3A;font-weight:700;">Why Choose Athar?</h2>
  <div style="display:flex;justify-content:center;gap:22px;flex-wrap:wrap;">
    <div style="background:white;width:260px;padding:28px 22px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);">
      <h3 style="margin-bottom:10px;font-size:19px;color:#2F6B3A;">🌱 Eco-Friendly Materials</h3>
      <p style="color:#555;line-height:1.6;font-size:15px;">Our products are made from sustainable, environmentally friendly materials.</p>
    </div>
    <div style="background:white;width:260px;padding:28px 22px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);">
      <h3 style="margin-bottom:10px;font-size:19px;color:#2F6B3A;">♻️ Reduce Plastic Waste</h3>
      <p style="color:#555;line-height:1.6;font-size:15px;">We help you replace single-use plastic with reusable alternatives.</p>
    </div>
    <div style="background:white;width:260px;padding:28px 22px;text-align:center;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.08);">
      <h3 style="margin-bottom:10px;font-size:19px;color:#2F6B3A;">💚 Sustainable Living</h3>
      <p style="color:#555;line-height:1.6;font-size:15px;">Athar promotes greener everyday habits through simple, practical products.</p>
    </div>
  </div>
</section>

<!-- ABOUT SECTION -->
<section id="about" style="background:#E9F2E3;text-align:center;padding:50px 40px;">
  <h2 style="font-size:32px;margin-bottom:18px;color:#2F6B3A;">About Athar</h2>
  <p style="max-width:700px;margin:auto;font-size:18px;color:#444;line-height:1.7;">
    Athar is an eco-friendly online store dedicated to promoting sustainable living. Our mission is to provide environmentally conscious products that help reduce plastic waste and encourage responsible shopping habits.
  </p>
</section>

</main>

<?php require_once 'includes/footer.php'; ?>
