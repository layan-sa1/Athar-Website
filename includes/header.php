<?php
/*
 * File: header.php
 */

// This file is the shared header and navbar used on every customer-facing page
// We include it at the top of each page instead of copy-pasting the nav everywhere

// Only start the session if it hasn't been started already
// This prevents the "headers already sent" error
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the cart doesn't exist yet in the session, make it an empty array
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Count total items in cart (add up all quantities)
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['qty'];
}

// $pageTitle and $activePage should be set before including this file
// If they're not set, we give them default values
if (!isset($pageTitle))  $pageTitle  = "Athar";
if (!isset($activePage)) $activePage = "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Athar | <?= htmlspecialchars($pageTitle) ?></title>
  <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;}
    html{scroll-behavior:smooth;}
    body{background:#F6F4EF;color:#2F6B3A;padding-top:80px;line-height:1.6;min-height:100vh;display:flex;flex-direction:column;}
    a{text-decoration:none;transition:0.3s;}
    img{max-width:100%;display:block;}
    main{flex:1;}
    header{background:#2F6B3A;padding:15px 40px;position:fixed;width:100%;top:0;left:0;z-index:1000;box-shadow:0 2px 8px rgba(0,0,0,.15);}
    nav{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;}
    .logo{display:flex;align-items:center;flex-shrink:0;}
    .logo img{height:54px;width:auto;object-fit:contain;}
    .nav-links{display:flex;align-items:center;flex-wrap:wrap;gap:10px;}
    .nav-links a{color:white;font-size:16px;padding:8px 10px;position:relative;transition:0.3s;text-decoration:none;}
    .nav-links a:hover{color:#8FBF8F;}
    .nav-links a::after{content:"";position:absolute;left:10px;bottom:2px;width:0;height:2px;background:#8FBF8F;transition:0.3s;}
    .nav-links a:hover::after,.nav-links a.active::after{width:calc(100% - 20px);}
    .nav-links a.active{color:#8FBF8F;}
    .cart-link{background:#4CAF50;border-radius:20px;padding:8px 16px !important;font-weight:bold;}
    .cart-link:hover{background:#1d4a25 !important;color:white !important;}
    .cart-link::after{display:none !important;}
    .nav-toggle{display:none;background:transparent;border:2px solid white;color:white;font-size:20px;padding:6px 10px;border-radius:6px;cursor:pointer;}
    .btn{display:inline-block;background:#4CAF50;color:white;padding:12px 26px;border-radius:8px;font-size:15px;font-weight:600;transition:0.3s;border:none;cursor:pointer;text-decoration:none;}
    .btn:hover{background:#2F6B3A;color:white;}
    .btn-sm{padding:9px 18px;font-size:14px;}
    .flash{padding:14px 24px;border-radius:10px;margin:20px 40px;font-size:15px;font-weight:600;}
    .flash-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
    .flash-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
    footer{background:#2F6B3A;color:rgba(255,255,255,.85);margin-top:auto;}
    .footer-inner{max-width:1200px;margin:0 auto;padding:50px 60px 40px;display:grid;grid-template-columns:2fr 1fr 1fr 1.5fr;gap:40px;align-items:start;}
    .footer-brand{display:flex;flex-direction:column;gap:10px;}
    .footer-logo{display:flex;align-items:center;gap:8px;}
    .footer-leaf{font-size:1.5rem;}
    .footer-brand-name{font-size:1.5rem;font-weight:700;color:white;}
    .footer-brand>p{font-size:.88rem;color:rgba(255,255,255,.6);line-height:1.6;max-width:220px;}
    .footer-col{display:flex;flex-direction:column;}
    .footer-col h4{color:white;font-size:.75rem;font-weight:700;letter-spacing:.13em;text-transform:uppercase;margin-bottom:14px;}
    .footer-col a{color:rgba(255,255,255,.65);font-size:.92rem;padding:5px 0;text-decoration:none;transition:color .2s;display:block;}
    .footer-col a:hover{color:white;}
    .footer-col>p{color:rgba(255,255,255,.65);font-size:.92rem;padding:5px 0;}
    .footer-bottom{border-top:1px solid rgba(255,255,255,.12);text-align:center;padding:18px 40px;}
    .footer-bottom p{font-size:.82rem;color:rgba(255,255,255,.45);}
	.skip-link{
    position:absolute;
    left:-9999px;
    top:auto;
    background:#2F6B3A;
    color:white;
    padding:12px 18px;
    border-radius:8px;
    text-decoration:none;
    z-index:9999;
}

.skip-link:focus{
    left:20px;
    top:20px;
}
    @media(max-width:900px){.footer-inner{grid-template-columns:1fr 1fr;padding:40px 30px;gap:28px;}}
    @media(max-width:768px){
      header{padding:15px 20px;}
      .nav-toggle{display:block;}
      .nav-links{display:none;width:100%;flex-direction:column;align-items:flex-start;padding:10px 0 5px;gap:0;}
      .nav-links.nav-open{display:flex;}
      .nav-links a{width:100%;padding:11px 6px;border-bottom:1px solid rgba(255,255,255,.1);}
    }
    @media(max-width:540px){.footer-inner{grid-template-columns:1fr;padding:30px 22px;gap:22px;}}
  </style>
</head>
<body>

<!-- Skip to main content link for keyboard/screen reader users -->
<a href="#main-content" class="skip-link">
  Skip to Main Content
</a>

<header role="banner">
  <nav>
    <a href="home.php" class="logo"><img src="images/Athar.jpg" alt="Athar"></a>
    <div class="nav-links" id="navLinks" role="navigation" aria-label="Main navigation">
      <!-- Highlight the active page by checking $activePage -->
      <a href="home.php"     class="<?= $activePage === 'home'     ? 'active' : '' ?>">Home</a>
      <a href="products.php" class="<?= $activePage === 'products' ? 'active' : '' ?>">Shop</a>
      <a href="home.php#about">About</a>
      <a href="contact.php"  class="<?= $activePage === 'contact'  ? 'active' : '' ?>">Contact Us</a>
      <!-- Show live cart count from the session -->
      <a href="cart.php" class="cart-link">🛒 Cart (<?= $cartCount ?>)</a>
    </div>
    <button class="nav-toggle" onclick="document.getElementById('navLinks').classList.toggle('nav-open')" aria-label="Toggle navigation">&#9776;</button>
  </nav>
</header>
