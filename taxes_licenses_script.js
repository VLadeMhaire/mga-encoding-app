// taxes_licenses_script.js - JavaScript for Taxes & Licenses page

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
    localStorage.setItem('taxesLicensesCompanyInfo', JSON.stringify(companyInfo));
}

// Load company info from localStorage
function loadCompanyInfo() {
    const savedInfo = localStorage.getItem('taxesLicensesCompanyInfo');
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
    localStorage.removeItem('taxesLicensesCompanyInfo');
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
            <input type="text" class="editable reference-number" placeholder="REF-001">
        </td>
        <td>
            <select class="editable tax-type">
                <option value="">Select</option>
                <option value="VAT">VAT</option>
                <option value="IT">Income Tax (IT)</option>
                <option value="DST">Documentary Stamp Tax (DST)</option>
                <option value="WT">Withholding Tax (WT)</option>
                <option value="LTO">Land Transportation Office (LTO)</option>
                <option value="BFP">Bureau of Fire Protection (BFP)</option>
                <option value="BPLO">Business Permit & Licensing Office (BPLO)</option>
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
            <input type="text" class="editable government-agency" placeholder="e.g., BIR, LGU, etc.">
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="editable amount" placeholder="0.00">
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
            updateTotals();
            updateSummaryRow();
        });
        
        input.addEventListener('change', function() {
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

// Function to update row count
function updateRowCount() {
    const tableBody = document.getElementById('tableBody');
    const rowCount = tableBody.children.length;
    document.getElementById('rowCount').textContent = rowCount;
}

// Function to update totals
function updateTotals() {
    let totalVAT = 0;
    let totalIT = 0;
    let totalLicenses = 0;
    let totalAmount = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const taxType = row.querySelector('.tax-type').value;
        const amount = parseFloat(row.querySelector('.amount').value) || 0;
        
        totalAmount += amount;
        
        // Categorize by tax type
        if (taxType === 'VAT') {
            totalVAT += amount;
        } else if (taxType === 'IT') {
            totalIT += amount;
        } else if (['LTO', 'BFP', 'BPLO', 'OTHERS'].includes(taxType)) {
            totalLicenses += amount;
        }
    });
    
    document.getElementById('totalVAT').textContent = totalVAT.toFixed(2);
    document.getElementById('totalIT').textContent = totalIT.toFixed(2);
    document.getElementById('totalLicenses').textContent = totalLicenses.toFixed(2);
    document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
}

// Function to update summary row
function updateSummaryRow() {
    let totalAmount = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const amount = parseFloat(row.querySelector('.amount').value) || 0;
        totalAmount += amount;
    });
    
    const summaryRow = document.getElementById('summaryRow');
    summaryRow.innerHTML = `
        <tr class="summary-row">
            <td colspan="5" style="text-align: right; font-weight: bold; font-size: 14px;">TOTALS:</td>
            <td class="uneditable">${totalAmount.toFixed(2)}</td>
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
    const totalVAT = parseFloat(document.getElementById('totalVAT').textContent) || 0;
    const totalIT = parseFloat(document.getElementById('totalIT').textContent) || 0;
    const totalLicenses = parseFloat(document.getElementById('totalLicenses').textContent) || 0;
    const totalAmount = parseFloat(document.getElementById('totalAmount').textContent) || 0;
    
    let htmlContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Taxes & Licenses Report</x:Name>
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
                .col-ref { width: 120px; }
                .col-type { width: 120px; }
                .col-payment { width: 100px; }
                .col-agency { width: 150px; }
                .col-amount { width: 100px; }
                .col-remarks { width: 150px; }
            </style>
            <title>Taxes & Licenses Report - ${formattedDate}</title>
        </head>
        <body>
            <div class="report-title">TAXES & LICENSES DATA REPORT</div>
            
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
            
            <div class="section-title">Taxes & Licenses Payments</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-ref">Reference Number</th>
                        <th class="col-type">Tax Type</th>
                        <th class="col-payment">Mode of Payment</th>
                        <th class="col-agency">Government Agency</th>
                        <th class="col-amount">Amount</th>
                        <th class="col-remarks">Remarks</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Add data rows
    const rows = document.querySelectorAll('#tableBody tr');
    let rowNumber = 1;
    
    rows.forEach(row => {
        // Date
        const dateInput = row.querySelector('.date-input');
        const dateValue = dateInput ? dateInput.value : '';
        
        // Reference Number
        const referenceInput = row.querySelector('.reference-number');
        const referenceValue = referenceInput ? referenceInput.value : '';
        
        // Tax Type
        const taxTypeSelect = row.querySelector('.tax-type');
        const taxTypeValue = taxTypeSelect ? taxTypeSelect.value : '';
        
        // Mode of Payment
        const paymentModeSelect = row.querySelector('.payment-mode');
        const paymentModeValue = paymentModeSelect ? paymentModeSelect.value : '';
        
        // Government Agency
        const agencyInput = row.querySelector('.government-agency');
        const agencyValue = agencyInput ? agencyInput.value : '';
        
        // Amount
        const amountInput = row.querySelector('.amount');
        const amountValue = amountInput ? parseFloat(amountInput.value) || 0 : 0;
        
        // Remarks
        const remarksInput = row.querySelector('.remarks');
        const remarksValue = remarksInput ? remarksInput.value : '';
        
        // Alternate row background for readability
        const rowClass = rowNumber % 2 === 0 ? 'highlight' : '';
        
        htmlContent += `
            <tr class="${rowClass}">
                <td class="date-cell">${dateValue}</td>
                <td class="text-left">${referenceValue}</td>
                <td class="text-center">${taxTypeValue}</td>
                <td class="text-center">${paymentModeValue}</td>
                <td class="text-left">${agencyValue}</td>
                <td class="currency numeric">${amountValue.toFixed(2)}</td>
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
                        <td colspan="5" class="text-right" style="font-weight: bold;">TOTALS:</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalAmount.toFixed(2)}</td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="totals-section">
                <div class="section-title">Financial Summary</div>
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Total VAT Payments:</td>
                        <td class="total-value">₱ ${totalVAT.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Income Tax Payments:</td>
                        <td class="total-value">₱ ${totalIT.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total License Fees:</td>
                        <td class="total-value">₱ ${totalLicenses.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label" style="border-top: 1px solid #00cc7a; padding-top: 10px;">Total Amount Paid:</td>
                        <td class="total-value" style="border-top: 1px solid #00cc7a; padding-top: 10px; font-size: 11pt; color: #006837;">
                            ₱ ${totalAmount.toFixed(2)}
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <p>Report generated on ${formattedDate} at ${formattedTime}</p>
                <p>Authorized by: ${authorizedEmployee} | Prepared by: Taxes & Licenses Data Tracker System</p>
                <p>This is a computer-generated report. No signature is required.</p>
                <p>© ${new Date().getFullYear()} Taxes & Licenses Data Tracker - Professional Tax and Government License Payment Tracking System</p>
            </div>
        </body>
        </html>
    `;
    
    // Create download link for XLS file
    const blob = new Blob([htmlContent], {type: 'application/vnd.ms-excel'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    const fileName = `Taxes_Licenses_Report_${companyName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().split('T')[0]}.xls`;
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

// Add event listeners to existing rows (if any)
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('editable')) {
        updateTotals();
        updateSummaryRow();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('editable')) {
        updateTotals();
        updateSummaryRow();
    }
});