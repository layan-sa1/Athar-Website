<?php
/*
 * File: admin_login.php
 * Author: [Wadha AlBaker]
 * Group: [3]
 * 
 */

// Admin login page - only admins can log in here
// After logging in they get sent to manage_products.php

// db.php starts the session and connects to the database
require_once 'includes/db.php';

// If the admin is already logged in, send them straight to the dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: manage_products.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Look up the admin by email
    $stmt = $conn->prepare("SELECT admin_id, full_name, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if admin exists and password matches
    // Note: in a real project you should use password_hash() and password_verify()
    // We're using plain text here to keep it simple for the demo
    if ($admin && $admin['password'] === $password) {
        // Save admin info to session
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        header("Location: manage_products.php");
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Athar | Admin Login</title>
  <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;}
    body{background:#F6F4EF;min-height:100vh;display:flex;flex-direction:column;}
    /* Simple header without nav links since this is the admin login */
    header{background:#2F6B3A;padding:15px 40px;box-shadow:0 2px 8px rgba(0,0,0,.15);}
    .logo img{height:54px;object-fit:contain;}
    main{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 20px;}
    .login-card{background:white;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.1);padding:44px 40px;width:100%;max-width:440px;}
    .badge{display:inline-block;background:#E9F2E3;color:#2F6B3A;font-size:13px;font-weight:700;padding:5px 14px;border-radius:20px;margin-bottom:20px;}
    .login-card h2{font-size:28px;color:#2F6B3A;margin-bottom:8px;}
    .subtitle{color:#666;font-size:15px;margin-bottom:28px;}
    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-weight:600;font-size:14px;color:#2F6B3A;margin-bottom:6px;}
    .form-group input{width:100%;padding:11px 14px;border:1.5px solid #d0d8d0;border-radius:8px;font-size:15px;}
    .form-group input:focus{outline:none;border-color:#4CAF50;}
    .btn{width:100%;background:#4CAF50;color:white;padding:13px;border-radius:8px;font-size:15px;font-weight:600;border:none;cursor:pointer;}
    .btn:hover{background:#2F6B3A;}
    .error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:14px;}
    .back-link{text-align:center;margin-top:14px;}
    .back-link a{color:#2F6B3A;font-size:14px;opacity:.65;}
    footer{background:#2F6B3A;color:rgba(255,255,255,.45);text-align:center;padding:16px;}
    footer p{font-size:.82rem;}
  </style>
</head>
<body>
<header>
  <a href="home.php" class="logo"><img src="images/Athar.jpg" alt="Athar"></a>
</header>
<main>
  <div class="login-card">
    <span class="badge">⚙️ Staff Area</span>
    <h2>Admin Login</h2>
    <p class="subtitle">Enter your credentials to access the dashboard.</p>

    <?php if ($error) : ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_login.php">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="admin@athar.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button class="btn" type="submit">Login →</button>
    </form>
    <div class="back-link"><a href="home.php">← Back to store</a></div>
  </div>
</main>
<footer><p>&copy; 2026 Athar Eco Living. All rights reserved.</p></footer>
<script>
// JS validation for admin login form (Task 13)
document.querySelector('form').addEventListener('submit', function(e) {
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;

    // Check email format
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailPattern.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return;
    }
    // Password must not be empty
    if (!password) {
        e.preventDefault();
        alert('Please enter your password.');
    }
});
</script>
</body>
</html>
