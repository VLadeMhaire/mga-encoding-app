<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$category = $_GET['category'];

// Validate category
$valid_categories = [
    'vatable_sales', 'non_vat_sales', 'vatable_purchases', 'non_vat_purchases',
    'vatable_expenses', 'non_vat_expenses', 'capex', 'taxes_licenses'
];

if (!in_array($category, $valid_categories)) {
    echo '<div class="no-data">Invalid category requested</div>';
    exit();
}

switch($category) {
    case 'vatable_sales':
        $table = 'vatable_sales';
        $title = "Vatable Sales";
        break;
    case 'non_vat_sales':
        $table = 'non_vat_sales';
        $title = "Non-VAT Sales";
        break;
    case 'vatable_purchases':
        $table = 'vatable_purchases';
        $title = "Vatable Purchases";
        break;
    case 'non_vat_purchases':
        $table = 'non_vat_purchases';
        $title = "Non-VAT Purchases";
        break;
    case 'vatable_expenses':
        $table = 'vatable_expenses';
        $title = "Vatable Expenses";
        break;
    case 'non_vat_expenses':
        $table = 'non_vat_expenses';
        $title = "Non-VAT Expenses";
        break;
    case 'capex':
        $table = 'capex';
        $title = "CAPEX";
        break;
    case 'taxes_licenses':
        $table = 'taxes_licenses';
        $title = "Taxes & Licenses";
        break;
}

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE '$table'");
if ($table_check->num_rows == 0) {
    echo '<div class="no-data">Data table not found. Please contact administrator.</div>';
    exit();
}

// Get data
$query = "SELECT * FROM $table WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0): ?>
<h2><?php echo $title; ?></h2>
<div style="overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Submitted Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                <td><?php echo date('M d, Y', strtotime($row['submitted_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div style="margin-top: 10px; font-style: italic; opacity: 0.8;">
        Total Records: <?php echo $result->num_rows; ?>
    </div>
</div>
<?php else: ?>
<div class="no-data">No <?php echo strtolower($title); ?> data found.</div>
<?php endif; ?>