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

$record = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Different update logic for different tables
    switch($type) {
        case 'information':
            $company_name = $_POST['company_name'];
            $address = $_POST['address'];
            $tin_number = $_POST['tin_number'];
            $month = $_POST['month'];
            $year = $_POST['year'];
            $authorized_employee = $_POST['authorized_employee'] ?? '';
            $contact_number = $_POST['contact_number'] ?? '';
            $email = $_POST['email'] ?? '';
            
            $stmt = $conn->prepare("UPDATE information SET company_name = ?, address = ?, tin_number = ?, month = ?, year = ?, authorized_employee = ?, contact_number = ?, email = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssssssii", $company_name, $address, $tin_number, $month, $year, $authorized_employee, $contact_number, $email, $id, $user_id);
            break;
            
        case 'vatable_sales':
            $date = $_POST['date'];
            $invoice_number = $_POST['invoice_number'] ?? '';
            $invoice_type = $_POST['invoice_type'] ?? '';
            $mode_of_payment = $_POST['mode_of_payment'] ?? '';
            $particulars = $_POST['particulars'] ?? '';
            $address = $_POST['address'] ?? '';
            $tin_number = $_POST['tin_number'] ?? '';
            $vatable_gross_sales = $_POST['vatable_gross_sales'] ?? 0;
            $net_sales = $_POST['net_sales'] ?? 0;
            $output_tax = $_POST['output_tax'] ?? 0;
            $gross_sales_type = $_POST['gross_sales_type'] ?? '';
            $exempt_sales = $_POST['exempt_sales'] ?? 0;
            $withholding_tax_vat = $_POST['withholding_tax_vat'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            
            $stmt = $conn->prepare("UPDATE vatable_sales SET date = ?, invoice_number = ?, invoice_type = ?, mode_of_payment = ?, particulars = ?, address = ?, tin_number = ?, vatable_gross_sales = ?, net_sales = ?, output_tax = ?, gross_sales_type = ?, exempt_sales = ?, withholding_tax_vat = ?, remarks = ? WHERE id = ? AND user_id = ?");
            
            // Debug log
            error_log("VAT Sales Update - Params: date=$date, invoice=$invoice_number, type=$invoice_type, payment=$mode_of_payment");
            error_log("particulars=$particulars, address=$address, tin=$tin_number, gross=$vatable_gross_sales");
            error_log("net=$net_sales, output=$output_tax, sales_type=$gross_sales_type, exempt=$exempt_sales");
            error_log("withholding=$withholding_tax_vat, remarks=$remarks, id=$id, user=$user_id");
            
            // 11 strings + 3 decimals + 2 integers = 16 parameters
            $stmt->bind_param("sssssssdddssssii", 
                $date, $invoice_number, $invoice_type, $mode_of_payment, $particulars, 
                $address, $tin_number, $vatable_gross_sales, $net_sales, $output_tax, 
                $gross_sales_type, $exempt_sales, $withholding_tax_vat, $remarks, 
                $id, $user_id
            );
            break;
            
        case 'non_vat_sales':
    $date = $_POST['date'];
    $invoice_number = $_POST['invoice_number'] ?? '';
    $invoice_type = $_POST['invoice_type'] ?? '';
    $mode_of_payment = $_POST['mode_of_payment'] ?? '';
    $particulars = $_POST['particulars'] ?? '';
    $address = $_POST['address'] ?? '';
    $tin_number = $_POST['tin_number'] ?? '';
    $vatable_gross_sales = $_POST['vatable_gross_sales'] ?? 0;
    $exempt_sales = $_POST['exempt_sales'] ?? 0;
    $withholding_tax_vat = $_POST['withholding_tax_vat'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    
    $stmt = $conn->prepare("UPDATE non_vat_sales SET date = ?, invoice_number = ?, invoice_type = ?, mode_of_payment = ?, particulars = ?, address = ?, tin_number = ?, vatable_gross_sales = ?, exempt_sales = ?, withholding_tax_vat = ?, remarks = ? WHERE id = ? AND user_id = ?");
    
    // Debug log
    error_log("Non-VAT Sales Update - Params: date=$date, invoice=$invoice_number, type=$invoice_type, payment=$mode_of_payment");
    error_log("particulars=$particulars, address=$address, tin=$tin_number, gross=$vatable_gross_sales");
    error_log("exempt=$exempt_sales, withholding=$withholding_tax_vat, remarks=$remarks, id=$id, user=$user_id");
    
    // 9 strings + 2 decimals + 2 integers = 13 parameters
    $stmt->bind_param("sssssssdsssii", 
        $date, $invoice_number, $invoice_type, $mode_of_payment, $particulars, 
        $address, $tin_number, $vatable_gross_sales, $exempt_sales, 
        $withholding_tax_vat, $remarks, $id, $user_id
    );
    break;
            
        case 'vatable_purchases':
            $date = $_POST['date'];
            $invoice_number = $_POST['invoice_number'] ?? '';
            $invoice_type = $_POST['invoice_type'] ?? '';
            $mode_of_payment = $_POST['mode_of_payment'] ?? '';
            $particulars = $_POST['particulars'] ?? '';
            $address = $_POST['address'] ?? '';
            $tin_number = $_POST['tin_number'] ?? '';
            $vatable_gross_purchases = $_POST['vatable_gross_purchases'] ?? 0;
            $net_purchases = $_POST['net_purchases'] ?? 0;
            $input_tax = $_POST['input_tax'] ?? 0;
            $withholding_tax_rate = $_POST['withholding_tax_rate'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            
            $stmt = $conn->prepare("UPDATE vatable_purchases SET date = ?, invoice_number = ?, invoice_type = ?, mode_of_payment = ?, particulars = ?, address = ?, tin_number = ?, vatable_gross_purchases = ?, net_purchases = ?, input_tax = ?, withholding_tax_rate = ?, remarks = ? WHERE id = ? AND user_id = ?");
            // 9 strings + 3 decimals + 2 integers = 14 parameters
            $stmt->bind_param("sssssssdddssii", 
                $date, $invoice_number, $invoice_type, $mode_of_payment, $particulars, 
                $address, $tin_number, $vatable_gross_purchases, $net_purchases, 
                $input_tax, $withholding_tax_rate, $remarks, $id, $user_id
            );
            break;
            
        case 'non_vat_purchases':
            $date = $_POST['date'];
            $invoice_number = $_POST['invoice_number'] ?? '';
            $invoice_type = $_POST['invoice_type'] ?? '';
            $mode_of_payment = $_POST['mode_of_payment'] ?? '';
            $particulars = $_POST['particulars'] ?? '';
            $address = $_POST['address'] ?? '';
            $tin_number = $_POST['tin_number'] ?? '';
            $vatable_gross_purchases = $_POST['vatable_gross_purchases'] ?? 0;
            $withholding_tax_rate = $_POST['withholding_tax_rate'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            
            $stmt = $conn->prepare("UPDATE non_vat_purchases SET date = ?, invoice_number = ?, invoice_type = ?, mode_of_payment = ?, particulars = ?, address = ?, tin_number = ?, vatable_gross_purchases = ?, withholding_tax_rate = ?, remarks = ? WHERE id = ? AND user_id = ?");
            // 9 strings + 1 decimal + 2 integers = 12 parameters
            $stmt->bind_param("sssssssdssii", 
                $date, $invoice_number, $invoice_type, $mode_of_payment, $particulars, 
                $address, $tin_number, $vatable_gross_purchases, $withholding_tax_rate, 
                $remarks, $id, $user_id
            );
            break;
            
        case 'vatable_expenses':
            $date = $_POST['date'];
            $invoice_number = $_POST['invoice_number'] ?? '';
            $invoice_type = $_POST['invoice_type'] ?? '';
            $transaction_type = $_POST['transaction_type'] ?? '';
            $mode_of_payment = $_POST['mode_of_payment'] ?? '';
            $particulars = $_POST['particulars'] ?? '';
            $address = $_POST['address'] ?? '';
            $tin_number = $_POST['tin_number'] ?? '';
            $gross_amount = $_POST['gross_amount'] ?? 0;
            $net_amount = $_POST['net_amount'] ?? 0;
            $input_tax = $_POST['input_tax'] ?? 0;
            $nature_of_expense = $_POST['nature_of_expense'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            
            $stmt = $conn->prepare("UPDATE vatable_expenses SET date = ?, invoice_number = ?, invoice_type = ?, transaction_type = ?, mode_of_payment = ?, particulars = ?, address = ?, tin_number = ?, gross_amount = ?, net_amount = ?, input_tax = ?, nature_of_expense = ?, remarks = ? WHERE id = ? AND user_id = ?");
            // 10 strings + 3 decimals + 2 integers = 15 parameters
            $stmt->bind_param("ssssssssdddssii", 
                $date, $invoice_number, $invoice_type, $transaction_type,
                $mode_of_payment, $particulars, $address, $tin_number, 
                $gross_amount, $net_amount, $input_tax, 
                $nature_of_expense, $remarks, $id, $user_id
            );
            break;
            
        case 'non_vat_expenses':
            $date = $_POST['date'];
            $invoice_number = $_POST['invoice_number'] ?? '';
            $invoice_type = $_POST['invoice_type'] ?? '';
            $transaction_type = $_POST['transaction_type'] ?? '';
            $mode_of_payment = $_POST['mode_of_payment'] ?? '';
            $particulars = $_POST['particulars'] ?? '';
            $address = $_POST['address'] ?? '';
            $tin_number = $_POST['tin_number'] ?? '';
            $gross_amount = $_POST['gross_amount'] ?? 0;
            $nature_of_expense = $_POST['nature_of_expense'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            
            $stmt = $conn->prepare("UPDATE non_vat_expenses SET date = ?, invoice_number = ?, invoice_type = ?, transaction_type = ?, mode_of_payment = ?, particulars = ?, address = ?, tin_number = ?, gross_amount = ?, nature_of_expense = ?, remarks = ? WHERE id = ? AND user_id = ?");
            // 10 strings + 1 decimal + 2 integers = 13 parameters
            $stmt->bind_param("ssssssssdssii", 
                $date, $invoice_number, $invoice_type, $transaction_type,
                $mode_of_payment, $particulars, $address, $tin_number, 
                $gross_amount, $nature_of_expense, $remarks, $id, $user_id
            );
            break;
            
        case 'capex':
    $date = $_POST['date'];
    $invoice_number = $_POST['invoice_number'] ?? '';
    $invoice_type = $_POST['invoice_type'] ?? '';
    $mode_of_payment = $_POST['mode_of_payment'] ?? '';
    $particulars = $_POST['particulars'] ?? '';
    $asset_description = $_POST['asset_description'] ?? '';
    $address = $_POST['address'] ?? '';
    $tin_number = $_POST['tin_number'] ?? '';
    $gross_purchase_non_vat = $_POST['gross_purchase_non_vat'] ?? 0;
    $gross_purchase_vatable = $_POST['gross_purchase_vatable'] ?? 0;
    $net_vatable_purchase = $_POST['net_vatable_purchase'] ?? 0;
    $input_tax = $_POST['input_tax'] ?? 0;
    $withholding_tax = $_POST['withholding_tax'] ?? 0;
    $remarks = $_POST['remarks'] ?? '';
    
    $stmt = $conn->prepare("UPDATE capex SET date = ?, invoice_number = ?, invoice_type = ?, mode_of_payment = ?, particulars = ?, asset_description = ?, address = ?, tin_number = ?, gross_purchase_non_vat = ?, gross_purchase_vatable = ?, net_vatable_purchase = ?, input_tax = ?, withholding_tax = ?, remarks = ? WHERE id = ? AND user_id = ?");
    
    // 9 strings + 5 decimals + 2 integers = 16 parameters
    $stmt->bind_param("sssssssssdddddii", 
        $date, $invoice_number, $invoice_type, $mode_of_payment, 
        $particulars, $asset_description, $address, $tin_number, 
        $gross_purchase_non_vat, $gross_purchase_vatable, $net_vatable_purchase, $input_tax,
        $withholding_tax, $remarks, $id, $user_id
    );
    break;
            
        case 'taxes_licenses':
    $date = $_POST['date'];
    $reference_number = $_POST['reference_number'] ?? '';
    $tax_type = $_POST['tax_type'] ?? '';
    $mode_of_payment = $_POST['mode_of_payment'] ?? '';
    $government_agency = $_POST['government_agency'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $remarks = $_POST['remarks'] ?? '';
    
    $stmt = $conn->prepare("UPDATE taxes_licenses SET date = ?, reference_number = ?, tax_type = ?, mode_of_payment = ?, government_agency = ?, amount = ?, remarks = ? WHERE id = ? AND user_id = ?");
    
    // Debug log
    error_log("Taxes & Licenses Update - Params: date=$date, ref=$reference_number, type=$tax_type, payment=$mode_of_payment");
    error_log("agency=$government_agency, amount=$amount, remarks=$remarks, id=$id, user=$user_id");
    
    // 6 strings + 1 decimal + 2 integers = 9 parameters
    $stmt->bind_param("sssssdsii", 
        $date, $reference_number, $tax_type, $mode_of_payment, 
        $government_agency, $amount, $remarks, $id, $user_id
    );
    break;
    }
    
    if (isset($stmt) && $stmt->execute()) {
        $success = "Record updated successfully!";
        // Refresh the record data
        $result = $conn->query("SELECT * FROM $type WHERE id = $id AND user_id = $user_id");
        $record = $result->fetch_assoc();
    } else {
        $error = "Error updating record. Please try again.";
    }
}

// Get page title based on type
$page_titles = [
    'information' => 'Company Information',
    'vatable_sales' => 'Vatable Sales',
    'non_vat_sales' => 'Non-VAT Sales',
    'vatable_purchases' => 'Vatable Purchases',
    'non_vat_purchases' => 'Non-VAT Purchases',
    'vatable_expenses' => 'Vatable Expenses',
    'non_vat_expenses' => 'Non-VAT Expenses',
    'capex' => 'CAPEX',
    'taxes_licenses' => 'Taxes & Licenses'
];

$page_title = $page_titles[$type] ?? 'Edit Record';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo $page_title; ?> - MGA&A Encoding App</title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
        }
        
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
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
        
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            color: #bbdefb;
        }
        
        .required::after {
            content: " *";
            color: #f44336;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        /* Allow decimal point input */
        input[type="text"][inputmode="decimal"] {
            -moz-appearance: textfield;
            font-family: 'Courier New', monospace;
        }
        
        input[type="text"][inputmode="decimal"]::-webkit-outer-spin-button,
        input[type="text"][inputmode="decimal"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        select {
            background: rgba(0, 0, 0, 0.5);
        }
        
        select option {
            background: #1a237e;
            color: white;
            padding: 10px;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #2196f3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.3);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            padding: 15px 40px;
            border-radius: 25px;
            border: none;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0d47a1;
            transform: scale(1.05);
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
            margin-right: 15px;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #1a237e;
        }
        
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e65100;
            transform: scale(1.05);
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background: rgba(76, 175, 80, 0.3);
            border: 1px solid #4caf50;
        }
        
        .error {
            background: rgba(244, 67, 54, 0.3);
            border: 1px solid #f44336;
        }
        
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        
        .section-title {
            color: #90caf9;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(144, 202, 249, 0.3);
            font-size: 1.3rem;
        }
        
        .input-hint {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
            font-style: italic;
        }
        
        .amount-input-group {
            margin-bottom: 15px;
        }
        
        .currency-symbol {
            position: absolute;
            margin-left: 10px;
            margin-top: 15px;
            color: #4caf50;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .input-with-currency {
            position: relative;
        }
        
        .input-with-currency input {
            padding-left: 35px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .radio-option:hover {
            background: rgba(33, 150, 243, 0.2);
        }
        
        .radio-option input[type="radio"] {
            width: auto;
            margin-right: 5px;
            cursor: pointer;
        }
        
        .radio-option label {
            margin-bottom: 0;
            cursor: pointer;
        }
        
        .calculation-section {
            background: rgba(33, 150, 243, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }
        
        .calculation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .calculation-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 8px;
        }
        
        .calculation-label {
            font-size: 0.9rem;
            color: #bbdefb;
            margin-bottom: 5px;
        }
        
        .calculation-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #4caf50;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="my_submissions.php" class="back-btn">← Back to My Submissions</a>
        
        <header>
            <h1>Edit <?php echo $page_title; ?></h1>
            <p>Update your encoded data</p>
        </header>
        
        <div class="form-container">
            <?php if (isset($success)): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="editForm" onsubmit="return validateForm()">
                
                <?php if ($type == 'information'): ?>
                <!-- Company Information Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="company_name" class="required">Company Name</label>
                        <input type="text" id="company_name" name="company_name" required 
                               value="<?php echo htmlspecialchars($record['company_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tin_number" class="required">12-digit TIN Number</label>
                        <input type="text" id="tin_number" name="tin_number" required 
                               pattern="[0-9]{12}"
                               maxlength="12"
                               value="<?php echo $record['tin_number']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address" class="required">Address</label>
                    <textarea id="address" name="address" required><?php echo htmlspecialchars($record['address']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="month" class="required">Month</label>
                        <select id="month" name="month" required>
                            <option value="">Select Month</option>
                            <?php
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                     'July', 'August', 'September', 'October', 'November', 'December'];
                            foreach ($months as $month): ?>
                            <option value="<?php echo $month; ?>" <?php echo $record['month'] == $month ? 'selected' : ''; ?>>
                                <?php echo $month; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="year" class="required">Year</label>
                        <input type="number" id="year" name="year" required 
                               min="2000" max="2030" value="<?php echo $record['year']; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="authorized_employee">Authorized Employee</label>
                        <input type="text" id="authorized_employee" name="authorized_employee"
                               value="<?php echo htmlspecialchars($record['authorized_employee']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number"
                               value="<?php echo $record['contact_number']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo $record['email']; ?>">
                </div>
                
                <?php elseif ($type == 'vatable_sales'): ?>
                <!-- Vatable Sales Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number"
                               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_type">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type">
                            <option value="">Select Invoice Type</option>
                            <option value="Cash" <?php echo $record['invoice_type'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['invoice_type'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                            <option value="Billing" <?php echo $record['invoice_type'] == 'Billing' ? 'selected' : ''; ?>>Billing</option>
                            <option value="Service" <?php echo $record['invoice_type'] == 'Service' ? 'selected' : ''; ?>>Service</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="section-title">Customer Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="particulars">Particulars / Customer Name</label>
                        <input type="text" id="particulars" name="particulars"
                               value="<?php echo htmlspecialchars($record['particulars']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tin_number">12-Digit TIN Number</label>
                    <input type="text" id="tin_number" name="tin_number" 
                           pattern="[0-9]{12}"
                           maxlength="12"
                           value="<?php echo $record['tin_number']; ?>">
                </div>
                
                <h3 class="section-title">Sales Amounts</h3>
                <div class="form-row">
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="vatable_gross_sales" class="required">Vatable Gross Sales</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="vatable_gross_sales" name="vatable_gross_sales" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['vatable_gross_sales'], 2); ?>"
                                       oninput="calculateVatSales()"
                                       required>
                            </div>
                        </div>
                        
                        <label style="margin-top: 15px; display: block;">Sales Type:</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="gross_sales_private" name="gross_sales_type" value="Private"
                                    <?php echo $record['gross_sales_type'] == 'Private' ? 'checked' : ''; ?>>
                                <label for="gross_sales_private" style="display: inline;">Private</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="gross_sales_government" name="gross_sales_type" value="Government"
                                    <?php echo $record['gross_sales_type'] == 'Government' ? 'checked' : ''; ?>>
                                <label for="gross_sales_government" style="display: inline;">Government</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="exempt_sales">Exempt Sales</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="exempt_sales" name="exempt_sales" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['exempt_sales'], 2); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- VAT Calculation Section -->
                <div class="calculation-section">
                    <h4 style="color: #90caf9; margin-bottom: 15px;">VAT Calculation</h4>
                    <div class="calculation-grid">
                        <div class="calculation-item">
                            <div class="calculation-label">Net Sales (Vatable Gross Sales ÷ 1.12)</div>
                            <div class="calculation-value" id="net_sales_display">₱<?php echo number_format($record['net_sales'], 2); ?></div>
                            <input type="hidden" id="net_sales" name="net_sales" value="<?php echo number_format($record['net_sales'], 2); ?>">
                        </div>
                        <div class="calculation-item">
                            <div class="calculation-label">Output Tax (12% of Net Sales)</div>
                            <div class="calculation-value" id="output_tax_display">₱<?php echo number_format($record['output_tax'], 2); ?></div>
                            <input type="hidden" id="output_tax" name="output_tax" value="<?php echo number_format($record['output_tax'], 2); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="withholding_tax_vat">Withholding Tax VAT</label>
                    <select id="withholding_tax_vat" name="withholding_tax_vat">
                        <option value="">Select Withholding Tax Rate</option>
                        <option value="1%" <?php echo $record['withholding_tax_vat'] == '1%' ? 'selected' : ''; ?>>1%</option>
                        <option value="2%" <?php echo $record['withholding_tax_vat'] == '2%' ? 'selected' : ''; ?>>2%</option>
                        <option value="5%" <?php echo $record['withholding_tax_vat'] == '5%' ? 'selected' : ''; ?>>5%</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php elseif ($type == 'non_vat_sales'): ?>
                <!-- Non-VAT Sales Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number"
                               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_type">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type">
                            <option value="">Select Invoice Type</option>
                            <option value="Cash" <?php echo $record['invoice_type'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['invoice_type'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                            <option value="Billing" <?php echo $record['invoice_type'] == 'Billing' ? 'selected' : ''; ?>>Billing</option>
                            <option value="Service" <?php echo $record['invoice_type'] == 'Service' ? 'selected' : ''; ?>>Service</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="section-title">Customer Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="particulars">Particulars / Customer Name</label>
                        <input type="text" id="particulars" name="particulars"
                               value="<?php echo htmlspecialchars($record['particulars']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tin_number">12-Digit TIN Number</label>
                    <input type="text" id="tin_number" name="tin_number" 
                           pattern="[0-9]{12}"
                           maxlength="12"
                           value="<?php echo $record['tin_number']; ?>">
                </div>
                
                <h3 class="section-title">Sales Amounts</h3>
                <div class="form-row">
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="vatable_gross_sales" class="required">Vatable Gross Sales</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="vatable_gross_sales" name="vatable_gross_sales" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['vatable_gross_sales'], 2); ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="exempt_sales">Exempt Sales</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="exempt_sales" name="exempt_sales" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['exempt_sales'], 2); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="withholding_tax_vat">Withholding Tax VAT</label>
                    <select id="withholding_tax_vat" name="withholding_tax_vat">
                        <option value="">Select Withholding Tax Rate</option>
                        <option value="1%" <?php echo $record['withholding_tax_vat'] == '1%' ? 'selected' : ''; ?>>1%</option>
                        <option value="2%" <?php echo $record['withholding_tax_vat'] == '2%' ? 'selected' : ''; ?>>2%</option>
                        <option value="5%" <?php echo $record['withholding_tax_vat'] == '5%' ? 'selected' : ''; ?>>5%</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php elseif ($type == 'vatable_purchases'): ?>
                <!-- Vatable Purchases Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number"
                               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_type">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type">
                            <option value="">Select Invoice Type</option>
                            <option value="OR" <?php echo $record['invoice_type'] == 'OR' ? 'selected' : ''; ?>>OR</option>
                            <option value="SI" <?php echo $record['invoice_type'] == 'SI' ? 'selected' : ''; ?>>SI</option>
                            <option value="DR" <?php echo $record['invoice_type'] == 'DR' ? 'selected' : ''; ?>>DR</option>
                            <option value="AR" <?php echo $record['invoice_type'] == 'AR' ? 'selected' : ''; ?>>AR</option>
                            <option value="TR" <?php echo $record['invoice_type'] == 'TR' ? 'selected' : ''; ?>>TR</option>
                            <option value="CR" <?php echo $record['invoice_type'] == 'CR' ? 'selected' : ''; ?>>CR</option>
                            <option value="OTHERS" <?php echo $record['invoice_type'] == 'OTHERS' ? 'selected' : ''; ?>>OTHERS</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="section-title">Supplier Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="particulars">Particulars / Supplier Name</label>
                        <input type="text" id="particulars" name="particulars"
                               value="<?php echo htmlspecialchars($record['particulars']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tin_number">TIN Number</label>
                    <input type="text" id="tin_number" name="tin_number" 
                           pattern="[0-9]{12}"
                           maxlength="12"
                           value="<?php echo $record['tin_number']; ?>">
                </div>
                
                <h3 class="section-title">Purchase Amounts</h3>
                <div class="form-row">
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="vatable_gross_purchases" class="required">Vatable Gross Purchases</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="vatable_gross_purchases" name="vatable_gross_purchases" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['vatable_gross_purchases'], 2); ?>"
                                       oninput="calculateVatPurchases()"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="withholding_tax_rate">Withholding Tax Rate</label>
                        <select id="withholding_tax_rate" name="withholding_tax_rate">
                            <option value="">Select Withholding Tax Rate</option>
                            <option value="1%" <?php echo $record['withholding_tax_rate'] == '1%' ? 'selected' : ''; ?>>1%</option>
                            <option value="2%" <?php echo $record['withholding_tax_rate'] == '2%' ? 'selected' : ''; ?>>2%</option>
                            <option value="5%" <?php echo $record['withholding_tax_rate'] == '5%' ? 'selected' : ''; ?>>5%</option>
                        </select>
                    </div>
                </div>
                
                <!-- VAT Calculation Section -->
                <div class="calculation-section">
                    <h4 style="color: #90caf9; margin-bottom: 15px;">VAT Calculation</h4>
                    <div class="calculation-grid">
                        <div class="calculation-item">
                            <div class="calculation-label">Net Purchases (Vatable Gross ÷ 1.12)</div>
                            <div class="calculation-value" id="net_purchases_display">₱<?php echo number_format($record['net_purchases'], 2); ?></div>
                            <input type="hidden" id="net_purchases" name="net_purchases" value="<?php echo number_format($record['net_purchases'], 2); ?>">
                        </div>
                        <div class="calculation-item">
                            <div class="calculation-label">Input Tax (12% of Net Purchases)</div>
                            <div class="calculation-value" id="input_tax_display">₱<?php echo number_format($record['input_tax'], 2); ?></div>
                            <input type="hidden" id="input_tax" name="input_tax" value="<?php echo number_format($record['input_tax'], 2); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php elseif ($type == 'non_vat_purchases'): ?>
                <!-- Non-VAT Purchases Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number"
                               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_type">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type">
                            <option value="">Select Invoice Type</option>
                            <option value="OR" <?php echo $record['invoice_type'] == 'OR' ? 'selected' : ''; ?>>OR</option>
                            <option value="SI" <?php echo $record['invoice_type'] == 'SI' ? 'selected' : ''; ?>>SI</option>
                            <option value="DR" <?php echo $record['invoice_type'] == 'DR' ? 'selected' : ''; ?>>DR</option>
                            <option value="AR" <?php echo $record['invoice_type'] == 'AR' ? 'selected' : ''; ?>>AR</option>
                            <option value="TR" <?php echo $record['invoice_type'] == 'TR' ? 'selected' : ''; ?>>TR</option>
                            <option value="CR" <?php echo $record['invoice_type'] == 'CR' ? 'selected' : ''; ?>>CR</option>
                            <option value="OTHERS" <?php echo $record['invoice_type'] == 'OTHERS' ? 'selected' : ''; ?>>OTHERS</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="section-title">Supplier Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="particulars">Particulars / Supplier Name</label>
                        <input type="text" id="particulars" name="particulars"
                               value="<?php echo htmlspecialchars($record['particulars']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tin_number">TIN Number</label>
                    <input type="text" id="tin_number" name="tin_number" 
                           pattern="[0-9]{12}"
                           maxlength="12"
                           value="<?php echo $record['tin_number']; ?>">
                </div>
                
                <h3 class="section-title">Purchase Amounts</h3>
                <div class="form-row">
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="vatable_gross_purchases" class="required">Non-Vatable Gross Purchases</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="vatable_gross_purchases" name="vatable_gross_purchases" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['vatable_gross_purchases'], 2); ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="withholding_tax_rate">Withholding Tax Rate</label>
                        <select id="withholding_tax_rate" name="withholding_tax_rate">
                            <option value="">Select Withholding Tax Rate</option>
                            <option value="1%" <?php echo $record['withholding_tax_rate'] == '1%' ? 'selected' : ''; ?>>1%</option>
                            <option value="2%" <?php echo $record['withholding_tax_rate'] == '2%' ? 'selected' : ''; ?>>2%</option>
                            <option value="5%" <?php echo $record['withholding_tax_rate'] == '5%' ? 'selected' : ''; ?>>5%</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php elseif ($type == 'vatable_expenses'): ?>
                <!-- Vatable Expenses Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number"
                               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_type">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type">
                            <option value="">Select Invoice Type</option>
                            <option value="OR" <?php echo $record['invoice_type'] == 'OR' ? 'selected' : ''; ?>>OR</option>
                            <option value="SI" <?php echo $record['invoice_type'] == 'SI' ? 'selected' : ''; ?>>SI</option>
                            <option value="DR" <?php echo $record['invoice_type'] == 'DR' ? 'selected' : ''; ?>>DR</option>
                            <option value="AR" <?php echo $record['invoice_type'] == 'AR' ? 'selected' : ''; ?>>AR</option>
                            <option value="TR" <?php echo $record['invoice_type'] == 'TR' ? 'selected' : ''; ?>>TR</option>
                            <option value="CR" <?php echo $record['invoice_type'] == 'CR' ? 'selected' : ''; ?>>CR</option>
                            <option value="OTHERS" <?php echo $record['invoice_type'] == 'OTHERS' ? 'selected' : ''; ?>>OTHERS</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transaction_type">Transaction Type</label>
                        <select id="transaction_type" name="transaction_type">
                            <option value="">Select Transaction Type</option>
                            <option value="Goods" <?php echo $record['transaction_type'] == 'Goods' ? 'selected' : ''; ?>>Goods</option>
                            <option value="Services" <?php echo $record['transaction_type'] == 'Services' ? 'selected' : ''; ?>>Services</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="section-title">Supplier Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="particulars" class="required">Particulars / Supplier Name</label>
                        <input type="text" id="particulars" name="particulars" required
                               value="<?php echo htmlspecialchars($record['particulars']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tin_number">12-Digit TIN Number</label>
                    <input type="text" id="tin_number" name="tin_number" 
                           pattern="[0-9]{12}"
                           maxlength="12"
                           value="<?php echo $record['tin_number']; ?>">
                </div>
                
                <h3 class="section-title">Expense Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="gross_amount" class="required">Gross Amount</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="gross_amount" name="gross_amount" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['gross_amount'], 2); ?>"
                                       oninput="calculateVatExpenses()"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nature_of_expense" class="required">Nature of Expense</label>
                        <select id="nature_of_expense" name="nature_of_expense" required>
                            <option value="">Select Nature of Expense</option>
                            <?php
                            $expense_types = [
                                'REPAIRS AND MAINTENANCE-LABOR', 'REPAIRS AND MAINTENANCE-MATERIALS',
                                'MEALS AND ACCOMMODATION - SERVICES', 'MEALS AND ACCOMMODATION - GOODS',
                                'REPRESENTATIONS - SERVICES', 'REPRESENTATIONS - GOODS',
                                'OTHERS - SERVICES', 'OTHERS - GOODS', 'MEDICAL EXPENSES - SERVICES',
                                'MEDICAL EXPENSES - GOODS', 'CLEANING SERVICES', 'CLEANING SUPPLIES',
                                'ELECTRICITY AND WATER', 'COMMUNICATION EXPENSE', 'FREIGHT AND COURIER',
                                'TRAVEL AND TRANSPORTATION', 'ADVERTISING AND MARKETING', 'SECURITY SERVICES',
                                'INSURANCE', 'SEMINAR AND TRAINING', 'PROFESSIONAL FEES', 'FUEL AND OIL',
                                'OFFICE SUPPLIES', 'UNIFORMS', 'Rental'
                            ];
                            foreach ($expense_types as $exp_type): ?>
                            <option value="<?php echo $exp_type; ?>" <?php echo $record['nature_of_expense'] == $exp_type ? 'selected' : ''; ?>>
                                <?php echo $exp_type; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- VAT Calculation Section -->
                <div class="calculation-section">
                    <h4 style="color: #90caf9; margin-bottom: 15px;">VAT Calculation</h4>
                    <div class="calculation-grid">
                        <div class="calculation-item">
                            <div class="calculation-label">Net Expense (Gross Amount ÷ 1.12)</div>
                            <div class="calculation-value" id="net_amount_display">₱<?php echo number_format($record['net_amount'], 2); ?></div>
                            <input type="hidden" id="net_amount" name="net_amount" value="<?php echo number_format($record['net_amount'], 2); ?>">
                        </div>
                        <div class="calculation-item">
                            <div class="calculation-label">Input Tax (12% of Net Expense)</div>
                            <div class="calculation-value" id="input_tax_display">₱<?php echo number_format($record['input_tax'], 2); ?></div>
                            <input type="hidden" id="input_tax" name="input_tax" value="<?php echo number_format($record['input_tax'], 2); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php elseif ($type == 'non_vat_expenses'): ?>
                <!-- Non-VAT Expenses Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="invoice_number"
                               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice_type">Invoice Type</label>
                        <select id="invoice_type" name="invoice_type">
                            <option value="">Select Invoice Type</option>
                            <option value="OR" <?php echo $record['invoice_type'] == 'OR' ? 'selected' : ''; ?>>OR</option>
                            <option value="SI" <?php echo $record['invoice_type'] == 'SI' ? 'selected' : ''; ?>>SI</option>
                            <option value="DR" <?php echo $record['invoice_type'] == 'DR' ? 'selected' : ''; ?>>DR</option>
                            <option value="AR" <?php echo $record['invoice_type'] == 'AR' ? 'selected' : ''; ?>>AR</option>
                            <option value="TR" <?php echo $record['invoice_type'] == 'TR' ? 'selected' : ''; ?>>TR</option>
                            <option value="CR" <?php echo $record['invoice_type'] == 'CR' ? 'selected' : ''; ?>>CR</option>
                            <option value="OTHERS" <?php echo $record['invoice_type'] == 'OTHERS' ? 'selected' : ''; ?>>OTHERS</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transaction_type">Transaction Type</label>
                        <select id="transaction_type" name="transaction_type">
                            <option value="">Select Transaction Type</option>
                            <option value="Goods" <?php echo $record['transaction_type'] == 'Goods' ? 'selected' : ''; ?>>Goods</option>
                            <option value="Services" <?php echo $record['transaction_type'] == 'Services' ? 'selected' : ''; ?>>Services</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
                        </select>
                    </div>
                </div>
                
                <h3 class="section-title">Supplier Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="particulars" class="required">Particulars / Supplier Name</label>
                        <input type="text" id="particulars" name="particulars" required
                               value="<?php echo htmlspecialchars($record['particulars']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="tin_number">12-Digit TIN Number</label>
                    <input type="text" id="tin_number" name="tin_number" 
                           pattern="[0-9]{12}"
                           maxlength="12"
                           value="<?php echo $record['tin_number']; ?>">
                </div>
                
                <h3 class="section-title">Expense Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <div class="amount-input-group">
                            <label for="gross_amount" class="required">Gross Amount</label>
                            <div class="input-with-currency">
                                <span class="currency-symbol">₱</span>
                                <input type="text" id="gross_amount" name="gross_amount" 
                                       inputmode="decimal"
                                       pattern="[0-9]*\.?[0-9]*"
                                       value="<?php echo number_format($record['gross_amount'], 2); ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="nature_of_expense" class="required">Nature of Expense</label>
                        <select id="nature_of_expense" name="nature_of_expense" required>
                            <option value="">Select Nature of Expense</option>
                            <?php
                            $expense_types = [
                                'REPAIRS AND MAINTENANCE-LABOR', 'REPAIRS AND MAINTENANCE-MATERIALS',
                                'MEALS AND ACCOMMODATION - SERVICES', 'MEALS AND ACCOMMODATION - GOODS',
                                'REPRESENTATIONS - SERVICES', 'REPRESENTATIONS - GOODS',
                                'OTHERS - SERVICES', 'OTHERS - GOODS', 'MEDICAL EXPENSES - SERVICES',
                                'MEDICAL EXPENSES - GOODS', 'CLEANING SERVICES', 'CLEANING SUPPLIES',
                                'ELECTRICITY AND WATER', 'COMMUNICATION EXPENSE', 'FREIGHT AND COURIER',
                                'TRAVEL AND TRANSPORTATION', 'ADVERTISING AND MARKETING', 'SECURITY SERVICES',
                                'INSURANCE', 'SEMINAR AND TRAINING', 'PROFESSIONAL FEES', 'FUEL AND OIL',
                                'OFFICE SUPPLIES', 'UNIFORMS', 'Rental'
                            ];
                            foreach ($expense_types as $exp_type): ?>
                            <option value="<?php echo $exp_type; ?>" <?php echo $record['nature_of_expense'] == $exp_type ? 'selected' : ''; ?>>
                                <?php echo $exp_type; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php elseif ($type == 'capex'): ?>
<!-- CAPEX Form -->
<div class="form-row">
    <div class="form-group">
        <label for="date" class="required">Date</label>
        <input type="date" id="date" name="date" required 
               value="<?php echo $record['date']; ?>">
    </div>
    
    <div class="form-group">
        <label for="invoice_number">Invoice Number</label>
        <input type="text" id="invoice_number" name="invoice_number"
               value="<?php echo htmlspecialchars($record['invoice_number']); ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="invoice_type">Invoice Type</label>
        <select id="invoice_type" name="invoice_type">
            <option value="">Select Invoice Type</option>
            <option value="OR" <?php echo $record['invoice_type'] == 'OR' ? 'selected' : ''; ?>>OR</option>
            <option value="SI" <?php echo $record['invoice_type'] == 'SI' ? 'selected' : ''; ?>>SI</option>
            <option value="DR" <?php echo $record['invoice_type'] == 'DR' ? 'selected' : ''; ?>>DR</option>
            <option value="AR" <?php echo $record['invoice_type'] == 'AR' ? 'selected' : ''; ?>>AR</option>
            <option value="TR" <?php echo $record['invoice_type'] == 'TR' ? 'selected' : ''; ?>>TR</option>
            <option value="CR" <?php echo $record['invoice_type'] == 'CR' ? 'selected' : ''; ?>>CR</option>
            <option value="OTHERS" <?php echo $record['invoice_type'] == 'OTHERS' ? 'selected' : ''; ?>>OTHERS</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="mode_of_payment">Mode of Payment</label>
        <select id="mode_of_payment" name="mode_of_payment">
            <option value="">Select Mode of Payment</option>
            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
            <option value="Charge" <?php echo $record['mode_of_payment'] == 'Charge' ? 'selected' : ''; ?>>Charge</option>
        </select>
    </div>
</div>

<h3 class="section-title">Supplier Details</h3>
<div class="form-row">
    <div class="form-group">
        <label for="particulars">Particulars / Supplier Name</label>
        <input type="text" id="particulars" name="particulars"
               value="<?php echo htmlspecialchars($record['particulars']); ?>">
    </div>
    
    <div class="form-group">
        <label for="tin_number">TIN Number</label>
        <input type="text" id="tin_number" name="tin_number" 
               pattern="[0-9]{12}"
               maxlength="12"
               value="<?php echo $record['tin_number']; ?>">
    </div>
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea id="address" name="address"><?php echo htmlspecialchars($record['address']); ?></textarea>
</div>

<div class="form-group">
    <label for="asset_description">Asset Description</label>
    <textarea id="asset_description" name="asset_description"><?php echo htmlspecialchars($record['asset_description']); ?></textarea>
</div>

<h3 class="section-title">Purchase Amounts</h3>
<div class="form-row">
    <div class="form-group">
        <div class="amount-input-group">
            <label for="gross_purchase_non_vat">Non-VAT Purchase</label>
            <div class="input-with-currency">
                <span class="currency-symbol">₱</span>
                <input type="text" id="gross_purchase_non_vat" name="gross_purchase_non_vat" 
                       inputmode="decimal"
                       pattern="[0-9]*\.?[0-9]*"
                       value="<?php echo number_format($record['gross_purchase_non_vat'], 2); ?>">
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="amount-input-group">
            <label for="gross_purchase_vatable" class="required">Vatable Purchase</label>
            <div class="input-with-currency">
                <span class="currency-symbol">₱</span>
                <input type="text" id="gross_purchase_vatable" name="gross_purchase_vatable" 
                       inputmode="decimal"
                       pattern="[0-9]*\.?[0-9]*"
                       value="<?php echo number_format($record['gross_purchase_vatable'], 2); ?>"
                       oninput="calculateCapexVat()"
                       required>
            </div>
        </div>
    </div>
</div>

<!-- VAT Calculation Section -->
<div class="calculation-section">
    <h4 style="color: #90caf9; margin-bottom: 15px;">VAT Calculation for Vatable Purchase (Auto-computed)</h4>
    <div class="calculation-grid">
        <div class="calculation-item">
            <div class="calculation-label">Net Vatable Purchase (Vatable Purchase ÷ 1.12)</div>
            <div class="calculation-value" id="net_vatable_purchase_display">₱<?php echo number_format($record['net_vatable_purchase'], 2); ?></div>
            <input type="hidden" id="net_vatable_purchase" name="net_vatable_purchase" value="<?php echo number_format($record['net_vatable_purchase'], 2); ?>">
        </div>
        <div class="calculation-item">
            <div class="calculation-label">Input Tax (12% of Net Vatable Purchase)</div>
            <div class="calculation-value" id="input_tax_display">₱<?php echo number_format($record['input_tax'], 2); ?></div>
            <input type="hidden" id="input_tax" name="input_tax" value="<?php echo number_format($record['input_tax'], 2); ?>">
        </div>
    </div>
</div>

<div class="form-group">
    <div class="amount-input-group">
        <label for="withholding_tax">Withholding Tax</label>
        <div class="input-with-currency">
            <span class="currency-symbol">₱</span>
            <input type="text" id="withholding_tax" name="withholding_tax" 
                   inputmode="decimal"
                   pattern="[0-9]*\.?[0-9]*"
                   value="<?php echo number_format($record['withholding_tax'], 2); ?>">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="remarks">Remarks</label>
    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
</div>
                
                <?php elseif ($type == 'taxes_licenses'): ?>
                <!-- Taxes & Licenses Form -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="date" class="required">Date</label>
                        <input type="date" id="date" name="date" required 
                               value="<?php echo $record['date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number"
                               value="<?php echo htmlspecialchars($record['reference_number']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tax_type">Tax Type</label>
                        <select id="tax_type" name="tax_type">
                            <option value="">Select Tax Type</option>
                            <option value="Income Tax" <?php echo $record['tax_type'] == 'Income Tax' ? 'selected' : ''; ?>>Income Tax</option>
                            <option value="Business Tax" <?php echo $record['tax_type'] == 'Business Tax' ? 'selected' : ''; ?>>Business Tax</option>
                            <option value="Property Tax" <?php echo $record['tax_type'] == 'Property Tax' ? 'selected' : ''; ?>>Property Tax</option>
                            <option value="VAT" <?php echo $record['tax_type'] == 'VAT' ? 'selected' : ''; ?>>VAT</option>
                            <option value="Withholding Tax" <?php echo $record['tax_type'] == 'Withholding Tax' ? 'selected' : ''; ?>>Withholding Tax</option>
                            <option value="License Fee" <?php echo $record['tax_type'] == 'License Fee' ? 'selected' : ''; ?>>License Fee</option>
                            <option value="Permit" <?php echo $record['tax_type'] == 'Permit' ? 'selected' : ''; ?>>Permit</option>
                            <option value="Other" <?php echo $record['tax_type'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mode_of_payment">Mode of Payment</label>
                        <select id="mode_of_payment" name="mode_of_payment">
                            <option value="">Select Mode of Payment</option>
                            <option value="Cash" <?php echo $record['mode_of_payment'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="Check" <?php echo $record['mode_of_payment'] == 'Check' ? 'selected' : ''; ?>>Check</option>
                            <option value="Bank Transfer" <?php echo $record['mode_of_payment'] == 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                            <option value="Online" <?php echo $record['mode_of_payment'] == 'Online' ? 'selected' : ''; ?>>Online</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="government_agency">Government Agency</label>
                    <input type="text" id="government_agency" name="government_agency"
                           value="<?php echo htmlspecialchars($record['government_agency']); ?>">
                </div>
                
                <div class="form-group">
                    <div class="amount-input-group">
                        <label for="amount" class="required">Amount</label>
                        <div class="input-with-currency">
                            <span class="currency-symbol">₱</span>
                            <input type="text" id="amount" name="amount" required
                                   inputmode="decimal"
                                   pattern="[0-9]*\.?[0-9]*"
                                   value="<?php echo number_format($record['amount'], 2); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($record['remarks']); ?></textarea>
                </div>
                
                <?php endif; ?>
                
                <div class="form-actions">
                    <a href="my_submissions.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-warning">Update Record</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function formatAmount(input) {
            // Get cursor position before modification
            let cursorPos = input.selectionStart;
            
            // Get current value
            let value = input.value;
            
            // Remove any non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
                cursorPos = Math.min(cursorPos, value.length);
            }
            
            // Limit to 2 decimal places
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
                cursorPos = Math.min(cursorPos, value.length);
            }
            
            // Update value
            input.value = value;
            
            // Restore cursor position
            input.setSelectionRange(cursorPos, cursorPos);
        }
        
        function validateTIN(input) {
            // Remove any non-numeric characters
            input.value = input.value.replace(/\D/g, '');
            
            // Limit to 12 digits
            if (input.value.length > 12) {
                input.value = input.value.substring(0, 12);
            }
        }
        
        function calculateVatSales() {
            const grossSalesInput = document.getElementById('vatable_gross_sales');
            const netSalesDisplay = document.getElementById('net_sales_display');
            const outputTaxDisplay = document.getElementById('output_tax_display');
            const netSalesHidden = document.getElementById('net_sales');
            const outputTaxHidden = document.getElementById('output_tax');
            
            if (grossSalesInput && netSalesDisplay && outputTaxDisplay) {
                let grossSales = grossSalesInput.value.replace(/[^0-9.]/g, '');
                
                if (grossSales && !isNaN(parseFloat(grossSales)) && parseFloat(grossSales) > 0) {
                    const gross = parseFloat(grossSales);
                    const netSales = gross / 1.12;
                    const outputTax = gross - netSales;
                    
                    netSalesDisplay.textContent = '₱' + netSales.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    outputTaxDisplay.textContent = '₱' + outputTax.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    netSalesHidden.value = netSales.toFixed(2);
                    outputTaxHidden.value = outputTax.toFixed(2);
                }
            }
        }
        
        function calculateVatPurchases() {
            const grossPurchasesInput = document.getElementById('vatable_gross_purchases');
            const netPurchasesDisplay = document.getElementById('net_purchases_display');
            const inputTaxDisplay = document.getElementById('input_tax_display');
            const netPurchasesHidden = document.getElementById('net_purchases');
            const inputTaxHidden = document.getElementById('input_tax');
            
            if (grossPurchasesInput && netPurchasesDisplay && inputTaxDisplay) {
                let grossPurchases = grossPurchasesInput.value.replace(/[^0-9.]/g, '');
                
                if (grossPurchases && !isNaN(parseFloat(grossPurchases)) && parseFloat(grossPurchases) > 0) {
                    const gross = parseFloat(grossPurchases);
                    const netPurchases = gross / 1.12;
                    const inputTax = gross - netPurchases;
                    
                    netPurchasesDisplay.textContent = '₱' + netPurchases.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    inputTaxDisplay.textContent = '₱' + inputTax.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    netPurchasesHidden.value = netPurchases.toFixed(2);
                    inputTaxHidden.value = inputTax.toFixed(2);
                }
            }
        }
        
        function calculateVatExpenses() {
            const grossAmountInput = document.getElementById('gross_amount');
            const netAmountDisplay = document.getElementById('net_amount_display');
            const inputTaxDisplay = document.getElementById('input_tax_display');
            const netAmountHidden = document.getElementById('net_amount');
            const inputTaxHidden = document.getElementById('input_tax');
            
            if (grossAmountInput && netAmountDisplay && inputTaxDisplay) {
                let grossAmount = grossAmountInput.value.replace(/[^0-9.]/g, '');
                
                if (grossAmount && !isNaN(parseFloat(grossAmount)) && parseFloat(grossAmount) > 0) {
                    const gross = parseFloat(grossAmount);
                    const netAmount = gross / 1.12;
                    const inputTax = gross - netAmount;
                    
                    netAmountDisplay.textContent = '₱' + netAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    inputTaxDisplay.textContent = '₱' + inputTax.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    netAmountHidden.value = netAmount.toFixed(2);
                    inputTaxHidden.value = inputTax.toFixed(2);
                }
            }
        }
        
        function validateForm() {
            const tinInput = document.getElementById('tin_number');
            if (tinInput) {
                if (tinInput.value && !/^\d{12}$/.test(tinInput.value)) {
                    alert('TIN Number must be exactly 12 digits');
                    tinInput.focus();
                    return false;
                }
            }
            
            const amountFields = document.querySelectorAll('input[inputmode="decimal"]');
            for (let field of amountFields) {
                if (field.value) {
                    let value = field.value.replace(/[^0-9.]/g, '');
                    if (value && isNaN(parseFloat(value))) {
                        alert(field.name + ' must be a valid number');
                        field.focus();
                        return false;
                    }
                    if (value && parseFloat(value) < 0) {
                        alert(field.name + ' cannot be negative');
                        field.focus();
                        return false;
                    }
                }
            }
            
            return true;
        }
        
        // Initialize amount formatting and calculations
        document.addEventListener('DOMContentLoaded', function() {
            const amountFields = document.querySelectorAll('input[inputmode="decimal"]');
            amountFields.forEach(field => {
                field.addEventListener('input', function() {
                    formatAmount(this);
                });
            });
            
            const tinFields = document.querySelectorAll('input[pattern="[0-9]{12}"]');
            tinFields.forEach(field => {
                field.addEventListener('input', function() {
                    validateTIN(this);
                });
            });
            
            // Initialize calculations based on form type
            <?php if ($type == 'vatable_sales'): ?>
                calculateVatSales();
            <?php elseif ($type == 'vatable_purchases'): ?>
                calculateVatPurchases();
            <?php elseif ($type == 'vatable_expenses'): ?>
                calculateVatExpenses();
            <?php endif; ?>
        });
    </script>
</body>
</html>