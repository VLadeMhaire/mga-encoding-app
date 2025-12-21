<?php
require_once 'config.php';
requireAdmin();

if (!isset($_GET['user_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$user_id = intval($_GET['user_id']);

// Get user info
$user_query = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    header('Location: admin_dashboard.php');
    exit();
}

// Set headers for Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $user['username'] . '_data_' . date('Y-m-d') . '.xls"');

echo '<html><meta charset="UTF-8"><body>';
echo '<h2 style="color: #1a237e;">Data Export for: ' . htmlspecialchars($user['username']) . '</h2>';
echo '<p>Export Date: ' . date('Y-m-d H:i:s') . '</p><br>';

// Function to create Excel table
function createExcelTable($title, $headers, $data) {
    echo '<table border="1" cellpadding="5" style="border-collapse: collapse; margin-bottom: 20px;">';
    echo '<tr><th colspan="' . count($headers) . '" style="background-color: #1a237e; color: white; padding: 12px; font-size: 14px;">' . $title . '</th></tr>';
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th style="background-color: #2196F3; color: white; padding: 8px; font-weight: bold;">' . $header . '</th>';
    }
    echo '</tr>';
    
    if ($data->num_rows > 0) {
        while($row = $data->fetch_assoc()) {
            echo '<tr>';
            foreach($row as $cell) {
                echo '<td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="' . count($headers) . '" style="text-align: center; padding: 10px;">No data found</td></tr>';
    }
    
    echo '</table><br>';
}

// 1. Company Information for this user
$info_query = $conn->prepare("SELECT 
    company_name AS 'Company Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT(month, ' ', year) AS 'Period',
    authorized_employee AS 'Authorized Employee',
    contact_number AS 'Contact Number',
    email AS 'Email',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM information WHERE user_id = ? ORDER BY submitted_at DESC");
$info_query->bind_param("i", $user_id);
$info_query->execute();
$info_result = $info_query->get_result();

createExcelTable('COMPANY INFORMATION', ['Company Name', 'Address', 'TIN Number', 'Period', 'Authorized Employee', 'Contact Number', 'Email', 'Submitted Date'], $info_result);

// 2. Vatable Sales for this user
$sales_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Customer',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(vatable_gross_sales, 2)) AS 'Vatable Gross Sales',
    CONCAT('₱', FORMAT(net_sales, 2)) AS 'Net Sales',
    CONCAT('₱', FORMAT(output_tax, 2)) AS 'Output Tax',
    gross_sales_type AS 'Sales Type',
    CONCAT('₱', FORMAT(exempt_sales, 2)) AS 'Exempt Sales',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Payment',
    withholding_tax_vat AS 'W/Tax VAT',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_sales WHERE user_id = ? ORDER BY date DESC");
$sales_query->bind_param("i", $user_id);
$sales_query->execute();
$sales_result = $sales_query->get_result();

createExcelTable('VATABLE SALES', ['Date', 'Invoice #', 'Customer', 'Address', 'TIN', 'Vatable Gross Sales', 
                                   'Net Sales', 'Output Tax', 'Sales Type', 'Exempt Sales', 'Invoice Type', 'Payment', 
                                   'W/Tax VAT', 'Remarks', 'Submitted Date'], $sales_result);

// 3. Non-VAT Sales for this user
$non_vat_sales_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Customer',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(vatable_gross_sales, 2)) AS 'Amount',
    CONCAT('₱', FORMAT(exempt_sales, 2)) AS 'Exempt Sales',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Payment',
    withholding_tax_vat AS 'W/Tax VAT',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_sales WHERE user_id = ? ORDER BY date DESC");
$non_vat_sales_query->bind_param("i", $user_id);
$non_vat_sales_query->execute();
$non_vat_sales_result = $non_vat_sales_query->get_result();

createExcelTable('NON-VAT SALES', ['Date', 'Invoice #', 'Customer', 'Address', 'TIN', 'Amount',
                                   'Exempt Sales', 'Invoice Type', 'Payment', 'W/Tax VAT', 'Remarks', 'Submitted Date'], $non_vat_sales_result);

// 4. Vatable Purchases for this user
$vat_purchases_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Supplier',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(vatable_gross_purchases, 2)) AS 'Vatable Gross Purchases',
    CONCAT('₱', FORMAT(net_purchases, 2)) AS 'Net Purchases',
    CONCAT('₱', FORMAT(input_tax, 2)) AS 'Input Tax',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Payment',
    withholding_tax_rate AS 'W/Tax Rate',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_purchases WHERE user_id = ? ORDER BY date DESC");
$vat_purchases_query->bind_param("i", $user_id);
$vat_purchases_query->execute();
$vat_purchases_result = $vat_purchases_query->get_result();

createExcelTable('VATABLE PURCHASES', ['Date', 'Invoice #', 'Supplier', 'Address', 'TIN', 'Vatable Gross Purchases',
                                       'Net Purchases', 'Input Tax', 'Invoice Type', 'Payment', 'W/Tax Rate', 'Remarks', 'Submitted Date'], $vat_purchases_result);

// 5. Non-VAT Purchases for this user
$non_vat_purchases_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Supplier',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(vatable_gross_purchases, 2)) AS 'Non-Vatable Purchases',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Payment',
    withholding_tax_rate AS 'W/Tax Rate',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_purchases WHERE user_id = ? ORDER BY date DESC");
$non_vat_purchases_query->bind_param("i", $user_id);
$non_vat_purchases_query->execute();
$non_vat_purchases_result = $non_vat_purchases_query->get_result();

createExcelTable('NON-VAT PURCHASES', ['Date', 'Invoice #', 'Supplier', 'Address', 'TIN', 'Non-Vatable Purchases',
                                       'Invoice Type', 'Payment', 'W/Tax Rate', 'Remarks', 'Submitted Date'], $non_vat_purchases_result);

// 6. Vatable Expenses for this user
$vat_expenses_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Supplier',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(gross_amount, 2)) AS 'Gross Amount',
    CONCAT('₱', FORMAT(net_amount, 2)) AS 'Net Expense',
    CONCAT('₱', FORMAT(input_tax, 2)) AS 'Input Tax',
    nature_of_expense AS 'Nature of Expense',
    invoice_type AS 'Invoice Type',
    transaction_type AS 'Transaction',
    mode_of_payment AS 'Payment',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_expenses WHERE user_id = ? ORDER BY date DESC");
$vat_expenses_query->bind_param("i", $user_id);
$vat_expenses_query->execute();
$vat_expenses_result = $vat_expenses_query->get_result();

createExcelTable('VATABLE EXPENSES', ['Date', 'Invoice #', 'Supplier', 'Address', 'TIN', 'Gross Amount',
                                      'Net Expense', 'Input Tax', 'Nature of Expense', 'Invoice Type', 'Transaction', 'Payment', 
                                      'Remarks', 'Submitted Date'], $vat_expenses_result);

// 7. Non-VAT Expenses for this user
$non_vat_expenses_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Supplier',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(gross_amount, 2)) AS 'Amount',
    nature_of_expense AS 'Nature of Expense',
    invoice_type AS 'Invoice Type',
    transaction_type AS 'Transaction',
    mode_of_payment AS 'Payment',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_expenses WHERE user_id = ? ORDER BY date DESC");
$non_vat_expenses_query->bind_param("i", $user_id);
$non_vat_expenses_query->execute();
$non_vat_expenses_result = $non_vat_expenses_query->get_result();

createExcelTable('NON-VAT EXPENSES', ['Date', 'Invoice #', 'Supplier', 'Address', 'TIN', 'Amount',
                                      'Nature of Expense', 'Invoice Type', 'Transaction', 'Payment', 'Remarks', 'Submitted Date'], $non_vat_expenses_result);

// 8. CAPEX for this user
$capex_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Supplier',
    asset_description AS 'Asset Description',
    address AS 'Address',
    tin_number AS 'TIN',
    CONCAT('₱', FORMAT(gross_purchase_non_vat, 2)) AS 'Non-VAT Purchase',
    CONCAT('₱', FORMAT(gross_purchase_vatable, 2)) AS 'Vatable Gross Purchase',
    CONCAT('₱', FORMAT(net_vatable_purchase, 2)) AS 'Net Vatable Purchase',
    CONCAT('₱', FORMAT(input_tax, 2)) AS 'Input Tax',
    CONCAT('₱', FORMAT(withholding_tax, 2)) AS 'Withholding Tax',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Payment',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM capex WHERE user_id = ? ORDER BY date DESC");
$capex_query->bind_param("i", $user_id);
$capex_query->execute();
$capex_result = $capex_query->get_result();

createExcelTable('CAPEX', ['Date', 'Invoice #', 'Supplier', 'Asset Description', 'Address', 'TIN',
                           'Non-VAT Purchase', 'Vatable Gross Purchase', 'Net Vatable Purchase', 'Input Tax', 'Withholding Tax',
                           'Invoice Type', 'Payment', 'Remarks', 'Submitted Date'], $capex_result);

// 9. Taxes & Licenses for this user
$taxes_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    reference_number AS 'Reference #',
    tax_type AS 'Tax Type',
    mode_of_payment AS 'Payment',
    government_agency AS 'Government Agency',
    CONCAT('₱', FORMAT(amount, 2)) AS 'Amount',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM taxes_licenses WHERE user_id = ? ORDER BY date DESC");
$taxes_query->bind_param("i", $user_id);
$taxes_query->execute();
$taxes_result = $taxes_query->get_result();

createExcelTable('TAXES & LICENSES', ['Date', 'Reference #', 'Tax Type', 'Payment', 'Government Agency', 'Amount', 'Remarks', 'Submitted Date'], $taxes_result);

// Calculate totals and add Financial Summary
echo '<h3 style="color: #1a237e; margin-top: 30px; padding-top: 20px; border-top: 2px solid #1a237e;">FINANCIAL SUMMARY</h3>';

// Fetch and calculate totals for summary
// Vatable Sales Total
$sales_total_query = $conn->prepare("SELECT 
    SUM(vatable_gross_sales) as total_gross_sales,
    SUM(net_sales) as total_net_sales,
    SUM(output_tax) as total_output_tax
    FROM vatable_sales WHERE user_id = ?");
$sales_total_query->bind_param("i", $user_id);
$sales_total_query->execute();
$sales_totals = $sales_total_query->get_result()->fetch_assoc();

// Non-VAT Sales Total
$non_vat_sales_total_query = $conn->prepare("SELECT 
    SUM(vatable_gross_sales) as total_non_vat_sales
    FROM non_vat_sales WHERE user_id = ?");
$non_vat_sales_total_query->bind_param("i", $user_id);
$non_vat_sales_total_query->execute();
$non_vat_sales_totals = $non_vat_sales_total_query->get_result()->fetch_assoc();

// Vatable Purchases Total
$vat_purchases_total_query = $conn->prepare("SELECT 
    SUM(vatable_gross_purchases) as total_gross_purchases,
    SUM(net_purchases) as total_net_purchases,
    SUM(input_tax) as total_input_tax_purchases
    FROM vatable_purchases WHERE user_id = ?");
$vat_purchases_total_query->bind_param("i", $user_id);
$vat_purchases_total_query->execute();
$vat_purchases_totals = $vat_purchases_total_query->get_result()->fetch_assoc();

// Non-VAT Purchases Total
$non_vat_purchases_total_query = $conn->prepare("SELECT 
    SUM(vatable_gross_purchases) as total_non_vat_purchases
    FROM non_vat_purchases WHERE user_id = ?");
$non_vat_purchases_total_query->bind_param("i", $user_id);
$non_vat_purchases_total_query->execute();
$non_vat_purchases_totals = $non_vat_purchases_total_query->get_result()->fetch_assoc();

// Vatable Expenses Total
$vat_expenses_total_query = $conn->prepare("SELECT 
    SUM(gross_amount) as total_gross_expenses,
    SUM(net_amount) as total_net_expenses,
    SUM(input_tax) as total_input_tax_expenses
    FROM vatable_expenses WHERE user_id = ?");
$vat_expenses_total_query->bind_param("i", $user_id);
$vat_expenses_total_query->execute();
$vat_expenses_totals = $vat_expenses_total_query->get_result()->fetch_assoc();

// Non-VAT Expenses Total
$non_vat_expenses_total_query = $conn->prepare("SELECT 
    SUM(gross_amount) as total_non_vat_expenses
    FROM non_vat_expenses WHERE user_id = ?");
$non_vat_expenses_total_query->bind_param("i", $user_id);
$non_vat_expenses_total_query->execute();
$non_vat_expenses_totals = $non_vat_expenses_total_query->get_result()->fetch_assoc();

// CAPEX Total
$capex_total_query = $conn->prepare("SELECT 
    SUM(gross_purchase_non_vat) as total_non_vat_capex,
    SUM(gross_purchase_vatable) as total_vatable_capex,
    SUM(net_vatable_purchase) as total_net_vatable_capex,
    SUM(input_tax) as total_input_tax_capex
    FROM capex WHERE user_id = ?");
$capex_total_query->bind_param("i", $user_id);
$capex_total_query->execute();
$capex_totals = $capex_total_query->get_result()->fetch_assoc();

// Taxes & Licenses Total
$taxes_total_query = $conn->prepare("SELECT 
    SUM(amount) as total_taxes
    FROM taxes_licenses WHERE user_id = ?");
$taxes_total_query->bind_param("i", $user_id);
$taxes_total_query->execute();
$taxes_totals = $taxes_total_query->get_result()->fetch_assoc();

// Calculate summary values
$total_net_sales = ($sales_totals['total_net_sales'] ?: 0) + ($non_vat_sales_totals['total_non_vat_sales'] ?: 0);
$total_output_tax = $sales_totals['total_output_tax'] ?: 0;
$total_gross_sales = ($sales_totals['total_gross_sales'] ?: 0) + ($non_vat_sales_totals['total_non_vat_sales'] ?: 0);

$total_input_tax = 
    ($vat_purchases_totals['total_input_tax_purchases'] ?: 0) + 
    ($vat_expenses_totals['total_input_tax_expenses'] ?: 0) + 
    ($capex_totals['total_input_tax_capex'] ?: 0);

$total_purchases = 
    ($vat_purchases_totals['total_gross_purchases'] ?: 0) + 
    ($non_vat_purchases_totals['total_non_vat_purchases'] ?: 0);

$total_expenses = 
    ($vat_expenses_totals['total_gross_expenses'] ?: 0) + 
    ($non_vat_expenses_totals['total_non_vat_expenses'] ?: 0);

$total_capex = 
    ($capex_totals['total_non_vat_capex'] ?: 0) + 
    ($capex_totals['total_vatable_capex'] ?: 0);

$total_taxes = $taxes_totals['total_taxes'] ?: 0;

$vat_payable = $total_output_tax - $total_input_tax;

// Create Financial Summary table
echo '<table border="1" cellpadding="5" style="border-collapse: collapse; margin-bottom: 20px; background-color: #f8f9fa;">';
echo '<tr><th colspan="2" style="background-color: #4CAF50; color: white; padding: 12px; font-size: 14px;">FINANCIAL SUMMARY</th></tr>';

// Sales Section
echo '<tr><td colspan="2" style="background-color: #e8f5e8; padding: 8px; font-weight: bold;">SALES</td></tr>';
echo '<tr><td style="padding: 6px;">Total Net Sales (Vatable Net + Non-VAT)</td><td style="padding: 6px; text-align: right;">₱' . number_format($total_net_sales, 2) . '</td></tr>';
echo '<tr><td style="padding: 6px;">VAT Output Tax (12%)</td><td style="padding: 6px; text-align: right; color: #ff9800;">₱' . number_format($total_output_tax, 2) . '</td></tr>';
echo '<tr><td style="padding: 6px;">Total Gross Sales (Net + VAT)</td><td style="padding: 6px; text-align: right;">₱' . number_format($total_gross_sales, 2) . '</td></tr>';

// Purchases & Expenses Section
echo '<tr><td colspan="2" style="background-color: #ffe8e8; padding: 8px; font-weight: bold;">PURCHASES & EXPENSES</td></tr>';
echo '<tr><td style="padding: 6px;">Total Input Tax (All Sections)</td><td style="padding: 6px; text-align: right; color: #f44336;">₱' . number_format($total_input_tax, 2) . '</td></tr>';
echo '<tr><td style="padding: 6px;">Total Purchases (VAT + Non-VAT)</td><td style="padding: 6px; text-align: right;">₱' . number_format($total_purchases, 2) . '</td></tr>';
echo '<tr><td style="padding: 6px;">Total Expenses (VAT + Non-VAT)</td><td style="padding: 6px; text-align: right;">₱' . number_format($total_expenses, 2) . '</td></tr>';
echo '<tr><td style="padding: 6px;">Total CAPEX</td><td style="padding: 6px; text-align: right;">₱' . number_format($total_capex, 2) . '</td></tr>';
echo '<tr><td style="padding: 6px;">Total Taxes & Licenses</td><td style="padding: 6px; text-align: right;">₱' . number_format($total_taxes, 2) . '</td></tr>';

// Net Result Section
echo '<tr><td colspan="2" style="background-color: #e8e8ff; padding: 8px; font-weight: bold;">NET RESULT</td></tr>';
echo '<tr><td style="padding: 6px;">VAT Payable (Output Tax - Input Tax)</td><td style="padding: 6px; text-align: right; font-weight: bold; color: ' . ($vat_payable >= 0 ? '#f44336' : '#4caf50') . ';">₱' . number_format(abs($vat_payable), 2) . ' (' . ($vat_payable >= 0 ? 'Payable' : 'Refundable') . ')</td></tr>';

$total_expenditures = $total_purchases + $total_expenses + $total_capex + $total_taxes;
$net_result = $total_net_sales - $total_expenditures;

echo '<tr><td style="padding: 6px; font-weight: bold;">Net Result (Net Sales - All Expenses)</td><td style="padding: 6px; text-align: right; font-weight: bold; color: ' . ($net_result >= 0 ? '#4caf50' : '#f44336') . '; font-size: 1.1em;">₱' . number_format($net_result, 2) . '</td></tr>';

echo '</table>';

// Add detailed calculation breakdown
echo '<table border="1" cellpadding="5" style="border-collapse: collapse; margin-bottom: 20px; background-color: #f0f8ff;">';
echo '<tr><th colspan="3" style="background-color: #2196F3; color: white; padding: 12px; font-size: 14px;">DETAILED CALCULATION BREAKDOWN</th></tr>';

// Vatable Sales Breakdown
if ($sales_totals['total_gross_sales']) {
    echo '<tr><td colspan="3" style="background-color: #e3f2fd; padding: 8px; font-weight: bold;">VATABLE SALES</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Vatable Gross Sales</td><td style="padding: 6px; text-align: right;">₱' . number_format($sales_totals['total_gross_sales'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">From ' . ($sales_result->num_rows ?: 0) . ' records</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Net Sales</td><td style="padding: 6px; text-align: right;">₱' . number_format($sales_totals['total_net_sales'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">Net = Gross ÷ 1.12</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Output Tax</td><td style="padding: 6px; text-align: right; color: #ff9800;">₱' . number_format($sales_totals['total_output_tax'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">12% of Net Sales</td></tr>';
}

// Non-VAT Sales Breakdown
if ($non_vat_sales_totals['total_non_vat_sales']) {
    echo '<tr><td colspan="3" style="background-color: #f3e5f5; padding: 8px; font-weight: bold;">NON-VAT SALES</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Non-VAT Sales</td><td style="padding: 6px; text-align: right;">₱' . number_format($non_vat_sales_totals['total_non_vat_sales'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">From ' . ($non_vat_sales_result->num_rows ?: 0) . ' records</td></tr>';
}

// Vatable Purchases Breakdown
if ($vat_purchases_totals['total_gross_purchases']) {
    echo '<tr><td colspan="3" style="background-color: #e8f5e9; padding: 8px; font-weight: bold;">VATABLE PURCHASES</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Vatable Gross Purchases</td><td style="padding: 6px; text-align: right;">₱' . number_format($vat_purchases_totals['total_gross_purchases'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">From ' . ($vat_purchases_result->num_rows ?: 0) . ' records</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Net Purchases</td><td style="padding: 6px; text-align: right;">₱' . number_format($vat_purchases_totals['total_net_purchases'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">Net = Gross ÷ 1.12</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Input Tax (Purchases)</td><td style="padding: 6px; text-align: right; color: #f44336;">₱' . number_format($vat_purchases_totals['total_input_tax_purchases'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">12% of Net Purchases</td></tr>';
}

// Vatable Expenses Breakdown
if ($vat_expenses_totals['total_gross_expenses']) {
    echo '<tr><td colspan="3" style="background-color: #fff3e0; padding: 8px; font-weight: bold;">VATABLE EXPENSES</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Vatable Gross Expenses</td><td style="padding: 6px; text-align: right;">₱' . number_format($vat_expenses_totals['total_gross_expenses'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">From ' . ($vat_expenses_result->num_rows ?: 0) . ' records</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Net Expenses</td><td style="padding: 6px; text-align: right;">₱' . number_format($vat_expenses_totals['total_net_expenses'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">Net = Gross ÷ 1.12</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Input Tax (Expenses)</td><td style="padding: 6px; text-align: right; color: #f44336;">₱' . number_format($vat_expenses_totals['total_input_tax_expenses'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">12% of Net Expenses</td></tr>';
}

// CAPEX Breakdown
if ($capex_totals['total_vatable_capex']) {
    echo '<tr><td colspan="3" style="background-color: #e0f7fa; padding: 8px; font-weight: bold;">CAPEX</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Vatable CAPEX</td><td style="padding: 6px; text-align: right;">₱' . number_format($capex_totals['total_vatable_capex'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">From ' . ($capex_result->num_rows ?: 0) . ' records</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Net CAPEX</td><td style="padding: 6px; text-align: right;">₱' . number_format($capex_totals['total_net_vatable_capex'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">Net = Gross ÷ 1.12</td></tr>';
    echo '<tr><td style="padding: 6px;">Total Input Tax (CAPEX)</td><td style="padding: 6px; text-align: right; color: #f44336;">₱' . number_format($capex_totals['total_input_tax_capex'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">12% of Net CAPEX</td></tr>';
}

// Non-VAT CAPEX
if ($capex_totals['total_non_vat_capex']) {
    echo '<tr><td style="padding: 6px;">Total Non-VAT CAPEX</td><td style="padding: 6px; text-align: right;">₱' . number_format($capex_totals['total_non_vat_capex'], 2) . '</td><td style="padding: 6px; font-size: 0.9em;">Direct expense</td></tr>';
}

echo '</table>';

// Add export summary
echo '<h3 style="color: #1a237e; margin-top: 30px; padding-top: 20px; border-top: 2px solid #1a237e;">EXPORT SUMMARY</h3>';
echo '<p>Total records exported:</p>';
echo '<ul>';
echo '<li>Company Information: ' . $info_result->num_rows . ' records</li>';
echo '<li>Vatable Sales: ' . $sales_result->num_rows . ' records</li>';
echo '<li>Non-VAT Sales: ' . $non_vat_sales_result->num_rows . ' records</li>';
echo '<li>Vatable Purchases: ' . $vat_purchases_result->num_rows . ' records</li>';
echo '<li>Non-VAT Purchases: ' . $non_vat_purchases_result->num_rows . ' records</li>';
echo '<li>Vatable Expenses: ' . $vat_expenses_result->num_rows . ' records</li>';
echo '<li>Non-VAT Expenses: ' . $non_vat_expenses_result->num_rows . ' records</li>';
echo '<li>CAPEX: ' . $capex_result->num_rows . ' records</li>';
echo '<li>Taxes & Licenses: ' . $taxes_result->num_rows . ' records</li>';
echo '</ul>';

echo '</body></html>';
exit();
?>