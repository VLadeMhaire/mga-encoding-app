<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Function to fetch data from a table
function fetchUserData($conn, $table, $user_id) {
    // Different order by clause for different tables
    if ($table == 'information') {
        $query = "SELECT * FROM $table WHERE user_id = ? ORDER BY submitted_at DESC";
    } else {
        $query = "SELECT * FROM $table WHERE user_id = ? ORDER BY date DESC";
    }
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Get all data
$info_result = fetchUserData($conn, 'information', $user_id);
$vat_sales_result = fetchUserData($conn, 'vatable_sales', $user_id);
$non_vat_sales_result = fetchUserData($conn, 'non_vat_sales', $user_id);
$vat_purchases_result = fetchUserData($conn, 'vatable_purchases', $user_id);
$non_vat_purchases_result = fetchUserData($conn, 'non_vat_purchases', $user_id);
$vat_expenses_result = fetchUserData($conn, 'vatable_expenses', $user_id);
$non_vat_expenses_result = fetchUserData($conn, 'non_vat_expenses', $user_id);
$capex_result = fetchUserData($conn, 'capex', $user_id);
$taxes_result = fetchUserData($conn, 'taxes_licenses', $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submissions - MGA&A Encoding App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #b71c1c 100%);
            min-height: 100vh;
            color: white;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: #90caf9;
            text-decoration: none;
            font-size: 1.1rem;
        }
        
        .back-btn:hover {
            text-decoration: underline;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 25px;
            border-radius: 25px;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0d47a1;
            transform: scale(1.05);
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-success:hover {
            background: #2e7d32;
            transform: scale(1.05);
        }
        
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e65100;
            transform: scale(1.05);
        }
        
        .data-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: none;
        }
        
        .data-section.active {
            display: block;
        }
        
        .data-section h2 {
            margin-bottom: 20px;
            color: #90caf9;
            border-bottom: 2px solid rgba(144, 202, 249, 0.3);
            padding-bottom: 10px;
        }
        
        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
        }
        
        .tab {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab:hover {
            background: rgba(33, 150, 243, 0.3);
        }
        
        .tab.active {
            background: #2196f3;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            color: white;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        th {
            background: rgba(33, 150, 243, 0.3);
            font-weight: bold;
        }
        
        tr:hover {
            background: rgba(255,255,255,0.05);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            font-size: 1.2rem;
            opacity: 0.8;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .info-label {
            font-weight: bold;
            width: 200px;
            color: #90caf9;
        }
        
        .info-value {
            flex: 1;
        }
        
        .amount {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #4caf50;
        }
        
        .record-count {
            background: rgba(33, 150, 243, 0.3);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .invoice-type {
            background: rgba(255, 193, 7, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .payment-type {
            background: rgba(76, 175, 80, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .tax-rate {
            background: rgba(156, 39, 176, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .action-buttons-cell {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 4px;
        }
        
        .remarks-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .remarks-cell:hover {
            overflow: visible;
            white-space: normal;
            background: rgba(0, 0, 0, 0.3);
            position: absolute;
            z-index: 100;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c62828;
            transform: scale(1.05);
        }
        
        .expense-type {
            background: rgba(255, 152, 0, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .nature-expense {
            background: rgba(156, 39, 176, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .nature-expense:hover {
            overflow: visible;
            white-space: normal;
            background: rgba(156, 39, 176, 0.4);
            position: absolute;
            z-index: 100;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .transaction-type {
            background: rgba(33, 150, 243, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .tax-type {
            background: rgba(244, 67, 54, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .agency-type {
            background: rgba(233, 30, 99, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .asset-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .asset-cell:hover {
            overflow: visible;
            white-space: normal;
            background: rgba(0, 150, 136, 0.3);
            position: absolute;
            z-index: 100;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <header>
            <h1>My Submissions</h1>
            <p>View, edit, and manage your encoded data</p>
        </header>
        
        <div class="action-buttons">
            <a href="export_my_data.php" class="btn btn-success">Export All My Data to Excel</a>
            <a href="index.php" class="btn btn-primary">Back to Encoding</a>
        </div>
        
        <div class="tabs">
            <div class="tab active" onclick="showSection('information')">Company Information</div>
            <div class="tab" onclick="showSection('vatable_sales')">Vatable Sales</div>
            <div class="tab" onclick="showSection('non_vat_sales')">Non-VAT Sales</div>
            <div class="tab" onclick="showSection('vatable_purchases')">Vatable Purchases</div>
            <div class="tab" onclick="showSection('non_vat_purchases')">Non-VAT Purchases</div>
            <div class="tab" onclick="showSection('vatable_expenses')">Vatable Expenses</div>
            <div class="tab" onclick="showSection('non_vat_expenses')">Non-VAT Expenses</div>
            <div class="tab" onclick="showSection('capex')">CAPEX</div>
            <div class="tab" onclick="showSection('taxes_licenses')">Taxes & Licenses</div>
        </div>
        
        <!-- Information Section -->
        <div id="information" class="data-section active">
            <h2>Company Information</h2>
            <?php if ($info_result->num_rows > 0): ?>
                <?php while($info = $info_result->fetch_assoc()): ?>
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">Company Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($info['company_name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">TIN Number:</div>
                        <div class="info-value"><?php echo $info['tin_number']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Address:</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($info['address'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Period:</div>
                        <div class="info-value"><?php echo $info['month'] . ' ' . $info['year']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Contact Number:</div>
                        <div class="info-value"><?php echo $info['contact_number']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo $info['email']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Submitted Date:</div>
                        <div class="info-value"><?php echo date('F j, Y g:i A', strtotime($info['submitted_at'])); ?></div>
                    </div>
                    <div style="text-align: right; margin-top: 15px;">
                        <a href="edit_data.php?type=information&id=<?php echo $info['id']; ?>" class="btn btn-warning btn-small">Edit Company Info</a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">No company information submitted yet.</div>
            <?php endif; ?>
        </div>
        
        <!-- Vatable Sales Section -->
        <!-- Vatable Sales Section -->
<div id="vatable_sales" class="data-section">
    <h2>Vatable Sales</h2>
    <div class="record-count"><?php echo $vat_sales_result->num_rows; ?> records</div>
    <?php if ($vat_sales_result->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>TIN</th>
                    <th>Vatable Gross Sales</th>
                    <th>Net Sales</th>
                    <th>Output Tax</th>
                    <th>Exempt Sales</th>
                    <th>Invoice Type</th>
                    <th>Payment</th>
                    <th>W/Tax VAT</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Reset pointer to beginning of result set
                $vat_sales_result->data_seek(0);
                while($sale = $vat_sales_result->fetch_assoc()): 
                    // Calculate Net Sales and Output Tax if not already in database
                    $vatable_gross_sales = isset($sale['vatable_gross_sales']) ? $sale['vatable_gross_sales'] : 0;
                    $net_sales = isset($sale['net_sales']) ? $sale['net_sales'] : ($vatable_gross_sales / 1.12);
                    $output_tax = isset($sale['output_tax']) ? $sale['output_tax'] : ($vatable_gross_sales - $net_sales);
                ?>
                <tr>
                    <td><?php echo $sale['date'] ? date('M d, Y', strtotime($sale['date'])) : 'N/A'; ?></td>
                    <td><?php echo $sale['invoice_number'] ?: 'N/A'; ?></td>
                    <td><?php echo $sale['particulars'] ? htmlspecialchars($sale['particulars']) : 'N/A'; ?></td>
                    <td><?php echo $sale['tin_number'] ?: 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($vatable_gross_sales, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_sales, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($output_tax, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($sale['exempt_sales'], 2); ?></td>
                    <td><span class="invoice-type"><?php echo $sale['invoice_type'] ?: 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo $sale['mode_of_payment'] ?: 'N/A'; ?></span></td>
                    <td><span class="tax-rate"><?php echo $sale['withholding_tax_vat'] ?: 'N/A'; ?></span></td>
                    <td class="remarks-cell"><?php echo $sale['remarks'] ? htmlspecialchars($sale['remarks']) : 'N/A'; ?></td>
                    <td>
                        <div class="action-buttons-cell">
                            <a href="edit_data.php?type=vatable_sales&id=<?php echo $sale['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                            <a href="delete_data.php?type=vatable_sales&id=<?php echo $sale['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="no-data">No vatable sales data submitted yet.</div>
    <?php endif; ?>
</div>
        
        <!-- Non-VAT Sales Section -->
        <div id="non_vat_sales" class="data-section">
            <h2>Non-VAT Sales</h2>
            <div class="record-count"><?php echo $non_vat_sales_result->num_rows; ?> records</div>
            <?php if ($non_vat_sales_result->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>TIN</th>
                            <th>Vatable Sales</th>
                            <th>Exempt Sales</th>
                            <th>Invoice Type</th>
                            <th>Payment</th>
                            <th>W/Tax VAT</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Reset pointer to beginning of result set
                        $non_vat_sales_result->data_seek(0);
                        while($row = $non_vat_sales_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo $row['invoice_number'] ?: 'N/A'; ?></td>
                            <td><?php echo $row['particulars'] ? htmlspecialchars($row['particulars']) : 'N/A'; ?></td>
                            <td><?php echo $row['tin_number'] ?: 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($row['vatable_gross_sales'], 2); ?></td>
                            <td class="amount">₱<?php echo number_format($row['exempt_sales'], 2); ?></td>
                            <td><span class="invoice-type"><?php echo $row['invoice_type'] ?: 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                            <td><span class="tax-rate"><?php echo $row['withholding_tax_vat'] ?: 'N/A'; ?></span></td>
                            <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                            <td>
                                <div class="action-buttons-cell">
                                    <a href="edit_data.php?type=non_vat_sales&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                                    <a href="delete_data.php?type=non_vat_sales&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="no-data">No non-VAT sales data submitted yet.</div>
            <?php endif; ?>
        </div>
        
        <!-- Vatable Purchases Section -->
<div id="vatable_purchases" class="data-section">
    <h2>Vatable Purchases</h2>
    <div class="record-count"><?php echo $vat_purchases_result->num_rows; ?> records</div>
    <?php if ($vat_purchases_result->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Supplier</th>
                    <th>TIN</th>
                    <th>Vatable Gross Purchases</th>
                    <th>Net Purchases</th>
                    <th>Input Tax</th>
                    <th>Invoice Type</th>
                    <th>Payment</th>
                    <th>W/Tax Rate</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $vat_purchases_result->data_seek(0);
                while($row = $vat_purchases_result->fetch_assoc()): 
                    // Calculate Net Purchases and Input Tax if not already in database
                    $vatable_gross_purchases = isset($row['vatable_gross_purchases']) ? $row['vatable_gross_purchases'] : 0;
                    $net_purchases = isset($row['net_purchases']) ? $row['net_purchases'] : ($vatable_gross_purchases / 1.12);
                    $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($vatable_gross_purchases - $net_purchases);
                ?>
                <tr>
                    <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                    <td><?php echo $row['invoice_number'] ?: 'N/A'; ?></td>
                    <td><?php echo $row['particulars'] ? htmlspecialchars($row['particulars']) : 'N/A'; ?></td>
                    <td><?php echo $row['tin_number'] ?: 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($vatable_gross_purchases, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_purchases, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($input_tax, 2); ?></td>
                    <td><span class="invoice-type"><?php echo $row['invoice_type'] ?: 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                    <td><span class="tax-rate"><?php echo $row['withholding_tax_rate'] ?: 'N/A'; ?></span></td>
                    <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                    <td>
                        <div class="action-buttons-cell">
                            <a href="edit_data.php?type=vatable_purchases&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                            <a href="delete_data.php?type=vatable_purchases&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="no-data">No vatable purchases data submitted yet.</div>
    <?php endif; ?>
</div>
        
        <!-- Non-VAT Purchases Section -->
        <div id="non_vat_purchases" class="data-section">
            <h2>Non-VAT Purchases</h2>
            <div class="record-count"><?php echo $non_vat_purchases_result->num_rows; ?> records</div>
            <?php if ($non_vat_purchases_result->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>TIN</th>
                            <th>Non-Vatable Purchases</th>
                            <th>Invoice Type</th>
                            <th>Payment</th>
                            <th>W/Tax Rate</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $non_vat_purchases_result->data_seek(0);
                        while($row = $non_vat_purchases_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo $row['invoice_number'] ?: 'N/A'; ?></td>
                            <td><?php echo $row['particulars'] ? htmlspecialchars($row['particulars']) : 'N/A'; ?></td>
                            <td><?php echo $row['tin_number'] ?: 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($row['vatable_gross_purchases'], 2); ?></td>
                            <td><span class="invoice-type"><?php echo $row['invoice_type'] ?: 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                            <td><span class="tax-rate"><?php echo $row['withholding_tax_rate'] ?: 'N/A'; ?></span></td>
                            <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                            <td>
                                <div class="action-buttons-cell">
                                    <a href="edit_data.php?type=non_vat_purchases&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                                    <a href="delete_data.php?type=non_vat_purchases&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="no-data">No non-VAT purchases data submitted yet.</div>
            <?php endif; ?>
        </div>
        
        <!-- Vatable Expenses Section -->
<div id="vatable_expenses" class="data-section">
    <h2>Vatable Expenses</h2>
    <div class="record-count"><?php echo $vat_expenses_result->num_rows; ?> records</div>
    <?php if ($vat_expenses_result->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Supplier</th>
                    <th>TIN</th>
                    <th>Gross Amount</th>
                    <th>Net Expense</th>
                    <th>Input Tax</th>
                    <th>Nature of Expense</th>
                    <th>Invoice Type</th>
                    <th>Transaction</th>
                    <th>Payment</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $vat_expenses_result->data_seek(0);
                while($row = $vat_expenses_result->fetch_assoc()): 
                    // Calculate Net Expense and Input Tax if not already in database
                    $gross_amount = isset($row['gross_amount']) ? $row['gross_amount'] : 0;
                    $net_amount = isset($row['net_amount']) ? $row['net_amount'] : ($gross_amount / 1.12);
                    $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($gross_amount - $net_amount);
                ?>
                <tr>
                    <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                    <td><?php echo $row['invoice_number'] ?: 'N/A'; ?></td>
                    <td><?php echo $row['particulars'] ? htmlspecialchars($row['particulars']) : 'N/A'; ?></td>
                    <td><?php echo $row['tin_number'] ?: 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($gross_amount, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_amount, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($input_tax, 2); ?></td>
                    <td><span class="nature-expense" title="<?php echo htmlspecialchars($row['nature_of_expense']); ?>"><?php echo $row['nature_of_expense'] ? htmlspecialchars($row['nature_of_expense']) : 'N/A'; ?></span></td>
                    <td><span class="invoice-type"><?php echo $row['invoice_type'] ?: 'N/A'; ?></span></td>
                    <td><span class="transaction-type"><?php echo $row['transaction_type'] ?: 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                    <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                    <td>
                        <div class="action-buttons-cell">
                            <a href="edit_data.php?type=vatable_expenses&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                            <a href="delete_data.php?type=vatable_expenses&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="no-data">No vatable expenses data submitted yet.</div>
    <?php endif; ?>
</div>
        
        <!-- Non-VAT Expenses Section -->
        <div id="non_vat_expenses" class="data-section">
            <h2>Non-VAT Expenses</h2>
            <div class="record-count"><?php echo $non_vat_expenses_result->num_rows; ?> records</div>
            <?php if ($non_vat_expenses_result->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Invoice Type</th>
                            <th>Transaction</th>
                            <th>Payment</th>
                            <th>Supplier</th>
                            <th>TIN</th>
                            <th>Amount</th>
                            <th>Nature of Expense</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $non_vat_expenses_result->data_seek(0);
                        while($row = $non_vat_expenses_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo $row['invoice_number'] ?: 'N/A'; ?></td>
                            <td><span class="invoice-type"><?php echo $row['invoice_type'] ?: 'N/A'; ?></span></td>
                            <td><span class="transaction-type"><?php echo $row['transaction_type'] ?: 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                            <td><?php echo $row['particulars'] ? htmlspecialchars($row['particulars']) : 'N/A'; ?></td>
                            <td><?php echo $row['tin_number'] ?: 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($row['gross_amount'], 2); ?></td>
                            <td><span class="nature-expense" title="<?php echo htmlspecialchars($row['nature_of_expense']); ?>"><?php echo $row['nature_of_expense'] ? htmlspecialchars($row['nature_of_expense']) : 'N/A'; ?></span></td>
                            <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                            <td>
                                <div class="action-buttons-cell">
                                    <a href="edit_data.php?type=non_vat_expenses&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                                    <a href="delete_data.php?type=non_vat_expenses&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="no-data">No non-VAT expenses data submitted yet.</div>
            <?php endif; ?>
        </div>
        
        <!-- CAPEX Section -->
<div id="capex" class="data-section">
    <h2>CAPEX</h2>
    <div class="record-count"><?php echo $capex_result->num_rows; ?> records</div>
    <?php if ($capex_result->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Supplier</th>
                    <th>Asset Description</th>
                    <th>TIN</th>
                    <th>Non-VAT Purchase</th>
                    <th>Vatable Gross Purchase</th>
                    <th>Net Vatable Purchase</th>
                    <th>Input Tax</th>
                    <th>W/Tax</th>
                    <th>Invoice Type</th>
                    <th>Payment</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $capex_result->data_seek(0);
                while($row = $capex_result->fetch_assoc()): 
                    // Calculate Net Vatable Purchase and Input Tax if not already in database
                    $gross_purchase_vatable = isset($row['gross_purchase_vatable']) ? $row['gross_purchase_vatable'] : 0;
                    $net_vatable_purchase = isset($row['net_vatable_purchase']) ? $row['net_vatable_purchase'] : ($gross_purchase_vatable / 1.12);
                    $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($gross_purchase_vatable - $net_vatable_purchase);
                ?>
                <tr>
                    <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                    <td><?php echo $row['invoice_number'] ?: 'N/A'; ?></td>
                    <td><?php echo $row['particulars'] ? htmlspecialchars($row['particulars']) : 'N/A'; ?></td>
                    <td class="asset-cell"><?php echo $row['asset_description'] ? htmlspecialchars($row['asset_description']) : 'N/A'; ?></td>
                    <td><?php echo $row['tin_number'] ?: 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($row['gross_purchase_non_vat'], 2); ?></td>
                    <td class="amount">₱<?php echo number_format($gross_purchase_vatable, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_vatable_purchase, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($input_tax, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($row['withholding_tax'], 2); ?></td>
                    <td><span class="invoice-type"><?php echo $row['invoice_type'] ?: 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                    <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                    <td>
                        <div class="action-buttons-cell">
                            <a href="edit_data.php?type=capex&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                            <a href="delete_data.php?type=capex&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="no-data">No CAPEX data submitted yet.</div>
    <?php endif; ?>
</div>
        
        <!-- Taxes & Licenses Section -->
        <div id="taxes_licenses" class="data-section">
            <h2>Taxes & Licenses</h2>
            <div class="record-count"><?php echo $taxes_result->num_rows; ?> records</div>
            <?php if ($taxes_result->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference #</th>
                            <th>Tax Type</th>
                            <th>Payment</th>
                            <th>Government Agency</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $taxes_result->data_seek(0);
                        while($row = $taxes_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date'] ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo $row['reference_number'] ?: 'N/A'; ?></td>
                            <td><span class="tax-type"><?php echo $row['tax_type'] ?: 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo $row['mode_of_payment'] ?: 'N/A'; ?></span></td>
                            <td><span class="agency-type"><?php echo $row['government_agency'] ? htmlspecialchars($row['government_agency']) : 'N/A'; ?></span></td>
                            <td class="amount">₱<?php echo number_format($row['amount'], 2); ?></td>
                            <td class="remarks-cell"><?php echo $row['remarks'] ? htmlspecialchars($row['remarks']) : 'N/A'; ?></td>
                            <td>
                                <div class="action-buttons-cell">
                                    <a href="edit_data.php?type=taxes_licenses&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">Edit</a>
                                    <a href="delete_data.php?type=taxes_licenses&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="no-data">No taxes & licenses data submitted yet.</div>
            <?php endif; ?>
        </div>
        
    </div>
    
    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.data-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Set active tab
            event.target.classList.add('active');
        }
        
        // Make remarks cells expand on hover
        document.addEventListener('DOMContentLoaded', function() {
            const remarksCells = document.querySelectorAll('.remarks-cell');
            remarksCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    this.style.position = 'absolute';
                    this.style.zIndex = '100';
                    this.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.5)';
                });
                
                cell.addEventListener('mouseleave', function() {
                    this.style.position = '';
                    this.style.zIndex = '';
                    this.style.backgroundColor = '';
                    this.style.boxShadow = '';
                });
            });
            
            // Make nature of expense cells expand on hover
            const natureCells = document.querySelectorAll('.nature-expense');
            natureCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    this.style.position = 'absolute';
                    this.style.zIndex = '100';
                    this.style.backgroundColor = 'rgba(156, 39, 176, 0.4)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.5)';
                });
                
                cell.addEventListener('mouseleave', function() {
                    this.style.position = '';
                    this.style.zIndex = '';
                    this.style.backgroundColor = '';
                    this.style.boxShadow = '';
                });
            });
            
            // Make asset description cells expand on hover
            const assetCells = document.querySelectorAll('.asset-cell');
            assetCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    this.style.position = 'absolute';
                    this.style.zIndex = '100';
                    this.style.backgroundColor = 'rgba(0, 150, 136, 0.3)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.5)';
                });
                
                cell.addEventListener('mouseleave', function() {
                    this.style.position = '';
                    this.style.zIndex = '';
                    this.style.backgroundColor = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>