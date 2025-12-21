<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Set headers for Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $username . '_data_' . date('Y-m-d') . '.xls"');

// Function to create Excel table
function createExcelTable($title, $headers, $data) {
    echo '<table border="1" style="border-collapse: collapse;">';
    echo '<tr><th colspan="' . count($headers) . '" style="background-color: #4CAF50; color: white; padding: 10px;">' . $title . '</th></tr>';
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th style="background-color: #2196F3; color: white; padding: 8px;">' . $header . '</th>';
    }
    echo '</tr>';
    
    if ($data->num_rows > 0) {
        while($row = $data->fetch_assoc()) {
            echo '<tr>';
            foreach($row as $cell) {
                echo '<td style="padding: 6px;">' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
    }
    
    echo '</table><br><br>';
}

$has_data = false;

// 1. Company Information
$info_query = $conn->prepare("SELECT 
    company_name AS 'Company Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT(month, ' ', year) AS 'Period',
    authorized_employee AS 'Authorized Employee',
    contact_number AS 'Contact Number',
    email AS 'Email',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM information WHERE user_id = ?");
$info_query->bind_param("i", $user_id);
$info_query->execute();
$info_result = $info_query->get_result();

if ($info_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'COMPANY INFORMATION',
        ['Company Name', 'Address', 'TIN Number', 'Period', 'Authorized Employee', 'Contact Number', 'Email', 'Submitted Date'],
        $info_result
    );
}

// 2. Vatable Sales
$sales_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Customer Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(vatable_gross_sales, 2)) AS 'Vatable Gross Sales',
    CONCAT('₱', FORMAT(net_sales, 2)) AS 'Net Sales',
    CONCAT('₱', FORMAT(output_tax, 2)) AS 'Output Tax',
    gross_sales_type AS 'Sales Type',
    CONCAT('₱', FORMAT(exempt_sales, 2)) AS 'Exempt Sales',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Mode of Payment',
    withholding_tax_vat AS 'Withholding Tax VAT',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_sales WHERE user_id = ?");
$sales_query->bind_param("i", $user_id);
$sales_query->execute();
$sales_result = $sales_query->get_result();

if ($sales_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'VATABLE SALES',
        ['Date', 'Invoice #', 'Particulars/Customer Name', 'Address', 'TIN Number', 'Vatable Gross Sales', 
         'Net Sales', 'Output Tax', 'Sales Type', 'Exempt Sales', 'Invoice Type', 'Mode of Payment', 
         'Withholding Tax VAT', 'Remarks', 'Submitted Date'],
        $sales_result
    );
}

// 3. Non-VAT Sales
$non_vat_sales_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Customer Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(vatable_gross_sales, 2)) AS 'Vatable Gross Sales',
    CONCAT('₱', FORMAT(exempt_sales, 2)) AS 'Exempt Sales',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Mode of Payment',
    withholding_tax_vat AS 'Withholding Tax VAT',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_sales WHERE user_id = ?");
$non_vat_sales_query->bind_param("i", $user_id);
$non_vat_sales_query->execute();
$non_vat_sales_result = $non_vat_sales_query->get_result();

if ($non_vat_sales_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'NON-VAT SALES',
        ['Date', 'Invoice #', 'Particulars/Customer Name', 'Address', 'TIN Number', 'Vatable Gross Sales',
         'Exempt Sales', 'Invoice Type', 'Mode of Payment', 'Withholding Tax VAT', 'Remarks', 'Submitted Date'],
        $non_vat_sales_result
    );
}

// 4. Vatable Purchases
$vat_purchases_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Supplier Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(vatable_gross_purchases, 2)) AS 'Vatable Gross Purchases',
    CONCAT('₱', FORMAT(net_purchases, 2)) AS 'Net Purchases',
    CONCAT('₱', FORMAT(input_tax, 2)) AS 'Input Tax',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Mode of Payment',
    withholding_tax_rate AS 'Withholding Tax Rate',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_purchases WHERE user_id = ?");
$vat_purchases_query->bind_param("i", $user_id);
$vat_purchases_query->execute();
$vat_purchases_result = $vat_purchases_query->get_result();

if ($vat_purchases_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'VATABLE PURCHASES',
        ['Date', 'Invoice #', 'Particulars/Supplier Name', 'Address', 'TIN Number', 'Vatable Gross Purchases',
         'Net Purchases', 'Input Tax', 'Invoice Type', 'Mode of Payment', 'Withholding Tax Rate', 'Remarks', 'Submitted Date'],
        $vat_purchases_result
    );
}

// 5. Non-VAT Purchases
$non_vat_purchases_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Supplier Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(vatable_gross_purchases, 2)) AS 'Non-Vatable Gross Purchases',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Mode of Payment',
    withholding_tax_rate AS 'Withholding Tax Rate',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_purchases WHERE user_id = ?");
$non_vat_purchases_query->bind_param("i", $user_id);
$non_vat_purchases_query->execute();
$non_vat_purchases_result = $non_vat_purchases_query->get_result();

