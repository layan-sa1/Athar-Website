<?php
/*
 * File: manage_products.php
 * Author: [Wadha AlBaker]
 * Group: [3]
 */

require_once 'includes/db.php';

// If no admin is logged in, send them to the login page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$message     = '';
$editProduct = null;

// DELETE a product
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    $message = "success:Product deleted successfully.";
    header("Location: manage_products.php?msg=" . urlencode($message));
    exit;
}

// LOAD a product into the form for editing
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// SAVE (add or update) a product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = intval($_POST['product_id'] ?? 0);
    $name        = trim($_POST['name']);
    $category    = trim($_POST['category']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $short_desc  = trim($_POST['short_description']);
    $description = trim($_POST['description']);
    $features    = trim($_POST['features']);

    $picture = trim($_POST['existing_picture'] ?? '');
    if (!empty($_FILES['picture']['name'])) {
        $filename  = basename($_FILES['picture']['name']);
        $uploadDir = 'images/';
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $uploadDir . $filename)) {
            $picture = $filename;
        }
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, short_description=?, description=?, features=?, picture=? WHERE id=?");
        $stmt->bind_param("ssdissssi", $name, $category, $price, $stock, $short_desc, $description, $features, $picture, $id);
        $stmt->execute();
        $stmt->close();
        $message = "success:Product updated successfully.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, stock, short_description, description, features, picture) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssdissss", $name, $category, $price, $stock, $short_desc, $description, $features, $picture);
        $stmt->execute();
        $stmt->close();
        $message = "success:Product added successfully.";
    }
    header("Location: manage_products.php?msg=" . urlencode($message));
    exit;
}

if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id ASC");
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $products = $stmt->get_result();
    $stmt->close();
} else {
    $products = $conn->query("SELECT * FROM products ORDER BY id ASC");
}

// Use the same header.php as all other pages
$pageTitle  = "Manage Products";
$activePage = "";
require_once 'includes/header.php';
?>

