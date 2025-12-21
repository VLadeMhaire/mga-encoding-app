<?php
require_once 'config.php';
requireLogin();

// Prevent admin users from accessing encoding forms
if (isAdmin()) {
    header('Location: index.php');
    exit();
}

// Check if user has submitted information first
$stmt = $conn->prepare("SELECT id FROM information WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$has_info = $stmt->get_result()->num_rows > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $has_info) {
    $user_id = $_SESSION['user_id'];
    $info_id = $_POST['info_id'];
    $date = $_POST['date'];
    $invoice_number = $_POST['invoice_number'];
    $invoice_type = $_POST['invoice_type'];
    $transaction_type = $_POST['transaction_type'];
    $mode_of_payment = $_POST['mode_of_payment'];
    $particulars = $_POST['particulars'];
    $address = $_POST['address'];
    $tin_number = $_POST['tin_number'];
    $gross_amount = $_POST['gross_amount'];
    $nature_of_expense = $_POST['nature_of_expense'];
    $remarks = $_POST['remarks'];
    
    // Calculate Net Amount and Input Tax
    $net_amount = !empty($gross_amount) ? $gross_amount / 1.12 : 0;
    $input_tax = !empty($gross_amount) ? $gross_amount - $net_amount : 0;
    
    $stmt = $conn->prepare("INSERT INTO vatable_expenses (user_id, info_id, date, invoice_number, invoice_type, transaction_type, mode_of_payment, particulars, address, tin_number, gross_amount, net_amount, input_tax, nature_of_expense, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssssdddss", 
        $user_id, $info_id, $date, $invoice_number, $invoice_type, $transaction_type,
        $mode_of_payment, $particulars, $address, $tin_number, $gross_amount,
        $net_amount, $input_tax, $nature_of_expense, $remarks
    );
    
    if ($stmt->execute()) {
        $success = "Vatable Expense record saved successfully!";
    } else {
        $error = "Error saving record. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vatable Expenses - MGA&A Encoding App</title>
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
        
        .warning {
            background: rgba(255, 193, 7, 0.3);
            border: 1px solid #ffc107;
            color: white;
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
        
        .auto-calc-section {
            background: rgba(33, 150, 243, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }
        
        .auto-calc-section h4 {
            color: #90caf9;
            margin-bottom: 15px;
            font-size: 1.1rem;
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
        
        .calculation-note {
            background: rgba(255, 193, 7, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 0.9rem;
            color: #ffc107;
            border-left: 3px solid #ffc107;
        }
        
        .formula {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #4caf50;
        }
        
        .readonly-field {
            background: rgba(0, 0, 0, 0.3) !important;
            color: #90caf9 !important;
            cursor: not-allowed;
            border: 1px solid rgba(144, 202, 249, 0.3) !important;
        }
        
        .amount-input-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <header>
            <h1>Vatable Expenses</h1>
            <p>Enter VAT-able expenses transactions with complete details</p>
        </header>
        
        <div class="form-container">
            <?php if (!$has_info): ?>
                <div class="message warning">
                    Please complete the <a href="information.php" style="color: #ffc107;">Company Information</a> section first before entering expenses data.
                </div>
            <?php else: ?>
                <?php if (isset($success)): ?>
                    <div class="message success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php 
                // Get user's information ID
                $stmt = $conn->prepare("SELECT id FROM information WHERE user_id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $info = $stmt->get_result()->fetch_assoc();
                ?>
                
                <form method="POST" action="" id="expenseForm" onsubmit="return validateForm()">
                    <input type="hidden" name="info_id" value="<?php echo $info['id']; ?>">
                    
                    <h3 class="section-title">Basic Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date" class="required">Date</label>
                            <input type="date" id="date" name="date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="invoice_number">Invoice Number</label>
                            <input type="text" id="invoice_number" name="invoice_number" placeholder="Enter invoice number">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="invoice_type">Invoice Type</label>
                            <select id="invoice_type" name="invoice_type">
                                <option value="">Select Invoice Type</option>
                                <option value="OR">OR (Official Receipt)</option>
                                <option value="SI">SI (Sales Invoice)</option>
                                <option value="DR">DR (Delivery Receipt)</option>
                                <option value="AR">AR (Acknowledgement Receipt)</option>
                                <option value="TR">TR (Transfer Receipt)</option>
                                <option value="CR">CR (Credit Receipt)</option>
                                <option value="OTHERS">OTHERS</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="transaction_type">Transaction Type</label>
                            <select id="transaction_type" name="transaction_type">
                                <option value="">Select Transaction Type</option>
                                <option value="Goods">Goods</option>
                                <option value="Services">Services</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mode_of_payment">Mode of Payment</label>
                            <select id="mode_of_payment" name="mode_of_payment">
                                <option value="">Select Mode of Payment</option>
                                <option value="Cash">Cash</option>
                                <option value="Charge">Charge</option>
                            </select>
                        </div>
                    </div>
                    
                    <h3 class="section-title">Supplier Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="particulars" class="required">Particulars / Supplier Name</label>
                            <input type="text" id="particulars" name="particulars" placeholder="Enter supplier name or particulars" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" placeholder="Enter supplier address"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tin_number">12-Digit TIN Number</label>
                        <input type="text" id="tin_number" name="tin_number" 
                               pattern="[0-9]{12}"
                               maxlength="12"
                               placeholder="000000000000"
                               oninput="validateTIN(this)">
                        <div class="input-hint">Enter 12 digits only (numbers only)</div>
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
                                           placeholder="0.00"
                                           oninput="formatAmount(this); calculateNetExpense();"
                                           required>
                                </div>
                                <div class="input-hint">Enter amount with decimal point (e.g., 1000.50)</div>
                                <div class="calculation-note">
                                    <strong>Note:</strong> Gross Amount = Net Amount + 12% Input Tax
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nature_of_expense" class="required">Nature of Expense</label>
                            <select id="nature_of_expense" name="nature_of_expense" required>
                                <option value="">Select Nature of Expense</option>
                                <option value="REPAIRS AND MAINTENANCE-LABOR">REPAIRS AND MAINTENANCE-LABOR</option>
                                <option value="REPAIRS AND MAINTENANCE-MATERIALS">REPAIRS AND MAINTENANCE-MATERIALS</option>
                                <option value="MEALS AND ACCOMMODATION - SERVICES">MEALS AND ACCOMMODATION - SERVICES</option>
                                <option value="MEALS AND ACCOMMODATION - GOODS">MEALS AND ACCOMMODATION - GOODS</option>
                                <option value="REPRESENTATIONS - SERVICES">REPRESENTATIONS - SERVICES</option>
                                <option value="REPRESENTATIONS - GOODS">REPRESENTATIONS - GOODS</option>
                                <option value="OTHERS - SERVICES">OTHERS - SERVICES</option>
                                <option value="OTHERS - GOODS">OTHERS - GOODS</option>
                                <option value="MEDICAL EXPENSES - SERVICES">MEDICAL EXPENSES - SERVICES</option>
                                <option value="MEDICAL EXPENSES - GOODS">MEDICAL EXPENSES - GOODS</option>
                                <option value="CLEANING SERVICES">CLEANING SERVICES</option>
                                <option value="CLEANING SUPPLIES">CLEANING SUPPLIES</option>
                                <option value="ELECTRICITY AND WATER">ELECTRICITY AND WATER</option>
                                <option value="COMMUNICATION EXPENSE">COMMUNICATION EXPENSE</option>
                                <option value="FREIGHT AND COURIER">FREIGHT AND COURIER</option>
                                <option value="TRAVEL AND TRANSPORTATION">TRAVEL AND TRANSPORTATION</option>
                                <option value="ADVERTISING AND MARKETING">ADVERTISING AND MARKETING</option>
                                <option value="SECURITY SERVICES">SECURITY SERVICES</option>
                                <option value="INSURANCE">INSURANCE</option>
                                <option value="SEMINAR AND TRAINING">SEMINAR AND TRAINING</option>
                                <option value="PROFESSIONAL FEES">PROFESSIONAL FEES</option>
                                <option value="FUEL AND OIL">FUEL AND OIL</option>
                                <option value="OFFICE SUPPLIES">OFFICE SUPPLIES</option>
                                <option value="UNIFORMS">UNIFORMS</option>
                                <option value="Rental">Rental</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Auto-calculated Fields Section -->
                    <div class="auto-calc-section">
                        <h4>VAT Calculation (Auto-computed)</h4>
                        <div class="calculation-grid">
                            <div class="calculation-item">
                                <div class="calculation-label">Net Expense (Gross Amount ÷ 1.12)</div>
                                <div class="calculation-value" id="net_amount_display">₱0.00</div>
                                <input type="hidden" id="net_amount" name="net_amount">
                            </div>
                            <div class="calculation-item">
                                <div class="calculation-label">Input Tax (12% of Net Expense)</div>
                                <div class="calculation-value" id="input_tax_display">₱0.00</div>
                                <input type="hidden" id="input_tax" name="input_tax">
                            </div>
                        </div>
                        <div class="calculation-note" style="margin-top: 15px;">
                            <strong>Formula:</strong><br>
                            <span class="formula">Net Expense = Gross Amount ÷ 1.12</span><br>
                            <span class="formula">Input Tax = Gross Amount - Net Expense</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="remarks" placeholder="Enter any additional remarks or notes"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Expense Record</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function validateTIN(input) {
            // Remove any non-numeric characters
            input.value = input.value.replace(/\D/g, '');
            
            // Limit to 12 digits
            if (input.value.length > 12) {
                input.value = input.value.substring(0, 12);
            }
        }
        
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
            
            // Trigger calculation if this is the gross amount field
            if (input.id === 'gross_amount') {
                calculateNetExpense();
            }
        }
        
        function calculateNetExpense() {
            const grossAmountInput = document.getElementById('gross_amount');
            const netAmountDisplay = document.getElementById('net_amount_display');
            const inputTaxDisplay = document.getElementById('input_tax_display');
            const netAmountHidden = document.getElementById('net_amount');
            const inputTaxHidden = document.getElementById('input_tax');
            
            // Get the raw value (remove any formatting)
            let grossAmount = grossAmountInput.value.replace(/[^0-9.]/g, '');
            
            if (grossAmount && !isNaN(parseFloat(grossAmount)) && parseFloat(grossAmount) > 0) {
                const gross = parseFloat(grossAmount);
                
                // Calculate Net Amount: Gross Amount ÷ 1.12
                const netAmount = gross / 1.12;
                
                // Calculate Input Tax: Gross Amount - Net Amount (or Net Amount * 0.12)
                const inputTax = gross - netAmount;
                
                // Format and display the results
                netAmountDisplay.textContent = '₱' + netAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                inputTaxDisplay.textContent = '₱' + inputTax.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                
                // Set the hidden input values
                netAmountHidden.value = netAmount.toFixed(2);
                inputTaxHidden.value = inputTax.toFixed(2);
                
                // Show calculation verification
                console.log('Gross Amount:', gross);
                console.log('Net Amount:', netAmount);
                console.log('Input Tax:', inputTax);
                console.log('Verification (Net Amount + Input Tax):', (netAmount + inputTax).toFixed(2));
            } else {
                // Reset values if input is empty or invalid
                netAmountDisplay.textContent = '₱0.00';
                inputTaxDisplay.textContent = '₱0.00';
                netAmountHidden.value = '0.00';
                inputTaxHidden.value = '0.00';
            }
        }
        
        function validateForm() {
            const tin = document.getElementById('tin_number');
            const grossAmount = document.getElementById('gross_amount');
            const date = document.getElementById('date');
            const particulars = document.getElementById('particulars');
            const natureOfExpense = document.getElementById('nature_of_expense');
            
            // Validate required fields
            if (!date.value) {
                alert('Please select a date');
                date.focus();
                return false;
            }
            
            if (!particulars.value.trim()) {
                alert('Please enter supplier particulars/name');
                particulars.focus();
                return false;
            }
            
            if (!natureOfExpense.value) {
                alert('Please select nature of expense');
                natureOfExpense.focus();
                return false;
            }
            
            // Validate TIN if provided
            if (tin.value && !/^\d{12}$/.test(tin.value)) {
                alert('TIN Number must be exactly 12 digits');
                tin.focus();
                return false;
            }
            
            // Validate gross amount
            if (grossAmount.value) {
                // Remove currency symbol and commas for validation
                let amountValue = grossAmount.value.replace(/[^0-9.]/g, '');
                if (amountValue && isNaN(parseFloat(amountValue))) {
                    alert('Gross Amount must be a valid number');
                    grossAmount.focus();
                    return false;
                }
                
                // Validate amount is positive
                if (amountValue && parseFloat(amountValue) <= 0) {
                    alert('Gross Amount must be greater than 0');
                    grossAmount.focus();
                    return false;
                }
            } else {
                alert('Please enter gross amount');
                grossAmount.focus();
                return false;
            }
            
            return true;
        }
        
        // Set today's date as default
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
            
            // Auto-format amount fields on page load
            const amountFields = document.querySelectorAll('input[inputmode="decimal"]');
            amountFields.forEach(field => {
                if (field.value) {
                    formatAmount(field);
                }
            });
            
            // Initialize calculation on page load if there's already a value
            const grossAmount = document.getElementById('gross_amount');
            if (grossAmount.value) {
                calculateNetExpense();
            }
        });
        
        // Allow decimal point key press
        document.addEventListener('keydown', function(e) {
            const target = e.target;
            if (target.getAttribute('inputmode') === 'decimal') {
                // Allow decimal point (period)
                if (e.key === '.' || e.key === 'Decimal') {
                    // Allow it - default behavior
                }
                // Allow navigation keys
                else if ([
                    'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End'
                ].includes(e.key)) {
                    // Allow navigation keys
                }
                // Allow Ctrl/Command combinations
                else if (e.ctrlKey || e.metaKey) {
                    // Allow Ctrl+A, Ctrl+C, Ctrl+V, etc.
                }
                // Allow numbers
                else if (/^\d$/.test(e.key)) {
                    // Allow numbers
                }
                // Prevent other keys
                else {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>