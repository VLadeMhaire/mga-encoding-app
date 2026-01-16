// vatable_purchases_script.js - JavaScript for Vatable Purchases page

// Initialize the table with one empty row
document.addEventListener('DOMContentLoaded', function() {
    // Set default date for report
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('reportDate').value = today;
    
    // Try to load the logo
    loadLogo();
    
    // Load saved company info if exists
    loadCompanyInfo();
    
    // Initialize table
    addNewRow();
    updateRowCount();
    updateTotals();
    updateSummaryRow();
    
    // Add event listeners for company info to save automatically
    document.querySelectorAll('.company-info-section input').forEach(input => {
        input.addEventListener('input', saveCompanyInfo);
        input.addEventListener('change', saveCompanyInfo);
    });
    
    // Format company TIN input
    const companyTIN = document.getElementById('companyTIN');
    companyTIN.addEventListener('input', function(e) {
        formatTIN(e.target);
    });
    
    // Clear company info button
    document.getElementById('clearInfoBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all company information?')) {
            clearCompanyInfo();
        }
    });
    
    // Add subtle background animation
    createBackgroundEffects();
    
    // Add row button
    document.getElementById('addRowBtn').addEventListener('click', function() {
        addNewRow();
        updateRowCount();
        updateTotals();
        updateSummaryRow();
    });
    
    // Export to Excel button
    document.getElementById('exportBtn').addEventListener('click', exportToExcel);
    
    // Clear all data button
    document.getElementById('clearBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all table data? This cannot be undone.')) {
            clearAllData();
        }
    });
    
    // Initialize tally sections
    updateTallySections();
});

// Function to create subtle background effects
function createBackgroundEffects() {
    const container = document.querySelector('.container');
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                // Container style changed
            }
        });
    });
    
    observer.observe(container, { attributes: true });
}

// Function to load logo
function loadLogo() {
    const logoPlaceholder = document.getElementById('logoPlaceholder');
    
    // Try to load from local file
    const logoUrl = 'MGAALogo.jpg';
    const img = new Image();
    
    img.onload = function() {
        logoPlaceholder.innerHTML = '';
        logoPlaceholder.appendChild(img);
        // Add neon effect to logo
        img.style.filter = 'drop-shadow(0 0 8px rgba(0, 255, 157, 0.7))';
    };
    
    img.onerror = function() {
        // If logo not found, keep the placeholder text
        console.log('Logo not found at ' + logoUrl + ', using placeholder');
        logoPlaceholder.style.textShadow = '0 0 10px rgba(0, 255, 157, 0.7)';
    };
    
    img.src = logoUrl;
}

// Save company info to localStorage
function saveCompanyInfo() {
    const companyInfo = {
        name: document.getElementById('companyName').value,
        tin: document.getElementById('companyTIN').value,
        address: document.getElementById('companyAddress').value,
        business: document.getElementById('lineOfBusiness').value,
        telephone: document.getElementById('telephone').value,
        date: document.getElementById('reportDate').value,
        employee: document.getElementById('authorizedEmployee').value,
        email: document.getElementById('email').value
    };
    localStorage.setItem('vatablePurchasesCompanyInfo', JSON.stringify(companyInfo));
}

// Load company info from localStorage
function loadCompanyInfo() {
    const savedInfo = localStorage.getItem('vatablePurchasesCompanyInfo');
    if (savedInfo) {
        const companyInfo = JSON.parse(savedInfo);
        document.getElementById('companyName').value = companyInfo.name || '';
        document.getElementById('companyTIN').value = companyInfo.tin || '';
        document.getElementById('companyAddress').value = companyInfo.address || '';
        document.getElementById('lineOfBusiness').value = companyInfo.business || '';
        document.getElementById('telephone').value = companyInfo.telephone || '';
        document.getElementById('reportDate').value = companyInfo.date || new Date().toISOString().split('T')[0];
        document.getElementById('authorizedEmployee').value = companyInfo.employee || '';
        document.getElementById('email').value = companyInfo.email || '';
    }
}

