// purchase_returns_script.js - JavaScript for Purchase Returns page

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
    localStorage.setItem('purchaseReturnsCompanyInfo', JSON.stringify(companyInfo));
}

// Load company info from localStorage
function loadCompanyInfo() {
    const savedInfo = localStorage.getItem('purchaseReturnsCompanyInfo');
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
    localStorage.removeItem('purchaseReturnsCompanyInfo');
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
            <input type="date" value="${today}" class="editable return-date">
        </td>
        <td>
            <input type="text" class="editable return-number" placeholder="RTN-001">
        </td>
        <td>
            <input type="text" class="editable original-invoice" placeholder="Original INV-001">
        </td>
        <td>
            <select class="editable return-mode">
                <option value="">Select</option>
                <option value="Cash Refund">Cash Refund</option>
                <option value="Credit Memo">Credit Memo</option>
                <option value="Exchange">Exchange</option>
                <option value="Store Credit">Store Credit</option>
                <option value="Others">Others</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="editable gross-returns" placeholder="0.00">
        </td>
        <td>
            <div class="uneditable net-returns">0.00</div>
        </td>
        <td>
            <div class="uneditable input-tax-return">0.00</div>
        </td>
        <td>
            <input type="text" class="editable remarks" placeholder="Enter return reason">
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
        });
        
        input.addEventListener('change', function() {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
        });
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
}

// Function to update calculations for a row
function updateCalculations(row) {
    const grossReturns = parseFloat(row.querySelector('.gross-returns').value) || 0;
    
    // Calculate net purchase returns: Gross Purchase Returns / 1.12
    const netReturns = grossReturns / 1.12;
    row.querySelector('.net-returns').textContent = netReturns.toFixed(2);
    
    // Calculate input tax return: Net Purchase Returns * 0.12
    const inputTaxReturn = netReturns * 0.12;
    row.querySelector('.input-tax-return').textContent = inputTaxReturn.toFixed(2);
}

// Function to format TIN input (for company TIN)
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

// Function to update row count
function updateRowCount() {
    const tableBody = document.getElementById('tableBody');
    const rowCount = tableBody.children.length;
    document.getElementById('rowCount').textContent = rowCount;
}

// Function to update totals
function updateTotals() {
    let totalGrossReturns = 0;
    let totalNetReturns = 0;
    let totalInputTaxReturn = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const grossReturns = parseFloat(row.querySelector('.gross-returns').value) || 0;
        const netReturns = parseFloat(row.querySelector('.net-returns').textContent) || 0;
        const inputTaxReturn = parseFloat(row.querySelector('.input-tax-return').textContent) || 0;
        
        totalGrossReturns += grossReturns;
        totalNetReturns += netReturns;
        totalInputTaxReturn += inputTaxReturn;
    });
    
    document.getElementById('totalGrossReturns').textContent = totalGrossReturns.toFixed(2);
    document.getElementById('totalNetReturns').textContent = totalNetReturns.toFixed(2);
    document.getElementById('totalInputTaxReturn').textContent = totalInputTaxReturn.toFixed(2);
    
    // Net Refund Amount = Total Gross Returns
    document.getElementById('netRefundAmount').textContent = totalGrossReturns.toFixed(2);
}