if ($non_vat_purchases_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'NON-VAT PURCHASES',
        ['Date', 'Invoice #', 'Particulars/Supplier Name', 'Address', 'TIN Number', 'Non-Vatable Gross Purchases',
         'Invoice Type', 'Mode of Payment', 'Withholding Tax Rate', 'Remarks', 'Submitted Date'],
        $non_vat_purchases_result
    );
}

// 6. Vatable Expenses
$vat_expenses_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Supplier Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(gross_amount, 2)) AS 'Gross Amount',
    CONCAT('₱', FORMAT(net_amount, 2)) AS 'Net Amount',
    CONCAT('₱', FORMAT(input_tax, 2)) AS 'Input Tax',
    nature_of_expense AS 'Nature of Expense',
    invoice_type AS 'Invoice Type',
    transaction_type AS 'Transaction Type',
    mode_of_payment AS 'Mode of Payment',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_expenses WHERE user_id = ?");
$vat_expenses_query->bind_param("i", $user_id);
$vat_expenses_query->execute();
$vat_expenses_result = $vat_expenses_query->get_result();

if ($vat_expenses_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'VATABLE EXPENSES',
        ['Date', 'Invoice #', 'Particulars/Supplier Name', 'Address', 'TIN Number', 'Gross Amount',
         'Net Amount', 'Input Tax', 'Nature of Expense', 'Invoice Type', 'Transaction Type', 'Mode of Payment', 
         'Remarks', 'Submitted Date'],
        $vat_expenses_result
    );
}

// 7. Non-VAT Expenses
$non_vat_expenses_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Supplier Name',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(gross_amount, 2)) AS 'Gross Amount',
    nature_of_expense AS 'Nature of Expense',
    invoice_type AS 'Invoice Type',
    transaction_type AS 'Transaction Type',
    mode_of_payment AS 'Mode of Payment',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_expenses WHERE user_id = ?");
$non_vat_expenses_query->bind_param("i", $user_id);
$non_vat_expenses_query->execute();
$non_vat_expenses_result = $non_vat_expenses_query->get_result();

if ($non_vat_expenses_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'NON-VAT EXPENSES',
        ['Date', 'Invoice #', 'Particulars/Supplier Name', 'Address', 'TIN Number', 'Gross Amount',
         'Nature of Expense', 'Invoice Type', 'Transaction Type', 'Mode of Payment', 'Remarks', 'Submitted Date'],
        $non_vat_expenses_result
    );
}

// 8. CAPEX
$capex_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    invoice_number AS 'Invoice #',
    particulars AS 'Particulars/Supplier Name',
    asset_description AS 'Asset Description',
    address AS 'Address',
    tin_number AS 'TIN Number',
    CONCAT('₱', FORMAT(gross_purchase_non_vat, 2)) AS 'Non-VAT Purchase',
    CONCAT('₱', FORMAT(gross_purchase_vatable, 2)) AS 'Vatable Gross Purchase',
    CONCAT('₱', FORMAT(net_vatable_purchase, 2)) AS 'Net Vatable Purchase',
    CONCAT('₱', FORMAT(input_tax, 2)) AS 'Input Tax',
    CONCAT('₱', FORMAT(withholding_tax, 2)) AS 'Withholding Tax',
    invoice_type AS 'Invoice Type',
    mode_of_payment AS 'Mode of Payment',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM capex WHERE user_id = ?");
$capex_query->bind_param("i", $user_id);
$capex_query->execute();
$capex_result = $capex_query->get_result();

if ($capex_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'CAPEX',
        ['Date', 'Invoice #', 'Particulars/Supplier Name', 'Asset Description', 'Address', 'TIN Number',
         'Non-VAT Purchase', 'Vatable Gross Purchase', 'Net Vatable Purchase', 'Input Tax', 'Withholding Tax',
         'Invoice Type', 'Mode of Payment', 'Remarks', 'Submitted Date'],
        $capex_result
    );
}

// 9. Taxes & Licenses
$taxes_query = $conn->prepare("SELECT 
    DATE_FORMAT(date, '%Y-%m-%d') AS 'Date',
    reference_number AS 'Reference #',
    tax_type AS 'Tax Type',
    mode_of_payment AS 'Mode of Payment',
    government_agency AS 'Government Agency',
    CONCAT('₱', FORMAT(amount, 2)) AS 'Amount',
    remarks AS 'Remarks',
    DATE_FORMAT(submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM taxes_licenses WHERE user_id = ?");
$taxes_query->bind_param("i", $user_id);
$taxes_query->execute();
$taxes_result = $taxes_query->get_result();

if ($taxes_result->num_rows > 0) {
    $has_data = true;
    createExcelTable(
        'TAXES & LICENSES',
        ['Date', 'Reference #', 'Tax Type', 'Mode of Payment', 'Government Agency', 'Amount', 'Remarks', 'Submitted Date'],
        $taxes_result
    );
}

// If no data at all
if (!$has_data) {
    echo '<h3>No data found for export.</h3>';
}

exit();
?>