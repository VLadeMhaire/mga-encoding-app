<?php
require_once 'config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $user_type = $_POST['user_type'];
    
    // Prevent editing main admin (id = 1)
    if ($user_id == 1) {
        header('Location: view_user.php?id=' . $user_id . '&error=Cannot edit main admin');
        exit();
    }
    
    // Prepare update query
    if (!empty($new_password)) {
        // Update with new password
        $stmt = $conn->prepare("UPDATE users SET email = ?, password = ?, user_type = ? WHERE id = ?");
        $stmt->bind_param("sssi", $email, $new_password, $user_type, $user_id);
    } else {
        // Update without changing password
        $stmt = $conn->prepare("UPDATE users SET email = ?, user_type = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $user_type, $user_id);
    }
    
    if ($stmt->execute()) {
        header('Location: view_user.php?id=' . $user_id . '&success=User updated successfully');
        exit();
    } else {
        header('Location: view_user.php?id=' . $user_id . '&error=Error updating user');
        exit();
    }
}

header('Location: manage_users.php');
exit();
?>