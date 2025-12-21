<?php
require_once 'config.php';
requireAdmin();

// Set headers for Excel file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="mga_all_data_' . date('Y-m-d_H-i-s') . '.xls"');

echo '<html><meta charset="UTF-8"><body>';

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
            foreach($headers as $key => $header) {
                $value = isset($row[$key]) ? $row[$key] : '';
                echo '<td style="padding: 6px; border: 1px solid #ddd;">' . htmlspecialchars($value) . '</td>';
            }
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="' . count($headers) . '" style="text-align: center; padding: 10px;">No data found</td></tr>';
    }
    
    echo '</table><br>';
}

// 1. Users Data
$users_query = $conn->query("SELECT 
    id AS 'User ID',
    username AS 'Username',
    email AS 'Email',
    user_type AS 'User Type',
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS 'Created Date'
    FROM users ORDER BY created_at DESC");

createExcelTable('USERS LIST', ['User ID', 'Username', 'Email', 'User Type', 'Created Date'], $users_query);

// 2. All Company Information
$info_query = $conn->query("SELECT 
    i.id AS 'Info ID',
    u.username AS 'Username',
    i.company_name AS 'Company Name',
    i.address AS 'Address',
    i.tin_number AS 'TIN Number',
    CONCAT(i.month, ' ', i.year) AS 'Period',
    i.authorized_employee AS 'Authorized Employee',
    i.contact_number AS 'Contact Number',
    i.email AS 'Email',
    DATE_FORMAT(i.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM information i 
    JOIN users u ON i.user_id = u.id 
    ORDER BY i.submitted_at DESC");

createExcelTable('ALL COMPANY INFORMATION', ['Info ID', 'Username', 'Company Name', 'Address', 'TIN Number', 'Period', 'Authorized Employee', 'Contact Number', 'Email', 'Submitted Date'], $info_query);

// 3. Vatable Sales (All Users)
$sales_query = $conn->query("SELECT 
    vs.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    vs.description AS 'Description',
    CONCAT('₱', FORMAT(vs.amount, 2)) AS 'Amount',
    DATE_FORMAT(vs.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(vs.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_sales vs 
    JOIN users u ON vs.user_id = u.id 
    LEFT JOIN information i ON vs.info_id = i.id 
    ORDER BY vs.date DESC");

createExcelTable('ALL VATABLE SALES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $sales_query);

// 4. Non-VAT Sales (All Users)
$non_vat_sales_query = $conn->query("SELECT 
    nvs.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    nvs.description AS 'Description',
    CONCAT('₱', FORMAT(nvs.amount, 2)) AS 'Amount',
    DATE_FORMAT(nvs.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(nvs.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_sales nvs 
    JOIN users u ON nvs.user_id = u.id 
    LEFT JOIN information i ON nvs.info_id = i.id 
    ORDER BY nvs.date DESC");

createExcelTable('ALL NON-VAT SALES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $non_vat_sales_query);

// 5. Vatable Purchases (All Users)
$vat_purchases_query = $conn->query("SELECT 
    vp.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    vp.description AS 'Description',
    CONCAT('₱', FORMAT(vp.amount, 2)) AS 'Amount',
    DATE_FORMAT(vp.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(vp.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_purchases vp 
    JOIN users u ON vp.user_id = u.id 
    LEFT JOIN information i ON vp.info_id = i.id 
    ORDER BY vp.date DESC");

createExcelTable('ALL VATABLE PURCHASES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $vat_purchases_query);

// 6. Non-VAT Purchases (All Users)
$non_vat_purchases_query = $conn->query("SELECT 
    nvp.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    nvp.description AS 'Description',
    CONCAT('₱', FORMAT(nvp.amount, 2)) AS 'Amount',
    DATE_FORMAT(nvp.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(nvp.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_purchases nvp 
    JOIN users u ON nvp.user_id = u.id 
    LEFT JOIN information i ON nvp.info_id = i.id 
    ORDER BY nvp.date DESC");

createExcelTable('ALL NON-VAT PURCHASES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $non_vat_purchases_query);

// 7. Vatable Expenses (All Users)
$vat_expenses_query = $conn->query("SELECT 
    ve.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    ve.description AS 'Description',
    CONCAT('₱', FORMAT(ve.amount, 2)) AS 'Amount',
    DATE_FORMAT(ve.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(ve.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM vatable_expenses ve 
    JOIN users u ON ve.user_id = u.id 
    LEFT JOIN information i ON ve.info_id = i.id 
    ORDER BY ve.date DESC");

createExcelTable('ALL VATABLE EXPENSES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $vat_expenses_query);

// 8. Non-VAT Expenses (All Users)
$non_vat_expenses_query = $conn->query("SELECT 
    nve.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    nve.description AS 'Description',
    CONCAT('₱', FORMAT(nve.amount, 2)) AS 'Amount',
    DATE_FORMAT(nve.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(nve.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM non_vat_expenses nve 
    JOIN users u ON nve.user_id = u.id 
    LEFT JOIN information i ON nve.info_id = i.id 
    ORDER BY nve.date DESC");

createExcelTable('ALL NON-VAT EXPENSES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $non_vat_expenses_query);

// 9. CAPEX (All Users)
$capex_query = $conn->query("SELECT 
    c.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    c.description AS 'Description',
    CONCAT('₱', FORMAT(c.amount, 2)) AS 'Amount',
    DATE_FORMAT(c.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(c.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM capex c 
    JOIN users u ON c.user_id = u.id 
    LEFT JOIN information i ON c.info_id = i.id 
    ORDER BY c.date DESC");

createExcelTable('ALL CAPEX', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $capex_query);

// 10. Taxes & Licenses (All Users)
$taxes_query = $conn->query("SELECT 
    tl.id AS 'ID',
    u.username AS 'Username',
    i.company_name AS 'Company',
    tl.description AS 'Description',
    CONCAT('₱', FORMAT(tl.amount, 2)) AS 'Amount',
    DATE_FORMAT(tl.date, '%Y-%m-%d') AS 'Date',
    DATE_FORMAT(tl.submitted_at, '%Y-%m-%d %H:%i') AS 'Submitted Date'
    FROM taxes_licenses tl 
    JOIN users u ON tl.user_id = u.id 
    LEFT JOIN information i ON tl.info_id = i.id 
    ORDER BY tl.date DESC");

createExcelTable('ALL TAXES & LICENSES', ['ID', 'Username', 'Company', 'Description', 'Amount', 'Date', 'Submitted Date'], $taxes_query);

echo '</body></html>';
exit();
?>