// Function to update summary row
function updateSummaryRow() {
    let totalGrossReturns = 0;
    let totalNetReturns = 0;
    let totalInputTaxReturn = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const grossReturns = parseFloat(row.querySelector('.gross-returns').value) || 0;
        const netReturns = parseFloat(row.querySelector('.net-returns').textContent) || 0;
        const inputTaxReturn = parseFloat(row.querySelector('.input-tax-return').textContent) || 0;
        
        totalGrossReturns += grossReturns;
        totalNetReturns += netReturns;
        totalInputTaxReturn += inputTaxReturn;
    });
    
    const summaryRow = document.getElementById('summaryRow');
    summaryRow.innerHTML = `
        <tr class="summary-row">
            <td colspan="4" style="text-align: right; font-weight: bold; font-size: 14px;">TOTALS:</td>
            <td class="uneditable">${totalGrossReturns.toFixed(2)}</td>
            <td class="uneditable">${totalNetReturns.toFixed(2)}</td>
            <td class="uneditable">${totalInputTaxReturn.toFixed(2)}</td>
            <td>-</td>
            <td>-</td>
        </tr>
    `;
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
    const totalGrossReturns = parseFloat(document.getElementById('totalGrossReturns').textContent) || 0;
    const totalNetReturns = parseFloat(document.getElementById('totalNetReturns').textContent) || 0;
    const totalInputTaxReturn = parseFloat(document.getElementById('totalInputTaxReturn').textContent) || 0;
    const netRefundAmount = parseFloat(document.getElementById('netRefundAmount').textContent) || 0;
    
    let htmlContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Purchase Returns Report</x:Name>
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
                .col-date { width: 90px; }
                .col-return { width: 120px; }
                .col-invoice { width: 130px; }
                .col-return-mode { width: 120px; }
                .col-amount { width: 120px; }
                .col-net { width: 120px; }
                .col-tax { width: 120px; }
                .col-remarks { width: 150px; }
            </style>
            <title>Purchase Returns Report - ${formattedDate}</title>
        </head>
        <body>
            <div class="report-title">PURCHASE RETURNS DATA REPORT</div>
            
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
            
            <div class="section-title">Purchase Returns Transactions</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-date">Return Date</th>
                        <th class="col-return">Return Number</th>
                        <th class="col-invoice">Original Invoice Number</th>
                        <th class="col-return-mode">Mode of Return</th>
                        <th class="col-amount">Gross Purchase Returns</th>
                        <th class="col-net">Net Purchase Returns</th>
                        <th class="col-tax">Input Tax Return</th>
                        <th class="col-remarks">Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Add data rows
    const rows = document.querySelectorAll('#tableBody tr');
    let rowNumber = 1;
    
    rows.forEach(row => {
        // Return Date
        const returnDateInput = row.querySelector('.return-date');
        const returnDateValue = returnDateInput ? returnDateInput.value : '';
        
        // Return Number
        const returnNumberInput = row.querySelector('.return-number');
        const returnNumberValue = returnNumberInput ? returnNumberInput.value : '';
        
        // Original Invoice Number
        const originalInvoiceInput = row.querySelector('.original-invoice');
        const originalInvoiceValue = originalInvoiceInput ? originalInvoiceInput.value : '';
        
        // Mode of Return
        const returnModeSelect = row.querySelector('.return-mode');
        const returnModeValue = returnModeSelect ? returnModeSelect.value : '';
        
        // Gross Purchase Returns
        const grossReturnsInput = row.querySelector('.gross-returns');
        const grossReturnsValue = grossReturnsInput ? parseFloat(grossReturnsInput.value) || 0 : 0;
        
        // Net Purchase Returns
        const netReturnsDiv = row.querySelector('.net-returns');
        const netReturnsValue = netReturnsDiv ? parseFloat(netReturnsDiv.textContent) || 0 : 0;
        
        // Input Tax Return
        const inputTaxReturnDiv = row.querySelector('.input-tax-return');
        const inputTaxReturnValue = inputTaxReturnDiv ? parseFloat(inputTaxReturnDiv.textContent) || 0 : 0;
        
        // Remarks
        const remarksInput = row.querySelector('.remarks');
        const remarksValue = remarksInput ? remarksInput.value : '';
        
        // Alternate row background for readability
        const rowClass = rowNumber % 2 === 0 ? 'highlight' : '';
        
        htmlContent += `
            <tr class="${rowClass}">
                <td class="date-cell">${returnDateValue}</td>
                <td class="text-left">${returnNumberValue}</td>
                <td class="text-left">${originalInvoiceValue}</td>
                <td class="text-center">${returnModeValue}</td>
                <td class="currency numeric">${grossReturnsValue.toFixed(2)}</td>
                <td class="currency numeric">${netReturnsValue.toFixed(2)}</td>
                <td class="currency numeric">${inputTaxReturnValue.toFixed(2)}</td>
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
                        <td colspan="4" class="text-right" style="font-weight: bold;">TOTALS:</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalGrossReturns.toFixed(2)}</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalNetReturns.toFixed(2)}</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalInputTaxReturn.toFixed(2)}</td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="totals-section">
                <div class="section-title">Financial Summary</div>
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Total Gross Purchase Returns:</td>
                        <td class="total-value">₱ ${totalGrossReturns.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Net Purchase Returns:</td>
                        <td class="total-value">₱ ${totalNetReturns.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Input Tax Return (12%):</td>
                        <td class="total-value">₱ ${totalInputTaxReturn.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label" style="border-top: 1px solid #00cc7a; padding-top: 10px;">Net Refund Amount:</td>
                        <td class="total-value" style="border-top: 1px solid #00cc7a; padding-top: 10px; font-size: 11pt; color: #006837;">
                            ₱ ${netRefundAmount.toFixed(2)}
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <p>Report generated on ${formattedDate} at ${formattedTime}</p>
                <p>Authorized by: ${authorizedEmployee} | Prepared by: Purchase Returns Data Tracker System</p>
                <p>This is a computer-generated report. No signature is required.</p>
                <p>© ${new Date().getFullYear()} Purchase Returns Data Tracker - Professional Purchase Returns Tracking System</p>
            </div>
        </body>
        </html>
    `;
    
    // Create download link for XLS file
    const blob = new Blob([htmlContent], {type: 'application/vnd.ms-excel'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    const fileName = `Purchase_Returns_Report_${companyName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().split('T')[0]}.xls`;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    setTimeout(() => {
        URL.revokeObjectURL(url);
    }, 100);
    
    // Show success message
    alert(`✅ Excel report "${fileName}" has been generated successfully!\n\nFeatures:\n• Professional formatting\n• Company header\n• Color-coded sections\n• Financial summary\n• Readable column widths\n• Print-ready layout`);
}

// Add event listeners to existing rows (if any)
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('editable')) {
        const row = e.target.closest('tr');
        if (row) {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
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
        }
    }
});