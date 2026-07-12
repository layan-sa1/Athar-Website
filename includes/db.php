<?php
/*
 * File: db.php
 * Author: [Reema AlMulla]
 * Group: [3]
 */

// This file connects to the MySQL database
// We use it in every page that needs to talk to the database

// Start session here so it's always started before any output
// Using session_status() check prevents "already started" errors
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database settings - change port if needed (3307 if you changed it in XAMPP)
$host   = "localhost";
$user   = "root";
$pass   = "";        // XAMPP default has no password
$dbname = "athar_db";

// Try to connect using mysqli
$conn = new mysqli($host, $user, $pass, $dbname);

// If connection failed, stop and show an error
if ($conn->connect_error) {
    die("<p style='color:red; text-align:center; padding:40px; font-family:Arial;'>
        Database connection failed: " . $conn->connect_error . "<br>
        Make sure MySQL is running and you imported athar_db.sql
    </p>");
}

// Set character encoding so Arabic and special characters display correctly
$conn->set_charset("utf8mb4");
?>
