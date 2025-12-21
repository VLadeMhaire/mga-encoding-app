<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mga_encoding_app');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Admin credentials (username: admin, password: password123)
function verifyAdminPassword($password) {
    // For demo: password123
    // In production, use password_hash() and password_verify()
    return $password === 'password123';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}
?>