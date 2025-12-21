<?php
require_once 'config.php';
requireLogin();

// Get parameters
$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Define valid table types
$valid_types = [
    'information', 'vatable_sales', 'non_vat_sales', 
    'vatable_purchases', 'non_vat_purchases',
    'vatable_expenses', 'non_vat_expenses', 'capex', 'taxes_licenses'
];

if (!in_array($type, $valid_types) || $id == 0) {
    header('Location: my_submissions.php');
    exit();
}

// Check if the record belongs to the current user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM $type WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: my_submissions.php');
    exit();
}

// Delete the record
$delete_query = "DELETE FROM $type WHERE id = ? AND user_id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("ii", $id, $user_id);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = "Record deleted successfully!";
} else {
    $_SESSION['error_message'] = "Error deleting record.";
}

header('Location: my_submissions.php');
exit();
?>