<?php
require_once 'config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$info_id = intval($_GET['id']);

// Get company information with proper period info
$info_query = $conn->prepare("
    SELECT i.*, u.username 
    FROM information i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.id = ?
");
$info_query->bind_param("i", $info_id);
$info_query->execute();
$info = $info_query->get_result()->fetch_assoc();

if (!$info) {
    header('Location: admin_dashboard.php');
    exit();
}

$user_id = $info['user_id'];

// Initialize all total variables to avoid undefined warnings
$vat_sales_total = 0;
$non_vat_sales_total = 0;
$net_sales_total = 0;        // Added for Net Sales calculation
$output_tax_total = 0;       // Added for Output Tax calculation
$vat_purchases_total = 0;
$non_vat_purchases_total = 0;
$net_purchases_total = 0;    // Added for Net Purchases calculation
$input_tax_purchases_total = 0;  // Added for Input Tax (Purchases) calculation
$vat_expenses_total = 0;
$non_vat_expenses_total = 0;
$net_expenses_total = 0;     // Added for Net Expenses calculation
$input_tax_expenses_total = 0;   // Added for Input Tax (Expenses) calculation
$capex_total = 0;
$capex_non_vat_total = 0;    // Added for CAPEX Non-VAT calculation
$capex_vatable_total = 0;    // Added for CAPEX Vatable calculation
$net_capex_vatable_total = 0;    // Added for Net CAPEX Vatable calculation
$input_tax_capex_total = 0;      // Added for Input Tax (CAPEX) calculation
$taxes_total = 0;
$input_tax_total = 0;

// Get all related data for this user - ALL DATA, not filtered by period
// This shows all submissions the user has ever made
$vat_sales = $conn->query("SELECT * FROM vatable_sales WHERE user_id = $user_id ORDER BY date DESC");
$non_vat_sales = $conn->query("SELECT * FROM non_vat_sales WHERE user_id = $user_id ORDER BY date DESC");
$vat_purchases = $conn->query("SELECT * FROM vatable_purchases WHERE user_id = $user_id ORDER BY date DESC");
$non_vat_purchases = $conn->query("SELECT * FROM non_vat_purchases WHERE user_id = $user_id ORDER BY date DESC");
$vat_expenses = $conn->query("SELECT * FROM vatable_expenses WHERE user_id = $user_id ORDER BY date DESC");
$non_vat_expenses = $conn->query("SELECT * FROM non_vat_expenses WHERE user_id = $user_id ORDER BY date DESC");
$capex = $conn->query("SELECT * FROM capex WHERE user_id = $user_id ORDER BY date DESC");
$taxes = $conn->query("SELECT * FROM taxes_licenses WHERE user_id = $user_id ORDER BY date DESC");

// Calculate totals from each section
// Vatable Sales Total
if ($vat_sales->num_rows > 0) {
    $vat_sales->data_seek(0);
    while($sale = $vat_sales->fetch_assoc()) {
        $vatable_gross_sales = isset($sale['vatable_gross_sales']) ? $sale['vatable_gross_sales'] : 0;
        $vat_sales_total += $vatable_gross_sales;
        
        // Calculate Net Sales and Output Tax
        $net_sales = isset($sale['net_sales']) ? $sale['net_sales'] : ($vatable_gross_sales / 1.12);
        $output_tax = isset($sale['output_tax']) ? $sale['output_tax'] : ($vatable_gross_sales - $net_sales);
        
        $net_sales_total += $net_sales;
        $output_tax_total += $output_tax;
    }
    $vat_sales->data_seek(0); // Reset pointer for display
}

// Non-VAT Sales Total
if ($non_vat_sales->num_rows > 0) {
    $non_vat_sales->data_seek(0);
    while($row = $non_vat_sales->fetch_assoc()) {
        $amount = isset($row['amount']) ? $row['amount'] : (isset($row['vatable_gross_sales']) ? $row['vatable_gross_sales'] : 0);
        $non_vat_sales_total += $amount;
    }
    $non_vat_sales->data_seek(0);
}

// Vatable Purchases Total
if ($vat_purchases->num_rows > 0) {
    $vat_purchases->data_seek(0);
    while($row = $vat_purchases->fetch_assoc()) {
        $vatable_gross_purchases = isset($row['vatable_gross_purchases']) ? $row['vatable_gross_purchases'] : 0;
        $vat_purchases_total += $vatable_gross_purchases;
        
        // Calculate Net Purchases and Input Tax
        $net_purchases = isset($row['net_purchases']) ? $row['net_purchases'] : ($vatable_gross_purchases / 1.12);
        $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($vatable_gross_purchases - $net_purchases);
        
        $net_purchases_total += $net_purchases;
        $input_tax_total += $input_tax;
    }
    $vat_purchases->data_seek(0);
}

// Non-VAT Purchases Total
if ($non_vat_purchases->num_rows > 0) {
    $non_vat_purchases->data_seek(0);
    while($row = $non_vat_purchases->fetch_assoc()) {
        $amount = isset($row['amount']) ? $row['amount'] : (isset($row['vatable_gross_purchases']) ? $row['vatable_gross_purchases'] : 0);
        $non_vat_purchases_total += $amount;
    }
    $non_vat_purchases->data_seek(0);
}

// Vatable Expenses Total
if ($vat_expenses->num_rows > 0) {
    $vat_expenses->data_seek(0);
    while($row = $vat_expenses->fetch_assoc()) {
        $gross_amount = isset($row['gross_amount']) ? $row['gross_amount'] : 0;
        $vat_expenses_total += $gross_amount;
        
        // Calculate Net Expenses and Input Tax
        $net_amount = isset($row['net_amount']) ? $row['net_amount'] : ($gross_amount / 1.12);
        $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($gross_amount - $net_amount);
        
        $net_expenses_total += $net_amount;
        $input_tax_expenses_total += $input_tax;
    }
    $vat_expenses->data_seek(0);
}

// Non-VAT Expenses Total
if ($non_vat_expenses->num_rows > 0) {
    $non_vat_expenses->data_seek(0);
    while($row = $non_vat_expenses->fetch_assoc()) {
        $amount = isset($row['amount']) ? $row['amount'] : (isset($row['gross_amount']) ? $row['gross_amount'] : 0);
        $non_vat_expenses_total += $amount;
    }
    $non_vat_expenses->data_seek(0);
}

// CAPEX Total
if ($capex->num_rows > 0) {
    $capex->data_seek(0);
    while($row = $capex->fetch_assoc()) {
        $gross_purchase_non_vat = isset($row['gross_purchase_non_vat']) ? $row['gross_purchase_non_vat'] : 0;
        $gross_purchase_vatable = isset($row['gross_purchase_vatable']) ? $row['gross_purchase_vatable'] : 0;
        $withholding_tax = isset($row['withholding_tax']) ? $row['withholding_tax'] : 0;
        
        $capex_non_vat_total += $gross_purchase_non_vat;
        $capex_vatable_total += $gross_purchase_vatable;
        $capex_total += $gross_purchase_non_vat + $gross_purchase_vatable + $withholding_tax;
        
        // Calculate Net CAPEX Vatable and Input Tax
        $net_capex_vatable = isset($row['net_vatable_purchase']) ? $row['net_vatable_purchase'] : ($gross_purchase_vatable / 1.12);
        $input_tax_capex = isset($row['input_tax']) ? $row['input_tax'] : ($gross_purchase_vatable - $net_capex_vatable);
        
        $net_capex_vatable_total += $net_capex_vatable;
        $input_tax_capex_total += $input_tax_capex;
    }
    $capex->data_seek(0);
}

// Taxes & Licenses Total
if ($taxes->num_rows > 0) {
    $taxes->data_seek(0);
    while($row = $taxes->fetch_assoc()) {
        $amount = isset($row['amount']) ? $row['amount'] : 0;
        $taxes_total += $amount;
    }
    $taxes->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submission - MGA&A Encoding App</title>
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
        
        .info-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(5px);
        }
        
        .info-card h2 {
            color: #90caf9;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(144, 202, 249, 0.3);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #bbdefb;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            padding: 8px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }
        
        .data-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: none;
        }
        
        .data-section.active {
            display: block;
        }
        
        .data-section h3 {
            color: #90caf9;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .record-count {
            background: rgba(33, 150, 243, 0.3);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
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
            padding: 30px;
            font-style: italic;
            opacity: 0.7;
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
        
        .table-container {
            overflow-x: auto;
        }
        
        .amount {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #4caf50;
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
        
        .transaction-type {
            background: rgba(33, 150, 243, 0.2);
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
            cursor: help;
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
            cursor: help;
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
        
        .period-filter {
            background: rgba(255, 193, 7, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .period-filter label {
            font-weight: bold;
            color: #ffc107;
        }
        
        .filter-badge {
            background: rgba(33, 150, 243, 0.3);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        
        .data-source {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
            font-style: italic;
        }
        
        .total-row {
            background: rgba(0, 150, 136, 0.2);
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid rgba(255,255,255,0.3);
        }
        
        .subtotal {
            color: #ffc107;
            font-weight: bold;
        }
        
        .export-section {
            text-align: right;
            margin-bottom: 10px;
        }
        
        .calculation-breakdown {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        
        .calculation-breakdown h3 {
            color: #90caf9;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn">← Back to Admin Dashboard</a>
        
        <header>
            <h1>Submission Details</h1>
            <p>View all submitted data for <?php echo htmlspecialchars($info['username']); ?></p>
        </header>
        
        <div class="action-buttons">
            <a href="export_user.php?user_id=<?php echo $user_id; ?>" class="btn btn-success">Export All User Data</a>
            <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
        
        <div class="info-card">
            <h2>Company Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Submitted By</div>
                    <div class="info-value"><?php echo htmlspecialchars($info['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Company Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($info['company_name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">TIN Number</div>
                    <div class="info-value"><?php echo $info['tin_number']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Period</div>
                    <div class="info-value"><?php echo $info['month'] . ' ' . $info['year']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($info['address'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Authorized Employee</div>
                    <div class="info-value"><?php echo $info['authorized_employee'] ? htmlspecialchars($info['authorized_employee']) : 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?php echo $info['contact_number'] ?: 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo $info['email'] ?: 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Submitted Date</div>
                    <div class="info-value"><?php echo date('F j, Y g:i A', strtotime($info['submitted_at'])); ?></div>
                </div>
            </div>
        </div>
        
        <div class="period-filter">
            <label>Viewing Period:</label>
            <span class="filter-badge"><?php echo $info['month'] . ' ' . $info['year']; ?></span>
            <span class="data-source">Showing ALL data for this user (not filtered by period)</span>
        </div>
        
        <div class="tabs">
            <div class="tab active" onclick="showSection('vat_sales')">Vatable Sales (<?php echo $vat_sales->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('non_vat_sales')">Non-VAT Sales (<?php echo $non_vat_sales->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('vat_purchases')">Vatable Purchases (<?php echo $vat_purchases->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('non_vat_purchases')">Non-VAT Purchases (<?php echo $non_vat_purchases->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('vat_expenses')">Vatable Expenses (<?php echo $vat_expenses->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('non_vat_expenses')">Non-VAT Expenses (<?php echo $non_vat_expenses->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('capex')">CAPEX (<?php echo $capex->num_rows; ?>)</div>
            <div class="tab" onclick="showSection('taxes')">Taxes & Licenses (<?php echo $taxes->num_rows; ?>)</div>
        </div>
        
        <!-- Vatable Sales -->
        <div id="vat_sales" class="data-section active">
            <div class="export-section">
                <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('vat_sales_table', 'Vatable_Sales_<?php echo $user_id; ?>')">Export This Table</a>
            </div>
            <h3>Vatable Sales <span class="record-count"><?php echo $vat_sales->num_rows; ?> records</span></h3>
            <?php if ($vat_sales->num_rows > 0): ?>
            <div class="table-container">
                <table id="vat_sales_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Address</th>
                            <th>TIN</th>
                            <th>Vatable Gross Sales</th>
                            <th>Net Sales</th>
                            <th>Output Tax</th>
                            <th>Exempt Sales</th>
                            <th>Invoice Type</th>
                            <th>Payment</th>
                            <th>W/Tax VAT</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Reset pointer and re-calculate for display
                        $vat_sales->data_seek(0);
                        while($sale = $vat_sales->fetch_assoc()): 
                            $vatable_gross_sales = isset($sale['vatable_gross_sales']) ? $sale['vatable_gross_sales'] : 0;
                            $net_sales = isset($sale['net_sales']) ? $sale['net_sales'] : ($vatable_gross_sales / 1.12);
                            $output_tax = isset($sale['output_tax']) ? $sale['output_tax'] : ($vatable_gross_sales - $net_sales);
                        ?>
                        <tr>
                            <td><?php echo isset($sale['date']) ? date('M d, Y', strtotime($sale['date'])) : 'N/A'; ?></td>
                            <td><?php echo isset($sale['invoice_number']) ? $sale['invoice_number'] : 'N/A'; ?></td>
                            <td><?php echo isset($sale['particulars']) ? htmlspecialchars($sale['particulars']) : (isset($sale['description']) ? htmlspecialchars($sale['description']) : 'N/A'); ?></td>
                            <td><?php echo isset($sale['address']) ? htmlspecialchars(substr($sale['address'], 0, 50)) . (strlen($sale['address']) > 50 ? '...' : '') : 'N/A'; ?></td>
                            <td><?php echo isset($sale['tin_number']) ? $sale['tin_number'] : 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($vatable_gross_sales, 2); ?></td>
                            <td class="amount">₱<?php echo number_format($net_sales, 2); ?></td>
                            <td class="amount">₱<?php echo number_format($output_tax, 2); ?></td>
                            <td class="amount">₱<?php echo isset($sale['exempt_sales']) ? number_format($sale['exempt_sales'], 2) : '0.00'; ?></td>
                            <td><span class="invoice-type"><?php echo isset($sale['invoice_type']) ? $sale['invoice_type'] : 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo isset($sale['mode_of_payment']) ? $sale['mode_of_payment'] : 'N/A'; ?></span></td>
                            <td><span class="tax-rate"><?php echo isset($sale['withholding_tax_vat']) ? $sale['withholding_tax_vat'] : 'N/A'; ?></span></td>
                            <td><?php echo isset($sale['remarks']) ? htmlspecialchars(substr($sale['remarks'], 0, 30)) . (strlen($sale['remarks']) > 30 ? '...' : '') : 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTALS:</strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($vat_sales_total, 2); ?></strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($net_sales_total, 2); ?></strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($output_tax_total, 2); ?></strong></td>
                            <td colspan="5"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">No vatable sales data found for this user.</div>
            <?php endif; ?>
        </div>
        
        <!-- Non-VAT Sales -->
        <div id="non_vat_sales" class="data-section">
            <div class="export-section">
                <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('non_vat_sales_table', 'Non_VAT_Sales_<?php echo $user_id; ?>')">Export This Table</a>
            </div>
            <h3>Non-VAT Sales <span class="record-count"><?php echo $non_vat_sales->num_rows; ?> records</span></h3>
            <?php if ($non_vat_sales->num_rows > 0): ?>
            <div class="table-container">
                <table id="non_vat_sales_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Address</th>
                            <th>TIN</th>
                            <th>Amount</th>
                            <th>Invoice Type</th>
                            <th>Payment</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $non_vat_sales->data_seek(0);
                        while($row = $non_vat_sales->fetch_assoc()): 
                            $amount = isset($row['amount']) ? $row['amount'] : (isset($row['vatable_gross_sales']) ? $row['vatable_gross_sales'] : 0);
                        ?>
                        <tr>
                            <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo isset($row['invoice_number']) ? $row['invoice_number'] : 'N/A'; ?></td>
                            <td><?php echo isset($row['particulars']) ? htmlspecialchars($row['particulars']) : (isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A'); ?></td>
                            <td><?php echo isset($row['address']) ? htmlspecialchars(substr($row['address'], 0, 50)) . (strlen($row['address']) > 50 ? '...' : '') : 'N/A'; ?></td>
                            <td><?php echo isset($row['tin_number']) ? $row['tin_number'] : 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($amount, 2); ?></td>
                            <td><span class="invoice-type"><?php echo isset($row['invoice_type']) ? $row['invoice_type'] : 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo isset($row['mode_of_payment']) ? $row['mode_of_payment'] : 'N/A'; ?></span></td>
                            <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 30)) . (strlen($row['remarks']) > 30 ? '...' : '') : 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTAL:</strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($non_vat_sales_total, 2); ?></strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">No non-VAT sales data found for this user.</div>
            <?php endif; ?>
        </div>
        
        <!-- Vatable Purchases -->
<div id="vat_purchases" class="data-section">
    <div class="export-section">
        <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('vat_purchases_table', 'Vatable_Purchases_<?php echo $user_id; ?>')">Export This Table</a>
    </div>
    <h3>Vatable Purchases <span class="record-count"><?php echo $vat_purchases->num_rows; ?> records</span></h3>
    <?php if ($vat_purchases->num_rows > 0): ?>
    <div class="table-container">
        <table id="vat_purchases_table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Supplier</th>
                    <th>Address</th>
                    <th>TIN</th>
                    <th>Vatable Gross Purchases</th>
                    <th>Net Purchases</th>
                    <th>Input Tax</th>
                    <th>Invoice Type</th>
                    <th>Payment</th>
                    <th>W/Tax Rate</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $vat_purchases->data_seek(0);
                while($row = $vat_purchases->fetch_assoc()): 
                    $vatable_gross_purchases = isset($row['vatable_gross_purchases']) ? $row['vatable_gross_purchases'] : 0;
                    $net_purchases = isset($row['net_purchases']) ? $row['net_purchases'] : ($vatable_gross_purchases / 1.12);
                    $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($vatable_gross_purchases - $net_purchases);
                ?>
                <tr>
                    <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                    <td><?php echo isset($row['invoice_number']) ? $row['invoice_number'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['particulars']) ? htmlspecialchars($row['particulars']) : (isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A'); ?></td>
                    <td><?php echo isset($row['address']) ? htmlspecialchars(substr($row['address'], 0, 50)) . (strlen($row['address']) > 50 ? '...' : '') : 'N/A'; ?></td>
                    <td><?php echo isset($row['tin_number']) ? $row['tin_number'] : 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($vatable_gross_purchases, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_purchases, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($input_tax, 2); ?></td>
                    <td><span class="invoice-type"><?php echo isset($row['invoice_type']) ? $row['invoice_type'] : 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo isset($row['mode_of_payment']) ? $row['mode_of_payment'] : 'N/A'; ?></span></td>
                    <td><span class="tax-rate"><?php echo isset($row['withholding_tax_rate']) ? $row['withholding_tax_rate'] : 'N/A'; ?></span></td>
                    <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 50)) . (strlen($row['remarks']) > 50 ? '...' : '') : 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="5"><strong>TOTALS:</strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($vat_purchases_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($net_purchases_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($input_tax_total, 2); ?></strong></td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="no-data">No vatable purchases data found for this user.</div>
    <?php endif; ?>
</div>
        
        <!-- Non-VAT Purchases -->
        <div id="non_vat_purchases" class="data-section">
            <div class="export-section">
                <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('non_vat_purchases_table', 'Non_VAT_Purchases_<?php echo $user_id; ?>')">Export This Table</a>
            </div>
            <h3>Non-VAT Purchases <span class="record-count"><?php echo $non_vat_purchases->num_rows; ?> records</span></h3>
            <?php if ($non_vat_purchases->num_rows > 0): ?>
            <div class="table-container">
                <table id="non_vat_purchases_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>Address</th>
                            <th>TIN</th>
                            <th>Amount</th>
                            <th>Invoice Type</th>
                            <th>Payment</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $non_vat_purchases->data_seek(0);
                        while($row = $non_vat_purchases->fetch_assoc()): 
                            $amount = isset($row['amount']) ? $row['amount'] : (isset($row['vatable_gross_purchases']) ? $row['vatable_gross_purchases'] : 0);
                        ?>
                        <tr>
                            <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo isset($row['invoice_number']) ? $row['invoice_number'] : 'N/A'; ?></td>
                            <td><?php echo isset($row['particulars']) ? htmlspecialchars($row['particulars']) : (isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A'); ?></td>
                            <td><?php echo isset($row['address']) ? htmlspecialchars(substr($row['address'], 0, 50)) . (strlen($row['address']) > 50 ? '...' : '') : 'N/A'; ?></td>
                            <td><?php echo isset($row['tin_number']) ? $row['tin_number'] : 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($amount, 2); ?></td>
                            <td><span class="invoice-type"><?php echo isset($row['invoice_type']) ? $row['invoice_type'] : 'N/A'; ?></span></td>
                            <td><span class="payment-type"><?php echo isset($row['mode_of_payment']) ? $row['mode_of_payment'] : 'N/A'; ?></span></td>
                            <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 50)) . (strlen($row['remarks']) > 50 ? '...' : '') : 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTAL:</strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($non_vat_purchases_total, 2); ?></strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">No non-VAT purchases data found for this user.</div>
            <?php endif; ?>
        </div>
        
        <!-- Vatable Expenses -->
<div id="vat_expenses" class="data-section">
    <div class="export-section">
        <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('vat_expenses_table', 'Vatable_Expenses_<?php echo $user_id; ?>')">Export This Table</a>
    </div>
    <h3>Vatable Expenses <span class="record-count"><?php echo $vat_expenses->num_rows; ?> records</span></h3>
    <?php if ($vat_expenses->num_rows > 0): ?>
    <div class="table-container">
        <table id="vat_expenses_table">
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
                </tr>
            </thead>
            <tbody>
                <?php 
                $vat_expenses->data_seek(0);
                while($row = $vat_expenses->fetch_assoc()): 
                    $gross_amount = isset($row['gross_amount']) ? $row['gross_amount'] : 0;
                    $net_amount = isset($row['net_amount']) ? $row['net_amount'] : ($gross_amount / 1.12);
                    $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($gross_amount - $net_amount);
                ?>
                <tr>
                    <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                    <td><?php echo isset($row['invoice_number']) ? $row['invoice_number'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['particulars']) ? htmlspecialchars($row['particulars']) : (isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A'); ?></td>
                    <td><?php echo isset($row['tin_number']) ? $row['tin_number'] : 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($gross_amount, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_amount, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($input_tax, 2); ?></td>
                    <td><span class="nature-expense" title="<?php echo isset($row['nature_of_expense']) ? htmlspecialchars($row['nature_of_expense']) : ''; ?>"><?php echo isset($row['nature_of_expense']) ? htmlspecialchars(substr($row['nature_of_expense'], 0, 40)) . (strlen($row['nature_of_expense']) > 40 ? '...' : '') : 'N/A'; ?></span></td>
                    <td><span class="invoice-type"><?php echo isset($row['invoice_type']) ? $row['invoice_type'] : 'N/A'; ?></span></td>
                    <td><span class="transaction-type"><?php echo isset($row['transaction_type']) ? $row['transaction_type'] : 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo isset($row['mode_of_payment']) ? $row['mode_of_payment'] : 'N/A'; ?></span></td>
                    <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 30)) . (strlen($row['remarks']) > 30 ? '...' : '') : 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="4"><strong>TOTALS:</strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($vat_expenses_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($net_expenses_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($input_tax_expenses_total, 2); ?></strong></td>
                    <td colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="no-data">No vatable expenses data found for this user.</div>
    <?php endif; ?>
</div>
        
        <!-- Non-VAT Expenses -->
        <div id="non_vat_expenses" class="data-section">
            <div class="export-section">
                <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('non_vat_expenses_table', 'Non_VAT_Expenses_<?php echo $user_id; ?>')">Export This Table</a>
            </div>
            <h3>Non-VAT Expenses <span class="record-count"><?php echo $non_vat_expenses->num_rows; ?> records</span></h3>
            <?php if ($non_vat_expenses->num_rows > 0): ?>
            <div class="table-container">
                <table id="non_vat_expenses_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>TIN</th>
                            <th>Amount</th>
                            <th>Nature of Expense</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $non_vat_expenses->data_seek(0);
                        while($row = $non_vat_expenses->fetch_assoc()): 
                            $amount = isset($row['amount']) ? $row['amount'] : (isset($row['gross_amount']) ? $row['gross_amount'] : 0);
                        ?>
                        <tr>
                            <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo isset($row['invoice_number']) ? $row['invoice_number'] : 'N/A'; ?></td>
                            <td><?php echo isset($row['particulars']) ? htmlspecialchars($row['particulars']) : (isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A'); ?></td>
                            <td><?php echo isset($row['tin_number']) ? $row['tin_number'] : 'N/A'; ?></td>
                            <td class="amount">₱<?php echo number_format($amount, 2); ?></td>
                            <td><span class="nature-expense" title="<?php echo isset($row['nature_of_expense']) ? htmlspecialchars($row['nature_of_expense']) : ''; ?>"><?php echo isset($row['nature_of_expense']) ? htmlspecialchars(substr($row['nature_of_expense'], 0, 40)) . (strlen($row['nature_of_expense']) > 40 ? '...' : '') : 'N/A'; ?></span></td>
                            <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 30)) . (strlen($row['remarks']) > 30 ? '...' : '') : 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="4"><strong>TOTAL:</strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($non_vat_expenses_total, 2); ?></strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">No non-VAT expenses data found for this user.</div>
            <?php endif; ?>
        </div>
        
        <!-- CAPEX -->
<div id="capex" class="data-section">
    <div class="export-section">
        <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('capex_table', 'CAPEX_<?php echo $user_id; ?>')">Export This Table</a>
    </div>
    <h3>CAPEX (Capital Expenditure) <span class="record-count"><?php echo $capex->num_rows; ?> records</span></h3>
    <?php if ($capex->num_rows > 0): ?>
    <div class="table-container">
        <table id="capex_table">
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
                    <th>Withholding Tax</th>
                    <th>Invoice Type</th>
                    <th>Payment</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $capex->data_seek(0);
                while($row = $capex->fetch_assoc()): 
                    $gross_purchase_non_vat = isset($row['gross_purchase_non_vat']) ? $row['gross_purchase_non_vat'] : 0;
                    $gross_purchase_vatable = isset($row['gross_purchase_vatable']) ? $row['gross_purchase_vatable'] : 0;
                    $net_vatable_purchase = isset($row['net_vatable_purchase']) ? $row['net_vatable_purchase'] : ($gross_purchase_vatable / 1.12);
                    $input_tax = isset($row['input_tax']) ? $row['input_tax'] : ($gross_purchase_vatable - $net_vatable_purchase);
                    $withholding_tax = isset($row['withholding_tax']) ? $row['withholding_tax'] : 0;
                ?>
                <tr>
                    <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                    <td><?php echo isset($row['invoice_number']) ? $row['invoice_number'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['particulars']) ? htmlspecialchars($row['particulars']) : (isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A'); ?></td>
                    <td class="asset-cell" title="<?php echo isset($row['asset_description']) ? htmlspecialchars($row['asset_description']) : ''; ?>"><?php echo isset($row['asset_description']) ? htmlspecialchars(substr($row['asset_description'], 0, 50)) . (strlen($row['asset_description']) > 50 ? '...' : '') : 'N/A'; ?></td>
                    <td><?php echo isset($row['tin_number']) ? $row['tin_number'] : 'N/A'; ?></td>
                    <td class="amount">₱<?php echo number_format($gross_purchase_non_vat, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($gross_purchase_vatable, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($net_vatable_purchase, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($input_tax, 2); ?></td>
                    <td class="amount">₱<?php echo number_format($withholding_tax, 2); ?></td>
                    <td><span class="invoice-type"><?php echo isset($row['invoice_type']) ? $row['invoice_type'] : 'N/A'; ?></span></td>
                    <td><span class="payment-type"><?php echo isset($row['mode_of_payment']) ? $row['mode_of_payment'] : 'N/A'; ?></span></td>
                    <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 30)) . (strlen($row['remarks']) > 30 ? '...' : '') : 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="5"><strong>TOTALS:</strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($capex_non_vat_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($capex_vatable_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($net_capex_vatable_total, 2); ?></strong></td>
                    <td class="amount"><strong>₱<?php echo number_format($input_tax_capex_total, 2); ?></strong></td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="no-data">No CAPEX data found for this user.</div>
    <?php endif; ?>
</div>
        
        <!-- Taxes & Licenses -->
        <div id="taxes" class="data-section">
            <div class="export-section">
                <a href="#" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.8rem;" onclick="exportTable('taxes_table', 'Taxes_Licenses_<?php echo $user_id; ?>')">Export This Table</a>
            </div>
            <h3>Taxes & Licenses <span class="record-count"><?php echo $taxes->num_rows; ?> records</span></h3>
            <?php if ($taxes->num_rows > 0): ?>
            <div class="table-container">
                <table id="taxes_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference #</th>
                            <th>Tax Type</th>
                            <th>Government Agency</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $taxes->data_seek(0);
                        while($row = $taxes->fetch_assoc()): 
                            $amount = isset($row['amount']) ? $row['amount'] : 0;
                        ?>
                        <tr>
                            <td><?php echo isset($row['date']) ? date('M d, Y', strtotime($row['date'])) : 'N/A'; ?></td>
                            <td><?php echo isset($row['reference_number']) ? $row['reference_number'] : 'N/A'; ?></td>
                            <td><span class="tax-type"><?php echo isset($row['tax_type']) ? $row['tax_type'] : 'N/A'; ?></span></td>
                            <td><span class="agency-type"><?php echo isset($row['government_agency']) ? htmlspecialchars($row['government_agency']) : 'N/A'; ?></span></td>
                            <td class="amount">₱<?php echo number_format($amount, 2); ?></td>
                            <td><?php echo isset($row['remarks']) ? htmlspecialchars(substr($row['remarks'], 0, 30)) . (strlen($row['remarks']) > 30 ? '...' : '') : 'N/A'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="total-row">
                            <td colspan="4"><strong>TOTAL:</strong></td>
                            <td class="amount"><strong>₱<?php echo number_format($taxes_total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">No taxes & licenses data found for this user.</div>
            <?php endif; ?>
        </div>
        
        <!-- Financial Summary -->
<div class="info-card">
    <h2>Financial Summary</h2>
    <?php
    // Calculate totals from all data
    $total_net_sales = $net_sales_total + $non_vat_sales_total; // Net Sales + Non-VAT Sales
    $total_purchases = $vat_purchases_total + $non_vat_purchases_total;
    $total_expenses = $vat_expenses_total + $non_vat_expenses_total;
    $total_capex = $capex_non_vat_total + $capex_vatable_total + $capex_total;
    $total_taxes = $taxes_total;
    
    // Calculate total input tax from all sections
    $total_input_tax_all = $input_tax_purchases_total + $input_tax_expenses_total + $input_tax_capex_total;
    
    $net_result = $total_net_sales - ($total_purchases + $total_expenses + $total_capex + $total_taxes);
    ?>
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Total Net Sales (Vatable Net + Non-VAT)</div>
            <div class="info-value amount">₱<?php echo number_format($total_net_sales, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">VAT Output Tax (12%)</div>
            <div class="info-value amount" style="color: #ff9800;">₱<?php echo number_format($output_tax_total, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Gross Sales (Net + VAT)</div>
            <div class="info-value amount">₱<?php echo number_format($vat_sales_total + $non_vat_sales_total, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Input Tax (All Sections)</div>
            <div class="info-value amount" style="color: #f44336;">₱<?php echo number_format($total_input_tax_all, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Purchases (VAT + Non-VAT)</div>
            <div class="info-value amount" style="color: #f44336;">₱<?php echo number_format($total_purchases, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Expenses (VAT + Non-VAT)</div>
            <div class="info-value amount" style="color: #f44336;">₱<?php echo number_format($total_expenses, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total CAPEX</div>
            <div class="info-value amount" style="color: #ff9800;">₱<?php echo number_format($total_capex, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Total Taxes & Licenses</div>
            <div class="info-value amount" style="color: #9c27b0;">₱<?php echo number_format($total_taxes, 2); ?></div>
        </div>
        <div class="info-item">
            <div class="info-label">Net Result (Net Sales - All Expenses)</div>
            <div class="info-value amount" style="color: <?php echo $net_result >= 0 ? '#4caf50' : '#f44336'; ?>; font-size: 1.3rem;">
                ₱<?php echo number_format($net_result, 2); ?>
            </div>
        </div>
    </div>
    
    <!-- Calculation Breakdown -->
    <div class="calculation-breakdown">
        <h3>Calculation Breakdown</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 0.9rem;">
            <div>
                <strong>Vatable Net Sales:</strong> 
                <span class="amount">₱<?php echo number_format($net_sales_total, 2); ?></span>
            </div>
            <div>
                <strong>+ Non-VAT Sales:</strong> 
                <span class="amount">₱<?php echo number_format($non_vat_sales_total, 2); ?></span>
            </div>
            <div>
                <strong>= Total Net Sales:</strong> 
                <span class="amount">₱<?php echo number_format($total_net_sales, 2); ?></span>
            </div>
            <div>
                <strong>Total VAT Output Tax:</strong> 
                <span class="amount">₱<?php echo number_format($output_tax_total, 2); ?></span>
            </div>
            <div>
                <strong>Vatable Net Purchases:</strong> 
                <span class="amount">₱<?php echo number_format($net_purchases_total, 2); ?></span>
            </div>
            <div>
                <strong>Total VAT Input Tax (Purchases):</strong> 
                <span class="amount">₱<?php echo number_format($input_tax_purchases_total, 2); ?></span>
            </div>
            <div>
                <strong>Vatable Net Expenses:</strong> 
                <span class="amount">₱<?php echo number_format($net_expenses_total, 2); ?></span>
            </div>
            <div>
                <strong>Total VAT Input Tax (Expenses):</strong> 
                <span class="amount">₱<?php echo number_format($input_tax_expenses_total, 2); ?></span>
            </div>
            <div>
                <strong>Net CAPEX Vatable Purchase:</strong> 
                <span class="amount">₱<?php echo number_format($net_capex_vatable_total, 2); ?></span>
            </div>
            <div>
                <strong>Total VAT Input Tax (CAPEX):</strong> 
                <span class="amount">₱<?php echo number_format($input_tax_capex_total, 2); ?></span>
            </div>
            <div>
                <strong>Total Input Tax:</strong> 
                <span class="amount" style="color: #f44336;">₱<?php 
                    $total_input_tax = $input_tax_purchases_total + $input_tax_expenses_total + $input_tax_capex_total;
                    echo number_format($total_input_tax, 2); 
                ?></span>
            </div>
        </div>
        <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
            <div style="font-family: 'Courier New', monospace; color: #ffc107;">
                <strong>Formula:</strong> Total Net Sales = Vatable Net Sales + Non-VAT Sales<br>
                <strong>Formula:</strong> Vatable Net = Vatable Gross ÷ 1.12<br>
                <strong>Total Input Tax:</strong> Purchases Input Tax + Expenses Input Tax + CAPEX Input Tax<br>
                <strong>VAT Payable:</strong> Output Tax - Total Input Tax = ₱<?php 
                    $total_input_tax = $input_tax_purchases_total + $input_tax_expenses_total + $input_tax_capex_total;
                    $vat_payable = $output_tax_total - $total_input_tax;
                    echo number_format($vat_payable, 2); 
                    if ($vat_payable >= 0) {
                        echo ' (Payable)';
                    } else {
                        echo ' (Refundable)';
                    }
                ?><br>
                <strong>Total CAPEX Investment:</strong> Non-VAT + Net Vatable + Input Tax = ₱<?php 
                    $total_capex_investment = $capex_non_vat_total + $net_capex_vatable_total + $input_tax_capex_total;
                    echo number_format($total_capex_investment, 2);
                ?>
            </div>
        </div>
    </div>
</div>
        
    </div>
    
    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.data-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).style.display = 'block';
            
            // Set active tab
            event.target.classList.add('active');
        }
        
        function exportTable(tableId, filename) {
            const table = document.getElementById(tableId);
            const html = table.outerHTML;
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename + '.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        
        // Make nature of expense cells expand on hover
        document.addEventListener('DOMContentLoaded', function() {
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