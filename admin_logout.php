<?php
/*
 * File: admin_logout.php
 * Author: [Wadha AlBaker]
 * Group: [3]
 */

// Logout page - clears the admin session and redirects to login
// Use session_status check to avoid double-start errors
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Remove just the admin-related session data
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);

// Send the admin back to the login page
header("Location: admin_login.php");
exit;
?>
