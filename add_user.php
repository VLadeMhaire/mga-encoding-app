<?php
require_once 'config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // Check if username exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        header('Location: manage_users.php?error=Username already exists');
        exit();
    }
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $user_type);
    
    if ($stmt->execute()) {
        header('Location: manage_users.php?success=User added successfully');
        exit();
    } else {
        header('Location: manage_users.php?error=Error adding user');
        exit();
    }
}

header('Location: manage_users.php');
exit();
?>