// Clear company info
function clearCompanyInfo() {
    document.getElementById('companyName').value = '';
    document.getElementById('companyTIN').value = '';
    document.getElementById('companyAddress').value = '';
    document.getElementById('lineOfBusiness').value = '';
    document.getElementById('telephone').value = '';
    document.getElementById('reportDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('authorizedEmployee').value = '';
    document.getElementById('email').value = '';
    localStorage.removeItem('vatablePurchasesCompanyInfo');
}

// Function to add a new row
function addNewRow() {
    const tableBody = document.getElementById('tableBody');
    const rowCount = tableBody.children.length + 1;
    const rowId = 'row_' + Date.now() + '_' + rowCount;
    
    const row = document.createElement('tr');
    row.id = rowId;
    
    // Set current date as default
    const today = new Date().toISOString().split('T')[0];
    
    row.innerHTML = `
        <td>
            <input type="date" value="${today}" class="editable date-input">
        </td>
        <td>
            <input type="text" class="editable invoice-number" placeholder="INV-001">
        </td>
        <td>
            <select class="editable invoice-type">
                <option value="">Select</option>
                <option value="OR">OR</option>
                <option value="SI">SI</option>
                <option value="DR">DR</option>
                <option value="AR">AR</option>
                <option value="TR">TR</option>
                <option value="CR">CR</option>
                <option value="OTHERS">OTHERS</option>
            </select>
        </td>
        <td>
            <select class="editable payment-mode">
                <option value="">Select</option>
                <option value="Cash">Cash</option>
                <option value="Charge">Charge</option>
            </select>
        </td>
        <td>
            <input type="text" class="editable tin-number tin-input" maxlength="15" placeholder="000-000-000-000">
            <div class="validation-message">TIN must be 12 digits</div>
        </td>
        <td>
            <input type="text" class="editable supplier-name" placeholder="Supplier name">
        </td>
        <td>
            <input type="text" class="editable address" placeholder="Supplier address">
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="editable gross-purchases" placeholder="0.00">
        </td>
        <td>
            <div class="uneditable net-purchases">0.00</div>
        </td>
        <td>
            <div class="uneditable input-tax">0.00</div>
        </td>
        <td>
            <select class="editable withholding-rate">
                <option value="0">0%</option>
                <option value="0.01">1%</option>
                <option value="0.02">2%</option>
                <option value="0.05">5%</option>
            </select>
        </td>
        <td>
            <div class="uneditable withholding-amount">0.00</div>
        </td>
        <td>
            <div class="uneditable gross-minus-withholding">0.00</div>
        </td>
        <td>
            <input type="text" class="editable remarks" placeholder="Enter remarks">
        </td>
        <td>
            <button class="delete-btn" onclick="deleteRow('${rowId}')" title="Delete row">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tableBody.appendChild(row);
    
    // Add event listeners to the new row
    const editableInputs = row.querySelectorAll('.editable');
    editableInputs.forEach(input => {
        input.addEventListener('input', function() {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
            updateTallySections();
        });
        
        input.addEventListener('change', function() {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
            updateTallySections();
        });
    });
    
    // Special handling for TIN input
    const tinInput = row.querySelector('.tin-number');
    tinInput.addEventListener('input', function(e) {
        formatTIN(e.target);
    });
    
    tinInput.addEventListener('blur', function(e) {
        validateTIN(e.target);
    });
    
    // Add neon effect to new row inputs on focus
    const inputs = row.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.boxShadow = '0 0 0 3px rgba(0, 255, 157, 0.3), inset 0 0 15px rgba(0, 255, 157, 0.2)';
        });
        
        input.addEventListener('blur', function() {
            this.style.boxShadow = 'inset 0 0 10px rgba(0, 20, 15, 0.5)';
        });
    });
    
    updateTallySections();
}

// Function to delete a row
function deleteRow(rowId) {
    const row = document.getElementById(rowId);
    if (row && confirm('Are you sure you want to delete this row?')) {
        // Add fade out effect
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            row.remove();
            updateRowCount();
            updateTotals();
            updateSummaryRow();
            updateTallySections();
        }, 300);
    }
}

// Function to clear all data
function clearAllData() {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';
    addNewRow();
    updateRowCount();
    updateTotals();
    updateSummaryRow();
    updateTallySections();
}

// Function to update calculations for a row
function updateCalculations(row) {
    const grossPurchases = parseFloat(row.querySelector('.gross-purchases').value) || 0;
    const withholdingRate = parseFloat(row.querySelector('.withholding-rate').value) || 0;
    
    // Calculate net purchases: Gross Purchases / 1.12
    const netPurchases = grossPurchases / 1.12;
    row.querySelector('.net-purchases').textContent = netPurchases.toFixed(2);
    
    // Calculate input tax: Net Purchases * 0.12
    const inputTax = netPurchases * 0.12;
    row.querySelector('.input-tax').textContent = inputTax.toFixed(2);
    
    // Calculate withholding amount: Net Purchases * Withholding Tax Rate
    const withholdingAmount = netPurchases * withholdingRate;
    row.querySelector('.withholding-amount').textContent = withholdingAmount.toFixed(2);
    
    // Calculate Gross Amount minus Withholding Tax
    const grossMinusWithholding = grossPurchases - withholdingAmount;
    row.querySelector('.gross-minus-withholding').textContent = grossMinusWithholding.toFixed(2);
    
    updateTallySections();
}

// Function to format TIN input
function formatTIN(input) {
    // Remove all non-digit characters
    let value = input.value.replace(/\D/g, '');
    
    // Limit to 12 digits
    if (value.length > 12) {
        value = value.substring(0, 12);
    }
    
    // Add dashes after every 3 digits
    if (value.length > 9) {
        value = value.substring(0, 9) + '-' + value.substring(9);
    }
    if (value.length > 6) {
        value = value.substring(0, 6) + '-' + value.substring(6);
    }
    if (value.length > 3) {
        value = value.substring(0, 3) + '-' + value.substring(3);
    }
    
    input.value = value;
}

// Function to validate TIN input
function validateTIN(input) {
    const value = input.value.replace(/\D/g, '');
    const validationMessage = input.nextElementSibling;
    
    if (value.length === 12) {
        validationMessage.style.display = 'none';
        input.style.borderColor = '#00ff9d';
        input.style.boxShadow = '0 0 5px rgba(0, 255, 157, 0.5), inset 0 0 10px rgba(0, 255, 157, 0.1)';
    } else if (value.length > 0) {
        validationMessage.style.display = 'block';
        input.style.borderColor = '#ff6666';
        input.style.boxShadow = '0 0 5px rgba(255, 102, 102, 0.5), inset 0 0 10px rgba(255, 102, 102, 0.1)';
    } else {
        validationMessage.style.display = 'none';
        input.style.borderColor = 'rgba(0, 255, 157, 0.3)';
        input.style.boxShadow = 'inset 0 0 10px rgba(0, 20, 15, 0.5)';
    }
}

// Function to update row count
function updateRowCount() {
    const tableBody = document.getElementById('tableBody');
    const rowCount = tableBody.children.length;
    document.getElementById('rowCount').textContent = rowCount;
}

// Function to update totals
function updateTotals() {
    let totalGrossPurchases = 0;
    let totalNetPurchases = 0;
    let totalInputTax = 0;
    let totalWithholding = 0;
    let totalGrossMinusWithholding = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const grossPurchases = parseFloat(row.querySelector('.gross-purchases').value) || 0;
        const netPurchases = parseFloat(row.querySelector('.net-purchases').textContent) || 0;
        const inputTax = parseFloat(row.querySelector('.input-tax').textContent) || 0;
        const withholdingAmount = parseFloat(row.querySelector('.withholding-amount').textContent) || 0;
        const grossMinusWithholding = parseFloat(row.querySelector('.gross-minus-withholding').textContent) || 0;
        
        totalGrossPurchases += grossPurchases;
        totalNetPurchases += netPurchases;
        totalInputTax += inputTax;
        totalWithholding += withholdingAmount;
        totalGrossMinusWithholding += grossMinusWithholding;
    });
    
    document.getElementById('totalGrossPurchases').textContent = totalGrossPurchases.toFixed(2);
    document.getElementById('totalNetPurchases').textContent = totalNetPurchases.toFixed(2);
    document.getElementById('totalInputTax').textContent = totalInputTax.toFixed(2);
    document.getElementById('totalWithholding').textContent = totalWithholding.toFixed(2);
    
    // Calculate net amount payable: Gross Purchases - Withholding Amount
    document.getElementById('netPayable').textContent = totalGrossMinusWithholding.toFixed(2);
}

// Function to update summary row
function updateSummaryRow() {
    let totalGrossPurchases = 0;
    let totalNetPurchases = 0;
    let totalInputTax = 0;
    let totalWithholding = 0;
    let totalGrossMinusWithholding = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const grossPurchases = parseFloat(row.querySelector('.gross-purchases').value) || 0;
        const netPurchases = parseFloat(row.querySelector('.net-purchases').textContent) || 0;
        const inputTax = parseFloat(row.querySelector('.input-tax').textContent) || 0;
        const withholdingAmount = parseFloat(row.querySelector('.withholding-amount').textContent) || 0;
        const grossMinusWithholding = parseFloat(row.querySelector('.gross-minus-withholding').textContent) || 0;
        
        totalGrossPurchases += grossPurchases;
        totalNetPurchases += netPurchases;
        totalInputTax += inputTax;
        totalWithholding += withholdingAmount;
        totalGrossMinusWithholding += grossMinusWithholding;
    });
    
    const summaryRow = document.getElementById('summaryRow');
    summaryRow.innerHTML = `
        <tr class="summary-row">
            <td colspan="7" style="text-align: right; font-weight: bold; font-size: 14px;">TOTALS:</td>
            <td class="uneditable">${totalGrossPurchases.toFixed(2)}</td>
            <td class="uneditable">${totalNetPurchases.toFixed(2)}</td>
            <td class="uneditable">${totalInputTax.toFixed(2)}</td>
            <td>-</td>
            <td class="uneditable">${totalWithholding.toFixed(2)}</td>
            <td class="uneditable">${totalGrossMinusWithholding.toFixed(2)}</td>
            <td>-</td>
            <td>-</td>
        </tr>
    `;
}

// Function to update tally sections
function updateTallySections() {
    let totalCashAmount = 0;
    let totalChargeAmount = 0;
    let totalOnePercent = 0;
    let totalTwoPercent = 0;
    let totalFivePercent = 0;
    let totalZeroPercent = 0;
    
    let cashCount = 0;
    let chargeCount = 0;
    let onePercentCount = 0;
    let twoPercentCount = 0;
    let fivePercentCount = 0;
    let zeroPercentCount = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        const paymentMode = row.querySelector('.payment-mode').value;
        const withholdingRate = parseFloat(row.querySelector('.withholding-rate').value) || 0;
        const netPurchases = parseFloat(row.querySelector('.net-purchases').textContent) || 0;
        const withholdingAmount = parseFloat(row.querySelector('.withholding-amount').textContent) || 0;
        
        // Count by payment mode
        if (paymentMode === 'Cash') {
            totalCashAmount += netPurchases;
            cashCount++;
        } else if (paymentMode === 'Charge') {
            totalChargeAmount += netPurchases;
            chargeCount++;
        }
        
        // Count by withholding tax rate
        if (withholdingRate === 0.01) { // 1%
            totalOnePercent += withholdingAmount;
            onePercentCount++;
        } else if (withholdingRate === 0.02) { // 2%
            totalTwoPercent += withholdingAmount;
            twoPercentCount++;
        } else if (withholdingRate === 0.05) { // 5%
            totalFivePercent += withholdingAmount;
            fivePercentCount++;
        } else if (withholdingRate === 0) { // 0%
            totalZeroPercent += netPurchases; // Use net purchases for 0% rate
            zeroPercentCount++;
        }
    });
    
    // Update DOM elements
    document.getElementById('totalCashAmount').textContent = totalCashAmount.toFixed(2);
    document.getElementById('totalChargeAmount').textContent = totalChargeAmount.toFixed(2);
    document.getElementById('totalOnePercent').textContent = totalOnePercent.toFixed(2);
    document.getElementById('totalTwoPercent').textContent = totalTwoPercent.toFixed(2);
    document.getElementById('totalFivePercent').textContent = totalFivePercent.toFixed(2);
    document.getElementById('totalZeroPercent').textContent = totalZeroPercent.toFixed(2);
    
    document.getElementById('cashCount').textContent = `${cashCount} transaction${cashCount !== 1 ? 's' : ''}`;
    document.getElementById('chargeCount').textContent = `${chargeCount} transaction${chargeCount !== 1 ? 's' : ''}`;
    document.getElementById('onePercentCount').textContent = `${onePercentCount} transaction${onePercentCount !== 1 ? 's' : ''}`;
    document.getElementById('twoPercentCount').textContent = `${twoPercentCount} transaction${twoPercentCount !== 1 ? 's' : ''}`;
    document.getElementById('fivePercentCount').textContent = `${fivePercentCount} transaction${fivePercentCount !== 1 ? 's' : ''}`;
    document.getElementById('zeroPercentCount').textContent = `${zeroPercentCount} transaction${zeroPercentCount !== 1 ? 's' : ''}`;
    
    // Calculate percentages
    const totalTallyAmount = totalCashAmount + totalChargeAmount;
    const totalTransactions = rows.length;
    
    const cashPercentage = totalTallyAmount > 0 ? ((totalCashAmount / totalTallyAmount) * 100).toFixed(1) : 0;
    const chargePercentage = totalTallyAmount > 0 ? ((totalChargeAmount / totalTallyAmount) * 100).toFixed(1) : 0;
    
    // Update payment mode percentages
    document.getElementById('cashPercentage').textContent = `${cashPercentage}% of total`;
    document.getElementById('chargePercentage').textContent = `${chargePercentage}% of total`;
    
    // Update summary
    document.getElementById('totalTallyAmount').textContent = `₱ ${totalTallyAmount.toFixed(2)}`;
    document.getElementById('totalTallyCount').textContent = `${totalTransactions} total transaction${totalTransactions !== 1 ? 's' : ''}`;
    
    const totalWithholding = parseFloat(document.getElementById('totalWithholding').textContent) || 0;
    document.getElementById('totalWithholdingTally').textContent = totalWithholding.toFixed(2);
}

// Function to export to Excel (XLS format) with improved design
function exportToExcel() {
    // Validate required company info
    const companyName = document.getElementById('companyName').value.trim();
    const companyTIN = document.getElementById('companyTIN').value.trim();
    const companyAddress = document.getElementById('companyAddress').value.trim();
    const lineOfBusiness = document.getElementById('lineOfBusiness').value.trim();
    const authorizedEmployee = document.getElementById('authorizedEmployee').value.trim();
    
    if (!companyName || !companyTIN || !companyAddress || !lineOfBusiness || !authorizedEmployee) {
        alert('Please fill in all required company information fields (marked with *) before exporting.');
        return;
    }
    
    // Create HTML content for Excel with professional styling
    const today = new Date();
    const formattedDate = today.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    const formattedTime = today.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute:'2-digit', 
        hour12: true 
    });
    
    // Calculate totals for the report
    const totalGrossPurchases = parseFloat(document.getElementById('totalGrossPurchases').textContent) || 0;
    const totalNetPurchases = parseFloat(document.getElementById('totalNetPurchases').textContent) || 0;
    const totalInputTax = parseFloat(document.getElementById('totalInputTax').textContent) || 0;
    const totalWithholding = parseFloat(document.getElementById('totalWithholding').textContent) || 0;
    const netPayable = parseFloat(document.getElementById('netPayable').textContent) || 0;
    
    // Calculate tally data for export
    let totalCashAmount = 0;
    let totalChargeAmount = 0;
    let totalOnePercent = 0;
    let totalTwoPercent = 0;
    let totalFivePercent = 0;
    let totalZeroPercent = 0;
    let cashCount = 0;
    let chargeCount = 0;
    let onePercentCount = 0;
    let twoPercentCount = 0;
    let fivePercentCount = 0;
    let zeroPercentCount = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const paymentMode = row.querySelector('.payment-mode').value;
        const withholdingRate = parseFloat(row.querySelector('.withholding-rate').value) || 0;
        const netPurchases = parseFloat(row.querySelector('.net-purchases').textContent) || 0;
        const withholdingAmount = parseFloat(row.querySelector('.withholding-amount').textContent) || 0;
        
        if (paymentMode === 'Cash') {
            totalCashAmount += netPurchases;
            cashCount++;
        } else if (paymentMode === 'Charge') {
            totalChargeAmount += netPurchases;
            chargeCount++;
        }
        
        if (withholdingRate === 0.01) {
            totalOnePercent += withholdingAmount;
            onePercentCount++;
        } else if (withholdingRate === 0.02) {
            totalTwoPercent += withholdingAmount;
            twoPercentCount++;
        } else if (withholdingRate === 0.05) {
            totalFivePercent += withholdingAmount;
            fivePercentCount++;
        } else if (withholdingRate === 0) {
            totalZeroPercent += netPurchases;
            zeroPercentCount++;
        }
    });
    
    let htmlContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Vatable Purchases Report</x:Name>
                            <x:WorksheetOptions>
                                <x:DisplayGridlines/>
                                <x:Print>
                                    <x:ValidPrinterInfo/>
                                    <x:PaperSizeIndex>9</x:PaperSizeIndex>
                                    <x:HorizontalResolution>600</x:HorizontalResolution>
                                    <x:VerticalResolution>600</x:VerticalResolution>
                                    <x:FitToPage>1</x:FitToPage>
                                    <x:FitWidth>1</x:FitWidth>
                                    <x:FitHeight>100</x:FitHeight>
                                </x:Print>
                                <x:Selected/>
                                <x:ProtectContents>False</x:ProtectContents>
                                <x:ProtectObjects>False</x:ProtectObjects>
                                <x:ProtectScenarios>False</x:ProtectScenarios>
                                <x:FreezePanes/>
                                <x:FrozenNoSplit/>
                                <x:SplitHorizontal>6</x:SplitHorizontal>
                                <x:TopRowBottomPane>6</x:TopRowBottomPane>
                                <x:ActivePane>2</x:ActivePane>
                                <x:Panes>
                                    <x:Pane>
                                        <x:Number>3</x:Number>
                                    </x:Pane>
                                    <x:Pane>
                                        <x:Number>2</x:Number>
                                        <x:ActiveRow>0</x:ActiveRow>
                                        <x:ActiveCol>5</x:ActiveCol>
                                    </x:Pane>
                                </x:Panes>
                            </x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                    <x:WindowHeight>9000</x:WindowHeight>
                    <x:WindowWidth>15360</x:WindowWidth>
                    <x:WindowTopX>0</x:WindowTopX>
                    <x:WindowTopY>0</x:WindowTopY>
                    <x:ProtectStructure>False</x:ProtectStructure>
                    <x:ProtectWindows>False</x:ProtectWindows>
                </x:ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                * {
                    font-family: 'Calibri', 'Arial', sans-serif;
                    mso-font-charset: 0;
                }
                
                body {
                    margin: 20px;
                }
                
                .report-title {
                    font-size: 24pt;
                    font-weight: bold;
                    color: #006837;
                    text-align: center;
                    margin-bottom: 10px;
                    border-bottom: 3px solid #00cc7a;
                    padding-bottom: 10px;
                }
                
                .company-header {
                    background-color: #f0f9f5;
                    border: 2px solid #00cc7a;
                    padding: 15px;
                    margin-bottom: 20px;
                    border-radius: 8px;
                }
                
                .company-info-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .company-info-table td {
                    padding: 5px 10px;
                    vertical-align: top;
                }
                
                .company-label {
                    font-weight: bold;
                    color: #006837;
                    width: 180px;
                    white-space: nowrap;
                }
                
                .company-value {
                    color: #333333;
                }
                
                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 10pt;
                }
                
                .data-table th {
                    background-color: #006837;
                    color: white;
                    font-weight: bold;
                    text-align: center;
                    padding: 10px 6px;
                    border: 1px solid #004d26;
                    white-space: nowrap;
                    vertical-align: middle;
                }
                
                .data-table td {
                    padding: 8px 6px;
                    border: 1px solid #cccccc;
                    vertical-align: middle;
                }
                
                .text-left {
                    text-align: left;
                }
                
                .text-center {
                    text-align: center;
                }
                
                .text-right {
                    text-align: right;
                }
                
                .currency {
                    font-family: 'Consolas', 'Courier New', monospace;
                    text-align: right;
                    white-space: nowrap;
                }
                
                .numeric {
                    text-align: right;
                    mso-number-format: "#,##0.00";
                }
                
                .date-cell {
                    text-align: center;
                    white-space: nowrap;
                }
                
                .summary-row {
                    background-color: #fff2cc;
                    font-weight: bold;
                }
                
                .summary-row td {
                    border-top: 2px solid #ff9900;
                    border-bottom: 2px solid #ff9900;
                }
                
                .section-title {
                    font-size: 14pt;
                    font-weight: bold;
                    color: #006837;
                    margin-top: 25px;
                    margin-bottom: 10px;
                    padding-bottom: 5px;
                    border-bottom: 1px solid #00cc7a;
                }
                
                .totals-section {
                    margin-top: 25px;
                    background-color: #f0f9f5;
                    border: 1px solid #00cc7a;
                    padding: 15px;
                    border-radius: 8px;
                }
                
                .totals-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .totals-table td {
                    padding: 8px 15px;
                    border: none;
                }
                
                .total-label {
                    font-weight: bold;
                    color: #006837;
                    text-align: right;
                    width: 60%;
                }
                
                .total-value {
                    font-weight: bold;
                    color: #333333;
                    text-align: right;
                    font-family: 'Consolas', 'Courier New', monospace;
                    border-bottom: 1px dotted #cccccc;
                }
                
                .footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #cccccc;
                    color: #666666;
                    font-size: 9pt;
                    text-align: center;
                }
                
                .highlight {
                    background-color: #e6f7f0;
                }
                
                /* Column widths */
                .col-date { width: 80px; }
                .col-invoice { width: 100px; }
                .col-type { width: 80px; }
                .col-payment { width: 80px; }
                .col-tin { width: 130px; }
                .col-supplier { width: 150px; }
                .col-address { width: 170px; }
                .col-amount { width: 100px; }
                .col-net { width: 100px; }
                .col-tax { width: 90px; }
                .col-rate { width: 90px; }
                .col-withholding { width: 100px; }
                .col-net-payable { width: 120px; }
                .col-remarks { width: 120px; }
            </style>
            <title>Vatable Purchases Report - ${formattedDate}</title>
        </head>
        <body>
            <div class="report-title">VATABLE PURCHASES DATA REPORT</div>
            
            <div class="company-header">
                <table class="company-info-table">
                    <tr>
                        <td class="company-label">Company Name:</td>
                        <td class="company-value">${companyName}</td>
                        <td class="company-label">TIN Number:</td>
                        <td class="company-value">${companyTIN}</td>
                    </tr>
                    <tr>
                        <td class="company-label">Address:</td>
                        <td class="company-value">${companyAddress}</td>
                        <td class="company-label">Line of Business:</td>
                        <td class="company-value">${lineOfBusiness}</td>
                    </tr>
                    <tr>
                        <td class="company-label">Telephone:</td>
                        <td class="company-value">${document.getElementById('telephone').value.trim() || 'N/A'}</td>
                        <td class="company-label">Report Date:</td>
                        <td class="company-value">${document.getElementById('reportDate').value}</td>
                    </tr>
                    <tr>
                        <td class="company-label">Authorized Employee:</td>
                        <td class="company-value">${authorizedEmployee}</td>
                        <td class="company-label">Email:</td>
                        <td class="company-value">${document.getElementById('email').value.trim() || 'N/A'}</td>
                    </tr>
                </table>
            </div>
            
            <div class="section-title">Purchases Transactions</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-invoice">Invoice Number</th>
                        <th class="col-type">Invoice Type</th>
                        <th class="col-payment">Mode of Payment</th>
                        <th class="col-tin">TIN Number</th>
                        <th class="col-supplier">Supplier Name</th>
                        <th class="col-address">Address</th>
                        <th class="col-amount">Gross Purchases</th>
                        <th class="col-net">Net Purchases</th>
                        <th class="col-tax">Input Tax</th>
                        <th class="col-rate">W/Tax Rate</th>
                        <th class="col-withholding">W/Tax Amount</th>
                        <th class="col-net-payable">Gross - W/Tax</th>
                        <th class="col-remarks">Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Add data rows
    let rowNumber = 1;
    
    rows.forEach(row => {
        // Date
        const dateInput = row.querySelector('.date-input');
        const dateValue = dateInput ? dateInput.value : '';
        
        // Invoice Number
        const invoiceInput = row.querySelector('.invoice-number');
        const invoiceValue = invoiceInput ? invoiceInput.value : '';
        
        // Invoice Type
        const invoiceTypeSelect = row.querySelector('.invoice-type');
        const invoiceTypeValue = invoiceTypeSelect ? invoiceTypeSelect.value : '';
        
        // Mode of Payment
        const paymentModeSelect = row.querySelector('.payment-mode');
        const paymentModeValue = paymentModeSelect ? paymentModeSelect.value : '';
        
        // TIN Number
        const tinInput = row.querySelector('.tin-number');
        const tinValue = tinInput ? tinInput.value : '';
        
        // Supplier Name
        const supplierInput = row.querySelector('.supplier-name');
        const supplierValue = supplierInput ? supplierInput.value : '';
        
        // Address
        const addressInput = row.querySelector('.address');
        const addressValue = addressInput ? addressInput.value : '';
        
        // Gross Purchases
        const grossPurchasesInput = row.querySelector('.gross-purchases');
        const grossPurchasesValue = grossPurchasesInput ? parseFloat(grossPurchasesInput.value) || 0 : 0;
        
        // Net Purchases
        const netPurchasesDiv = row.querySelector('.net-purchases');
        const netPurchasesValue = netPurchasesDiv ? parseFloat(netPurchasesDiv.textContent) || 0 : 0;
        
        // Input Tax
        const inputTaxDiv = row.querySelector('.input-tax');
        const inputTaxValue = inputTaxDiv ? parseFloat(inputTaxDiv.textContent) || 0 : 0;
        
        // Withholding Tax Rate
        const withholdingRateSelect = row.querySelector('.withholding-rate');
        let rateValue = '0%';
        if (withholdingRateSelect) {
            const rate = parseFloat(withholdingRateSelect.value) || 0;
            rateValue = (rate * 100) + '%';
        }
        
        // Withholding Amount
        const withholdingAmountDiv = row.querySelector('.withholding-amount');
        const withholdingAmountValue = withholdingAmountDiv ? parseFloat(withholdingAmountDiv.textContent) || 0 : 0;
        
        // Gross - Withholding Tax
        const grossMinusWithholdingDiv = row.querySelector('.gross-minus-withholding');
        const grossMinusWithholdingValue = grossMinusWithholdingDiv ? parseFloat(grossMinusWithholdingDiv.textContent) || 0 : 0;
        
        // Remarks
        const remarksInput = row.querySelector('.remarks');
        const remarksValue = remarksInput ? remarksInput.value : '';
        
        // Alternate row background for readability
        const rowClass = rowNumber % 2 === 0 ? 'highlight' : '';
        
        htmlContent += `
            <tr class="${rowClass}">
                <td class="date-cell">${dateValue}</td>
                <td class="text-left">${invoiceValue}</td>
                <td class="text-center">${invoiceTypeValue}</td>
                <td class="text-center">${paymentModeValue}</td>
                <td class="text-center">${tinValue}</td>
                <td class="text-left">${supplierValue}</td>
                <td class="text-left">${addressValue}</td>
                <td class="currency numeric">${grossPurchasesValue.toFixed(2)}</td>
                <td class="currency numeric">${netPurchasesValue.toFixed(2)}</td>
                <td class="currency numeric">${inputTaxValue.toFixed(2)}</td>
                <td class="text-center">${rateValue}</td>
                <td class="currency numeric">${withholdingAmountValue.toFixed(2)}</td>
                <td class="currency numeric">${grossMinusWithholdingValue.toFixed(2)}</td>
                <td class="text-left">${remarksValue}</td>
            </tr>
        `;
        
        rowNumber++;
    });
    
    // Add summary row
    htmlContent += `
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="7" class="text-right" style="font-weight: bold;">TOTALS:</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalGrossPurchases.toFixed(2)}</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalNetPurchases.toFixed(2)}</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalInputTax.toFixed(2)}</td>
                        <td>-</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalWithholding.toFixed(2)}</td>
                        <td class="currency numeric" style="font-weight: bold;">${netPayable.toFixed(2)}</td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="totals-section">
                <div class="section-title">Financial Summary</div>
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Total Vatable Gross Purchases:</td>
                        <td class="total-value">₱ ${totalGrossPurchases.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Net Purchases:</td>
                        <td class="total-value">₱ ${totalNetPurchases.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Input Tax (12%):</td>
                        <td class="total-value">₱ ${totalInputTax.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Withholding Tax:</td>
                        <td class="total-value">₱ ${totalWithholding.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label" style="border-top: 1px solid #00cc7a; padding-top: 10px;">Net Amount Payable:</td>
                        <td class="total-value" style="border-top: 1px solid #00cc7a; padding-top: 10px; font-size: 11pt; color: #006837;">
                            ₱ ${netPayable.toFixed(2)}
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="totals-section" style="margin-top: 15px;">
                <div class="section-title">Payment & Withholding Tax Distribution</div>
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Cash Purchases:</td>
                        <td class="total-value">₱ ${totalCashAmount.toFixed(2)} (${cashCount} transaction${cashCount !== 1 ? 's' : ''})</td>
                    </tr>
                    <tr>
                        <td class="total-label">Charge Purchases:</td>
                        <td class="total-value">₱ ${totalChargeAmount.toFixed(2)} (${chargeCount} transaction${chargeCount !== 1 ? 's' : ''})</td>
                    </tr>
                    <tr>
                        <td class="total-label">1% Withholding Tax:</td>
                        <td class="total-value">₱ ${totalOnePercent.toFixed(2)} (${onePercentCount} transaction${onePercentCount !== 1 ? 's' : ''})</td>
                    </tr>
                    <tr>
                        <td class="total-label">2% Withholding Tax:</td>
                        <td class="total-value">₱ ${totalTwoPercent.toFixed(2)} (${twoPercentCount} transaction${twoPercentCount !== 1 ? 's' : ''})</td>
                    </tr>
                    <tr>
                        <td class="total-label">5% Withholding Tax:</td>
                        <td class="total-value">₱ ${totalFivePercent.toFixed(2)} (${fivePercentCount} transaction${fivePercentCount !== 1 ? 's' : ''})</td>
                    </tr>
                    <tr>
                        <td class="total-label">0% Withholding Tax:</td>
                        <td class="total-value">₱ ${totalZeroPercent.toFixed(2)} (${zeroPercentCount} transaction${zeroPercentCount !== 1 ? 's' : ''})</td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <p>Report generated on ${formattedDate} at ${formattedTime}</p>
                <p>Authorized by: ${authorizedEmployee} | Prepared by: Vatable Purchases Data Tracker System</p>
                <p>This is a computer-generated report. No signature is required.</p>
                <p>© ${new Date().getFullYear()} Vatable Purchases Data Tracker - Professional VAT-Compliant Purchases Tracking System</p>
            </div>
        </body>
        </html>
    `;
    
    // Create download link for XLS file
    const blob = new Blob([htmlContent], {type: 'application/vnd.ms-excel'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    const fileName = `Vatable_Purchases_Report_${companyName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().split('T')[0]}.xls`;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    setTimeout(() => {
        URL.revokeObjectURL(url);
    }, 100);
    
    // Show success message
    alert(`✅ Excel report "${fileName}" has been generated successfully!\n\nFeatures:\n• Professional formatting\n• Company header\n• Color-coded sections\n• Financial summary\n• Payment & withholding tax distribution\n• Readable column widths\n• Print-ready layout`);
}

// Add event listeners to existing rows (if any)
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('editable')) {
        const row = e.target.closest('tr');
        if (row) {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
            updateTallySections();
        }
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('editable')) {
        const row = e.target.closest('tr');
        if (row) {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
            updateTallySections();
        }
    }
});