<style>
  /* Hide the store nav links and replace with admin nav */
  .nav-links { display: none !important; }
  .nav-toggle { display: none !important; }

  /* Admin nav shown instead */
  .admin-nav { display: flex; align-items: center; gap: 8px; }
  .admin-nav a { color: white; background: rgba(255,255,255,0.15); padding: 7px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; transition: 0.3s; }
  .admin-nav a:hover { background: rgba(255,255,255,0.3); }
  .admin-info { color: white; font-size: 14px; margin-left: 8px; }
  .logout-btn { background: #e74c3c; color: white; padding: 7px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; margin-left: 8px; }
  .logout-btn:hover { background: #c0392b; color: white; }

  /* Page styles */
  .admin-banner{background:#E9F2E3;padding:30px 60px;text-align:center;border-bottom:1px solid #d4e8c2;}
  .admin-banner h1{font-size:28px;color:#2F6B3A;margin-bottom:6px;}
  .admin-banner p{color:#555;font-size:15px;}
  .search-row{display:flex;gap:10px;padding:20px 40px 10px;align-items:center;}
  .search-row input{padding:9px 14px;border:1.5px solid #ccc;border-radius:8px;font-size:14px;width:260px;}
  .search-row button{background:#4CAF50;color:white;border:none;padding:9px 16px;border-radius:8px;cursor:pointer;font-size:14px;}
  .table-wrap{padding:0 40px 20px;overflow-x:auto;}
  table{width:100%;border-collapse:collapse;background:white;border-radius:14px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,.07);}
  thead{background:#2F6B3A;color:white;}
  thead th{padding:13px 16px;font-size:14px;text-align:left;}
  tbody tr:hover{background:#f9f9f9;}
  tbody td{padding:11px 16px;font-size:14px;color:#333;border-bottom:1px solid #f0f0f0;}
  .form-section{padding:10px 40px 50px;}
  .form-card{background:white;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,.07);padding:36px;max-width:960px;margin:0 auto;}
  .form-card h2{font-size:20px;color:#2F6B3A;margin-bottom:20px;font-weight:700;}
  .form-row{display:flex;gap:18px;flex-wrap:wrap;margin-bottom:16px;}
  .form-col{flex:1;min-width:200px;display:flex;flex-direction:column;}
  .form-col label{font-weight:600;font-size:13px;color:#2F6B3A;margin-bottom:5px;}
  .form-col input,.form-col select,.form-col textarea{padding:9px 12px;border:1.5px solid #d0d8d0;border-radius:8px;font-size:14px;font-family:inherit;}
  .form-col input:focus,.form-col select:focus,.form-col textarea:focus{outline:none;border-color:#4CAF50;}
  .form-col textarea{resize:vertical;min-height:80px;}
</style>

<!-- Inject admin nav into the existing header via JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var nav = document.querySelector('nav');
    var adminNav = document.createElement('div');
    adminNav.className = 'admin-nav';
    adminNav.innerHTML = `

        <span class="admin-info">👋 Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    `;
    nav.appendChild(adminNav);
});
</script>

<main id="main-content">
  <div class="admin-banner">
    <div style="display:inline-block;background:#2F6B3A;color:white;font-size:12px;font-weight:700;padding:4px 12px;border-radius:20px;margin-bottom:12px;letter-spacing:.08em;">⚙️ ADMIN DASHBOARD</div>
    <h1>Product Catalog</h1>
    <p>Add new products, edit existing ones, and manage inventory.</p>
  </div>

  <?php if ($message) :
      $parts = explode(":", $message, 2);
      $type  = $parts[0];
      $text  = $parts[1] ?? $message;
  ?>
    <div class="flash flash-<?= $type ?>"><?= htmlspecialchars($text) ?></div>
  <?php endif; ?>

  <!-- PRODUCT TABLE -->
  <section style="padding-bottom:10px;">
    <h2 style="text-align:center;font-size:26px;margin:24px 0 14px;color:#2F6B3A;font-weight:700;">All Products</h2>

    <form class="search-row" method="GET" action="manage_products.php">
      <input type="text" name="search" placeholder="Search by product name..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit">🔍 Search</button>
      <?php if ($search) : ?>
        <a href="manage_products.php" style="color:#e74c3c;font-size:14px;">✕ Clear</a>
      <?php endif; ?>
    </form>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($products->num_rows === 0) : ?>
            <tr><td colspan="6" style="text-align:center;padding:30px;color:#888;">No products found.</td></tr>
          <?php else :
            while ($p = $products->fetch_assoc()) : ?>
            <tr>
              <td><img src="images/<?= htmlspecialchars($p['picture']) ?>" style="width:54px;height:54px;object-fit:cover;border-radius:8px;"></td>
              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><?= htmlspecialchars($p['category']) ?></td>
              <td><?= number_format($p['price'], 2) ?> SAR</td>
              <td><?= $p['stock'] ?></td>
              <td style="white-space:nowrap;">
                <a href="manage_products.php?edit=<?= $p['id'] ?>#product-form" class="btn btn-edit btn-sm">✏️ Edit</a>
                <a href="manage_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" style="margin-left:6px;"
                   onclick="return confirm('Delete this product? This cannot be undone.')">🗑️ Delete</a>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- ADD / EDIT FORM -->
  <div class="form-section">
    <div class="form-card" id="product-form">
      <h2><?= $editProduct ? '✏️ Edit Product' : '➕ Add New Product' ?></h2>

      <form method="POST" action="manage_products.php" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?? 0 ?>">
        <input type="hidden" name="existing_picture" value="<?= htmlspecialchars($editProduct['picture'] ?? '') ?>">

        <div class="form-row">
          <div class="form-col">
            <label>Product Name *</label>
            <input type="text" name="name" required placeholder="e.g. Bamboo Toothbrush" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
          </div>
          <div class="form-col">
            <label>Category *</label>
            <select name="category" required>
              <option value="">Select category</option>
              <?php
              $cats = ['Bamboo Products','Reusable Bags','Eco Cleaning','Water Bottles','Organic Skincare','Eco Accessories','Kitchen','Reusable Cups'];
              foreach ($cats as $cat) {
                  $sel = (isset($editProduct['category']) && $editProduct['category'] === $cat) ? 'selected' : '';
                  echo "<option value='$cat' $sel>$cat</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <label>Price (SAR) *</label>
            <input type="number" name="price" required step="0.01" min="0" placeholder="0.00" value="<?= $editProduct['price'] ?? '' ?>">
          </div>
          <div class="form-col">
            <label>Stock Quantity *</label>
            <input type="number" name="stock" required min="0" placeholder="0" value="<?= $editProduct['stock'] ?? '' ?>">
          </div>
          <div class="form-col">
            <label>Product Image</label>
            <input type="file" name="picture" accept="image/*">
            <?php if (!empty($editProduct['picture'])) : ?>
              <small style="color:#888;margin-top:4px;">Current: <?= htmlspecialchars($editProduct['picture']) ?></small>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <label>Short Description</label>
            <input type="text" name="short_description" placeholder="One sentence summary" value="<?= htmlspecialchars($editProduct['short_description'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <label>Full Description</label>
            <textarea name="description" placeholder="Detailed product description..."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <label>Features (separate with |)</label>
            <input type="text" name="features" placeholder="Feature 1|Feature 2|Feature 3" value="<?= htmlspecialchars($editProduct['features'] ?? '') ?>">
          </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
          <button class="btn" type="submit" style="padding:12px 30px;font-size:15px;">
            <?= $editProduct ? '💾 Save Changes' : '➕ Add Product' ?>
          </button>
          <?php if ($editProduct) : ?>
            <a href="manage_products.php" class="btn" style="background:#8FBF8F;padding:12px 20px;">✕ Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

</main>

<script>
document.querySelector('.search-row input').addEventListener('input', function() {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(function(row) {
        const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        row.style.display = name.includes(keyword) ? '' : 'none';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>