<?php
/*
 * File: products.php
 * Author: [Layan AlTamimi]
 * Group: [3]
 */

// Products page - shows all products from the database
// Supports filtering by category and searching by name

require_once 'includes/db.php';

$pageTitle  = "Shop";
$activePage = "products";
require_once 'includes/header.php';

// Check if a category filter was passed in the URL (e.g. products.php?category=Bamboo+Products)
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Check if a search keyword was submitted
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query depending on what filters are active
// We start with the base query and add WHERE conditions if needed
if ($categoryFilter && $searchKeyword) {
    // Both category and search filters are active
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND name LIKE ? ORDER BY id ASC");
    $like = "%" . $searchKeyword . "%";
    $stmt->bind_param("ss", $categoryFilter, $like);
} elseif ($categoryFilter) {
    // Only category filter
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY id ASC");
    $stmt->bind_param("s", $categoryFilter);
} elseif ($searchKeyword) {
    // Only search filter
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id ASC");
    $like = "%" . $searchKeyword . "%";
    $stmt->bind_param("s", $like);
} else {
    // No filters - get all products
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY id ASC");
}

$stmt->execute();
$result = $stmt->get_result();

// Get list of all unique categories for the filter buttons
$catResult = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
?>

<style>
  .page-title{text-align:center;font-size:36px;margin:40px 0 8px;color:#2F6B3A;font-weight:700;}
  .page-subtitle{text-align:center;color:#666;margin-bottom:20px;font-size:17px;padding:0 20px;}
  .filter-bar{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;padding:0 40px 20px;}
  .filter-btn{background:white;color:#2F6B3A;border:2px solid #2F6B3A;padding:8px 18px;border-radius:20px;font-size:14px;cursor:pointer;transition:0.3s;text-decoration:none;font-weight:600;}
  .filter-btn:hover,.filter-btn.active{background:#2F6B3A;color:white;}
  .search-bar{display:flex;justify-content:center;gap:8px;padding:0 40px 28px;}
  .search-bar input{padding:10px 16px;border:1.5px solid #ccc;border-radius:8px;font-size:15px;width:300px;}
  .search-bar button{background:#4CAF50;color:white;border:none;padding:10px 20px;border-radius:8px;font-size:15px;cursor:pointer;}
  .search-bar button:hover{background:#2F6B3A;}
  .prod-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:26px;padding:0 40px 60px;}
  .product-card{background:white;border-radius:14px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,.08);text-align:center;padding-bottom:22px;transition:0.3s;}
  .product-card:hover{transform:translateY(-8px);box-shadow:0 12px 24px rgba(0,0,0,.14);}
  .product-card img{width:100%;height:220px;object-fit:cover;}
  .product-card h3{margin:14px 12px 6px;font-size:18px;color:#2F6B3A;font-weight:700;}
  .product-card .price{color:#555;margin-bottom:8px;font-size:15px;font-weight:600;}
  .card-btns{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;padding:0 12px;}
  .btn-detail{background:#8FBF8F;color:white;padding:9px 16px;border-radius:8px;font-size:14px;text-decoration:none;display:inline-block;}
  .btn-detail:hover{background:#6fa86f;}
  .btn-cart{background:#4CAF50;color:white;padding:9px 16px;border-radius:8px;font-size:14px;text-decoration:none;display:inline-block;}
  .btn-cart:hover{background:#2F6B3A;}
  .no-results{text-align:center;padding:60px 40px;color:#666;font-size:18px;}
  @media(max-width:768px){.prod-grid{padding:0 20px 40px;grid-template-columns:1fr 1fr;}.search-bar input{width:100%;}}
  @media(max-width:540px){.prod-grid{grid-template-columns:1fr;}}
</style>

<main id="main-content">
  <h1 class="page-title">Our Eco-Friendly Products</h1>
  <p class="page-subtitle">Browse our sustainable collection and choose products that care for the planet.</p>

  <!-- SEARCH BAR - submits to the same page using GET -->
  <form class="search-bar" method="GET" action="products.php">
    <!-- Keep the category filter if one is already selected -->
    <?php if ($categoryFilter): ?>
      <input type="hidden" name="category" value="<?= htmlspecialchars($categoryFilter) ?>">
    <?php endif; ?>
    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($searchKeyword) ?>">
    <button type="submit">🔍 Search</button>
    <?php if ($searchKeyword || $categoryFilter): ?>
      <!-- Show a clear/reset link if any filter is active -->
      <a href="products.php" style="background:#e74c3c;color:white;border:none;padding:10px 16px;border-radius:8px;font-size:14px;text-decoration:none;">✕ Clear</a>
    <?php endif; ?>
  </form>

  <!-- CATEGORY FILTER BUTTONS - dynamically generated from the database -->
  <div class="filter-bar">
    <a href="products.php" class="filter-btn <?= !$categoryFilter ? 'active' : '' ?>">All</a>
    <?php while ($cat = $catResult->fetch_assoc()) : ?>
      <a href="products.php?category=<?= urlencode($cat['category']) ?>"
         class="filter-btn <?= $categoryFilter === $cat['category'] ? 'active' : '' ?>">
        <?= htmlspecialchars($cat['category']) ?>
      </a>
    <?php endwhile; ?>
  </div>

  <!-- PRODUCTS GRID -->
  <div class="prod-grid">
    <?php
    // Check if we got any products back from the query
    if ($result->num_rows === 0) : ?>
      <div class="no-results" style="grid-column:1/-1;">
        <p>No products found. <a href="products.php" style="color:#4CAF50;">View all products</a></p>
      </div>
    <?php else :
      // Loop through each product and display it as a card
      while ($product = $result->fetch_assoc()) : ?>
      <div class="product-card">
        <img src="images/<?= htmlspecialchars($product['picture']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <h3><?= htmlspecialchars($product['name']) ?></h3>
        <p class="price"><?= number_format($product['price'], 2) ?> SAR</p>
        <!-- Show out of stock badge if stock is 0 -->
        <?php if ($product['stock'] <= 0) : ?>
          <p style="color:#e74c3c;font-size:13px;margin-bottom:10px;font-weight:600;">⚠ Out of Stock</p>
        <?php endif; ?>
        <div class="card-btns">
          <a href="product_details.php?id=<?= $product['id'] ?>" class="btn-detail">View Details</a>
          <?php if ($product['stock'] > 0) : ?>
            <a href="cart.php?action=add&id=<?= $product['id'] ?>" class="btn-cart">Add to Cart</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile;
    endif; ?>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
