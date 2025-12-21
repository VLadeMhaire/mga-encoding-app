<?php
require_once 'config.php';
requireLogin();

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
    $mode_of_payment = $_POST['mode_of_payment'];
    $particulars = $_POST['particulars'];
    $asset_description = $_POST['asset_description'];
    $address = $_POST['address'];
    $tin_number = $_POST['tin_number'];
    $gross_purchase_non_vat = $_POST['gross_purchase_non_vat'] ?? 0;
    $gross_purchase_vatable = $_POST['gross_purchase_vatable'] ?? 0;
    $net_vatable_purchase = $_POST['net_vatable_purchase'] ?? 0;
    $input_tax = $_POST['input_tax'] ?? 0;
    $withholding_tax = $_POST['withholding_tax'] ?? 0;
    $remarks = $_POST['remarks'];
    
    $stmt = $conn->prepare("INSERT INTO capex (user_id, info_id, date, invoice_number, invoice_type, mode_of_payment, particulars, asset_description, address, tin_number, gross_purchase_non_vat, gross_purchase_vatable, net_vatable_purchase, input_tax, withholding_tax, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssssddddds", 
        $user_id, $info_id, $date, $invoice_number, $invoice_type,
        $mode_of_payment, $particulars, $asset_description, $address, $tin_number,
        $gross_purchase_non_vat, $gross_purchase_vatable, $net_vatable_purchase, $input_tax,
        $withholding_tax, $remarks
    );
    
    if ($stmt->execute()) {
        $success = "CAPEX record saved successfully!";
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
    <title>CAPEX (Capital Expenditure) - MGA&A Encoding App</title>
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
        
        .amount-subtitle {
            font-size: 0.9rem;
            color: #ffcc80;
            font-style: italic;
            margin-top: 3px;
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
        
        .total-summary {
            background: rgba(0, 150, 136, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            border: 1px solid rgba(0, 150, 136, 0.3);
        }
        
        .total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .total-label {
            font-weight: bold;
            color: #bbdefb;
        }
        
        .total-amount {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #4caf50;
            font-size: 1.1rem;
        }
        
        .grand-total {
            color: #ffc107;
            font-size: 1.2rem;
            border-top: 2px solid rgba(255,255,255,0.2);
            padding-top: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <header>
            <h1>CAPEX (Capital Expenditure)</h1>
            <p>Enter capital expenditure transactions with complete details</p>
        </header>
        
        <div class="form-container">
            <?php if (!$has_info): ?>
                <div class="message warning">
                    Please complete the <a href="information.php" style="color: #ffc107;">Company Information</a> section first before entering CAPEX data.
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
                
                <form method="POST" action="" id="capexForm" onsubmit="return validateForm()">
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
                            <label for="mode_of_payment">Mode of Payment</label>
                            <select id="mode_of_payment" name="mode_of_payment">
                                <option value="">Select Mode of Payment</option>
                                <option value="Cash">Cash</option>
                                <option value="Charge">Charge</option>
                            </select>
                        </div>
                    </div>
                    
                    <h3 class="section-title">Supplier & Asset Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="particulars" class="required">Particulars / Supplier Name</label>
                            <input type="text" id="particulars" name="particulars" placeholder="Enter supplier name or particulars" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="asset_description" class="required">Asset Description</label>
                            <textarea id="asset_description" name="asset_description" placeholder="Enter asset description" required></textarea>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" placeholder="Enter supplier address"></textarea>
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
                    </div>
                    
                    <h3 class="section-title">Purchase Amounts</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <div class="amount-input-group">
                                <label for="gross_purchase_non_vat">Gross Purchase 1</label>
                                <div class="input-with-currency">
                                    <span class="currency-symbol">₱</span>
                                    <input type="text" id="gross_purchase_non_vat" name="gross_purchase_non_vat" 
                                           inputmode="decimal"
                                           pattern="[0-9]*\.?[0-9]*"
                                           placeholder="0.00"
                                           oninput="formatAmount(this)">
                                </div>
                                <div class="amount-subtitle">For Non-VAT Purchases</div>
                                <div class="input-hint">Enter amount with decimal point (e.g., 1000.50)</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="amount-input-group">
                                <label for="gross_purchase_vatable" class="required">Gross Purchase 2</label>
                                <div class="input-with-currency">
                                    <span class="currency-symbol">₱</span>
                                    <input type="text" id="gross_purchase_vatable" name="gross_purchase_vatable" 
                                           inputmode="decimal"
                                           pattern="[0-9]*\.?[0-9]*"
                                           placeholder="0.00"
                                           oninput="formatAmount(this); calculateCapexVat();"
                                           required>
                                </div>
                                <div class="amount-subtitle">For VATable Purchases</div>
                                <div class="input-hint">Enter amount with decimal point (e.g., 1000.50)</div>
                                <div class="calculation-note">
                                    <strong>Note:</strong> Vatable Gross Purchase = Net Vatable Purchase + 12% Input Tax
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Auto-calculated VAT Section -->
                    <div class="auto-calc-section">
                        <h4>VAT Calculation for Vatable Purchase (Auto-computed)</h4>
                        <div class="calculation-grid">
                            <div class="calculation-item">
                                <div class="calculation-label">Net Vatable Purchase (Gross Purchase 2 ÷ 1.12)</div>
                                <div class="calculation-value" id="net_vatable_purchase_display">₱0.00</div>
                                <input type="hidden" id="net_vatable_purchase" name="net_vatable_purchase">
                            </div>
                            <div class="calculation-item">
                                <div class="calculation-label">Input Tax (12% of Net Vatable Purchase)</div>
                                <div class="calculation-value" id="input_tax_display">₱0.00</div>
                                <input type="hidden" id="input_tax" name="input_tax">
                            </div>
                        </div>
                        <div class="calculation-note" style="margin-top: 15px;">
                            <strong>Formula:</strong><br>
                            <span class="formula">Net Vatable Purchase = Gross Purchase 2 ÷ 1.12</span><br>
                            <span class="formula">Input Tax = Gross Purchase 2 - Net Vatable Purchase</span>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="amount-input-group">
                                <label for="withholding_tax">Withholding Tax</label>
                                <div class="input-with-currency">
                                    <span class="currency-symbol">₱</span>
                                    <input type="text" id="withholding_tax" name="withholding_tax" 
                                           inputmode="decimal"
                                           pattern="[0-9]*\.?[0-9]*"
                                           placeholder="0.00"
                                           oninput="formatAmount(this); calculateCapexTotal();">
                                </div>
                                <div class="input-hint">Manual cash amount input</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Summary -->
                    <div class="total-summary">
                        <h4 style="color: #90caf9; margin-bottom: 15px;">Total Purchase Summary</h4>
                        <div class="total-item">
                            <div class="total-label">Non-VAT Purchase:</div>
                            <div class="total-amount" id="non_vat_total_display">₱0.00</div>
                        </div>
                        <div class="total-item">
                            <div class="total-label">Net Vatable Purchase:</div>
                            <div class="total-amount" id="net_vatable_total_display">₱0.00</div>
                        </div>
                        <div class="total-item">
                            <div class="total-label">VAT Input Tax:</div>
                            <div class="total-amount" id="vat_input_total_display">₱0.00</div>
                        </div>
                        <div class="total-item">
                            <div class="total-label">Withholding Tax:</div>
                            <div class="total-amount" id="withholding_total_display">₱0.00</div>
                        </div>
                        <div class="total-item grand-total">
                            <div class="total-label">Total Purchase Amount:</div>
                            <div class="total-amount" id="grand_total_display">₱0.00</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="remarks" placeholder="Enter any additional remarks or notes"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save CAPEX Record</button>
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
            
            // Trigger calculations
            if (input.id === 'gross_purchase_vatable') {
                calculateCapexVat();
            }
            calculateCapexTotal();
        }
        
        function calculateCapexVat() {
            const grossVatableInput = document.getElementById('gross_purchase_vatable');
            const netVatableDisplay = document.getElementById('net_vatable_purchase_display');
            const inputTaxDisplay = document.getElementById('input_tax_display');
            const netVatableHidden = document.getElementById('net_vatable_purchase');
            const inputTaxHidden = document.getElementById('input_tax');
            
            // Get the raw value (remove any formatting)
            let grossVatable = grossVatableInput.value.replace(/[^0-9.]/g, '');
            
            if (grossVatable && !isNaN(parseFloat(grossVatable)) && parseFloat(grossVatable) > 0) {
                const gross = parseFloat(grossVatable);
                
                // Calculate Net Vatable Purchase: Gross Purchase 2 ÷ 1.12
                const netVatable = gross / 1.12;
                
                // Calculate Input Tax: Gross Purchase 2 - Net Vatable Purchase (or Net Vatable * 0.12)
                const inputTax = gross - netVatable;
                
                // Format and display the results
                netVatableDisplay.textContent = '₱' + netVatable.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                inputTaxDisplay.textContent = '₱' + inputTax.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                
                // Set the hidden input values
                netVatableHidden.value = netVatable.toFixed(2);
                inputTaxHidden.value = inputTax.toFixed(2);
                
                // Show calculation verification
                console.log('Gross Vatable Purchase:', gross);
                console.log('Net Vatable Purchase:', netVatable);
                console.log('Input Tax:', inputTax);
                console.log('Verification (Net + Input Tax):', (netVatable + inputTax).toFixed(2));
            } else {
                // Reset values if input is empty or invalid
                netVatableDisplay.textContent = '₱0.00';
                inputTaxDisplay.textContent = '₱0.00';
                netVatableHidden.value = '0.00';
                inputTaxHidden.value = '0.00';
            }
        }
        
        function calculateCapexTotal() {
            const nonVatInput = document.getElementById('gross_purchase_non_vat');
            const withholdingInput = document.getElementById('withholding_tax');
            const netVatableHidden = document.getElementById('net_vatable_purchase');
            const inputTaxHidden = document.getElementById('input_tax');
            
            const nonVatTotalDisplay = document.getElementById('non_vat_total_display');
            const netVatableTotalDisplay = document.getElementById('net_vatable_total_display');
            const vatInputTotalDisplay = document.getElementById('vat_input_total_display');
            const withholdingTotalDisplay = document.getElementById('withholding_total_display');
            const grandTotalDisplay = document.getElementById('grand_total_display');
            
            // Parse values
            let nonVat = nonVatInput.value.replace(/[^0-9.]/g, '');
            nonVat = nonVat && !isNaN(parseFloat(nonVat)) ? parseFloat(nonVat) : 0;
            
            let withholding = withholdingInput.value.replace(/[^0-9.]/g, '');
            withholding = withholding && !isNaN(parseFloat(withholding)) ? parseFloat(withholding) : 0;
            
            let netVatable = netVatableHidden.value ? parseFloat(netVatableHidden.value) : 0;
            let inputTax = inputTaxHidden.value ? parseFloat(inputTaxHidden.value) : 0;
            
            // Calculate totals
            const totalNonVat = nonVat;
            const totalNetVatable = netVatable;
            const totalInputTax = inputTax;
            const totalWithholding = withholding;
            const grandTotal = totalNonVat + totalNetVatable + totalInputTax + totalWithholding;
            
            // Update displays
            nonVatTotalDisplay.textContent = '₱' + totalNonVat.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            netVatableTotalDisplay.textContent = '₱' + totalNetVatable.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            vatInputTotalDisplay.textContent = '₱' + totalInputTax.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            withholdingTotalDisplay.textContent = '₱' + totalWithholding.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            grandTotalDisplay.textContent = '₱' + grandTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            
            console.log('CAPEX Totals:', {
                nonVat: totalNonVat,
                netVatable: totalNetVatable,
                inputTax: totalInputTax,
                withholding: totalWithholding,
                grandTotal: grandTotal
            });
        }
        
        function validateForm() {
            const tin = document.getElementById('tin_number');
            const date = document.getElementById('date');
            const particulars = document.getElementById('particulars');
            const assetDescription = document.getElementById('asset_description');
            const grossNonVat = document.getElementById('gross_purchase_non_vat');
            const grossVatable = document.getElementById('gross_purchase_vatable');
            const withholdingTax = document.getElementById('withholding_tax');
            
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
            
            if (!assetDescription.value.trim()) {
                alert('Please enter asset description');
                assetDescription.focus();
                return false;
            }
            
            if (!grossVatable.value.trim()) {
                alert('Please enter Gross Purchase 2 (Vatable amount)');
                grossVatable.focus();
                return false;
            }
            
            // Validate TIN if provided
            if (tin.value && !/^\d{12}$/.test(tin.value)) {
                alert('TIN Number must be exactly 12 digits');
                tin.focus();
                return false;
            }
            
            // Validate gross purchase non-VAT if provided
            if (grossNonVat.value) {
                let amountValue = grossNonVat.value.replace(/[^0-9.]/g, '');
                if (amountValue && isNaN(parseFloat(amountValue))) {
                    alert('Gross Purchase 1 must be a valid number');
                    grossNonVat.focus();
                    return false;
                }
                
                // Validate amount is positive if entered
                if (amountValue && parseFloat(amountValue) < 0) {
                    alert('Gross Purchase 1 cannot be negative');
                    grossNonVat.focus();
                    return false;
                }
            }
            
            // Validate gross purchase vatable
            if (grossVatable.value) {
                let amountValue = grossVatable.value.replace(/[^0-9.]/g, '');
                if (amountValue && isNaN(parseFloat(amountValue))) {
                    alert('Gross Purchase 2 must be a valid number');
                    grossVatable.focus();
                    return false;
                }
                
                // Validate amount is positive
                if (amountValue && parseFloat(amountValue) <= 0) {
                    alert('Gross Purchase 2 must be greater than 0');
                    grossVatable.focus();
                    return false;
                }
            }
            
            // Validate withholding tax if provided
            if (withholdingTax.value) {
                let amountValue = withholdingTax.value.replace(/[^0-9.]/g, '');
                if (amountValue && isNaN(parseFloat(amountValue))) {
                    alert('Withholding Tax must be a valid number');
                    withholdingTax.focus();
                    return false;
                }
                
                // Validate amount is positive if entered
                if (amountValue && parseFloat(amountValue) < 0) {
                    alert('Withholding Tax cannot be negative');
                    withholdingTax.focus();
                    return false;
                }
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
            
            // Initialize calculations on page load if there's already a value
            const grossVatable = document.getElementById('gross_purchase_vatable');
            if (grossVatable.value) {
                calculateCapexVat();
                calculateCapexTotal();
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