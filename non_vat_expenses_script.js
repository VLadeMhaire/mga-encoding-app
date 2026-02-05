// non_vat_expenses_script.js - JavaScript for Non-Vatable Expenses page

// Expense categories with their withholding tax rates
const expenseCategories = {
    // NonVat (0%)
    "SALARIES AND WAGES": 0.00,
    "SSS/PHILHEALTH/PAG-IBIG": 0.00,
    
    // Goods (1%)
    "REPAIRS AND MAINTENANCE-MATERIALS": 0.01,
    "MEALS AND ALLOWANCES – GOODS": 0.01,
    "REPRESENTATIONS – GOODS": 0.01,
    "FUEL AND OIL": 0.01,
    "OFFICE SUPPLIES": 0.01,
    "UNIFORMS": 0.01,
    "MEDICAL EXPENSES – GOODS": 0.01,
    "CLEANING SUPPLIES": 0.01,
    "OTHERS - GOODS": 0.01,
    
    // Services (2%)
    "REPAIRS AND MAINTENANCE-LABOR": 0.02,
    "MEALS AND ACCOMMODATION – SERVICES": 0.02,
    "REPRESENTATIONS – SERVICES": 0.02,
    "ELECTRICITY AND WATER": 0.02,
    "COMMUNICATION EXPENSE": 0.02,
    "FREIGHT AND COURIER": 0.02,
    "TRAVEL AND TRANSPORTATION": 0.02,
    "ADVERTISING AND MARKETING": 0.02,
    "SECURITY SERVICES": 0.02,
    "INSURANCE": 0.02,
    "SEMINAR AND TRAINING": 0.02,
    "MEDICAL EXPENSES – SERVICES": 0.02,
    "PROFESSIONAL FEES": 0.02,
    "CLEANING SERVICES": 0.02,
    "OTHERS - SERVICES": 0.02,
    
    // Rental (5%)
    "Rental": 0.05
};

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
    updateExpenseTally();
    
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
    localStorage.setItem('nonVatableExpensesCompanyInfo', JSON.stringify(companyInfo));
}

// Load company info from localStorage
function loadCompanyInfo() {
    const savedInfo = localStorage.getItem('nonVatableExpensesCompanyInfo');
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
    localStorage.removeItem('nonVatableExpensesCompanyInfo');
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
    
    // Create nature of expense options
    let natureOptions = '<option value="">Select Nature of Expense</option>';
    
    // Add NonVat category
    natureOptions += '<optgroup label="NonVat (0%)">';
    natureOptions += '<option value="SALARIES AND WAGES">SALARIES AND WAGES</option>';
    natureOptions += '<option value="SSS/PHILHEALTH/PAG-IBIG">SSS/PHILHEALTH/PAG-IBIG</option>';
    natureOptions += '</optgroup>';
    
    // Add Goods category
    natureOptions += '<optgroup label="Goods (1%)">';
    natureOptions += '<option value="REPAIRS AND MAINTENANCE-MATERIALS">REPAIRS AND MAINTENANCE-MATERIALS</option>';
    natureOptions += '<option value="MEALS AND ALLOWANCES – GOODS">MEALS AND ALLOWANCES – GOODS</option>';
    natureOptions += '<option value="REPRESENTATIONS – GOODS">REPRESENTATIONS – GOODS</option>';
    natureOptions += '<option value="FUEL AND OIL">FUEL AND OIL</option>';
    natureOptions += '<option value="OFFICE SUPPLIES">OFFICE SUPPLIES</option>';
    natureOptions += '<option value="UNIFORMS">UNIFORMS</option>';
    natureOptions += '<option value="MEDICAL EXPENSES – GOODS">MEDICAL EXPENSES – GOODS</option>';
    natureOptions += '<option value="CLEANING SUPPLIES">CLEANING SUPPLIES</option>';
    natureOptions += '<option value="OTHERS - GOODS">OTHERS - GOODS</option>';
    natureOptions += '</optgroup>';
    
    // Add Services category
    natureOptions += '<optgroup label="Services (2%)">';
    natureOptions += '<option value="REPAIRS AND MAINTENANCE-LABOR">REPAIRS AND MAINTENANCE-LABOR</option>';
    natureOptions += '<option value="MEALS AND ACCOMMODATION – SERVICES">MEALS AND ACCOMMODATION – SERVICES</option>';
    natureOptions += '<option value="REPRESENTATIONS – SERVICES">REPRESENTATIONS – SERVICES</option>';
    natureOptions += '<option value="ELECTRICITY AND WATER">ELECTRICITY AND WATER</option>';
    natureOptions += '<option value="COMMUNICATION EXPENSE">COMMUNICATION EXPENSE</option>';
    natureOptions += '<option value="FREIGHT AND COURIER">FREIGHT AND COURIER</option>';
    natureOptions += '<option value="TRAVEL AND TRANSPORTATION">TRAVEL AND TRANSPORTATION</option>';
    natureOptions += '<option value="ADVERTISING AND MARKETING">ADVERTISING AND MARKETING</option>';
    natureOptions += '<option value="SECURITY SERVICES">SECURITY SERVICES</option>';
    natureOptions += '<option value="INSURANCE">INSURANCE</option>';
    natureOptions += '<option value="SEMINAR AND TRAINING">SEMINAR AND TRAINING</option>';
    natureOptions += '<option value="MEDICAL EXPENSES – SERVICES">MEDICAL EXPENSES – SERVICES</option>';
    natureOptions += '<option value="PROFESSIONAL FEES">PROFESSIONAL FEES</option>';
    natureOptions += '<option value="CLEANING SERVICES">CLEANING SERVICES</option>';
    natureOptions += '<option value="OTHERS - SERVICES">OTHERS - SERVICES</option>';
    natureOptions += '</optgroup>';
    
    // Add Rental category
    natureOptions += '<optgroup label="Rental (5%)">';
    natureOptions += '<option value="Rental">Rental</option>';
    natureOptions += '</optgroup>';
    
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
            <select class="editable transaction-type">
                <option value="">Select</option>
                <option value="Goods">Goods</option>
                <option value="Services">Services</option>
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
            <input type="number" step="0.01" min="0" class="editable gross-amount" placeholder="0.00">
        </td>
        <td>
            <select class="editable nature-expense">
                ${natureOptions}
            </select>
        </td>
        <td>
            <div class="uneditable withholding-rate">0%</div>
        </td>
        <td>
            <div class="uneditable withholding-tax">0.00</div>
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
        });
        
        input.addEventListener('change', function() {
            updateCalculations(row);
            updateTotals();
            updateSummaryRow();
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
    const grossAmount = parseFloat(row.querySelector('.gross-amount').value) || 0;
    const natureExpense = row.querySelector('.nature-expense').value;
    
    // Get withholding tax rate based on nature of expense
    let withholdingRate = 0;
    if (natureExpense && expenseCategories[natureExpense] !== undefined) {
        withholdingRate = expenseCategories[natureExpense];
    }
    
    // Display withholding tax rate as percentage
    const ratePercentage = (withholdingRate * 100).toFixed(0) + '%';
    row.querySelector('.withholding-rate').textContent = ratePercentage;
    
    // For non-vatable expenses: Withholding Tax = Gross Amount * Withholding Tax Rate
    const withholdingTax = grossAmount * withholdingRate;
    row.querySelector('.withholding-tax').textContent = withholdingTax.toFixed(2);
    
    // Calculate Gross Amount minus Withholding Tax
    const grossMinusWithholding = grossAmount - withholdingTax;
    row.querySelector('.gross-minus-withholding').textContent = grossMinusWithholding.toFixed(2);
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
    let totalGrossAmount = 0;
    let totalWithholdingTax = 0;
    let totalGrossMinusWithholding = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const grossAmount = parseFloat(row.querySelector('.gross-amount').value) || 0;
        const withholdingTax = parseFloat(row.querySelector('.withholding-tax').textContent) || 0;
        const grossMinusWithholding = parseFloat(row.querySelector('.gross-minus-withholding').textContent) || 0;
        
        totalGrossAmount += grossAmount;
        totalWithholdingTax += withholdingTax;
        totalGrossMinusWithholding += grossMinusWithholding;
    });
    
    document.getElementById('totalGrossAmount').textContent = totalGrossAmount.toFixed(2);
    document.getElementById('totalWithholdingTax').textContent = totalWithholdingTax.toFixed(2);
    document.getElementById('netPayable').textContent = totalGrossMinusWithholding.toFixed(2);
    
    // Update the expense tally
    updateExpenseTally();
}

// Function to update summary row
function updateSummaryRow() {
    let totalGrossAmount = 0;
    let totalWithholdingTax = 0;
    let totalGrossMinusWithholding = 0;
    
    const rows = document.querySelectorAll('#tableBody tr');
    rows.forEach(row => {
        const grossAmount = parseFloat(row.querySelector('.gross-amount').value) || 0;
        const withholdingTax = parseFloat(row.querySelector('.withholding-tax').textContent) || 0;
        const grossMinusWithholding = parseFloat(row.querySelector('.gross-minus-withholding').textContent) || 0;
        
        totalGrossAmount += grossAmount;
        totalWithholdingTax += withholdingTax;
        totalGrossMinusWithholding += grossMinusWithholding;
    });
    
    const summaryRow = document.getElementById('summaryRow');
    summaryRow.innerHTML = `
        <tr class="summary-row">
            <td colspan="7" style="text-align: right; font-weight: bold; font-size: 14px;">TOTALS:</td>
            <td class="uneditable">${totalGrossAmount.toFixed(2)}</td>
            <td>-</td>
            <td>-</td>
            <td class="uneditable">${totalWithholdingTax.toFixed(2)}</td>
            <td class="uneditable">${totalGrossMinusWithholding.toFixed(2)}</td>
            <td>-</td>
            <td>-</td>
        </tr>
    `;
}

// Function to calculate and update expense tally
function updateExpenseTally() {
    const tallyGrid = document.getElementById('expenseTally');
    const rows = document.querySelectorAll('#tableBody tr');
    
    // Create a map to store tally data
    const expenseTally = {};
    let totalGrossAmount = 0;
    let totalRows = 0;
    
    // Initialize categories with zero values
    Object.keys(expenseCategories).forEach(category => {
        expenseTally[category] = {
            count: 0,
            totalGrossAmount: 0,
            totalWithholdingTax: 0,
            rate: expenseCategories[category]
        };
    });
    
    // Process each row
    rows.forEach(row => {
        const grossAmountInput = row.querySelector('.gross-amount');
        const natureExpenseSelect = row.querySelector('.nature-expense');
        const withholdingTaxDiv = row.querySelector('.withholding-tax');
        
        if (grossAmountInput && natureExpenseSelect) {
            const grossAmount = parseFloat(grossAmountInput.value) || 0;
            const natureExpense = natureExpenseSelect.value;
            const withholdingTax = parseFloat(withholdingTaxDiv.textContent) || 0;
            
            if (natureExpense && expenseCategories[natureExpense] !== undefined) {
                expenseTally[natureExpense].count++;
                expenseTally[natureExpense].totalGrossAmount += grossAmount;
                expenseTally[natureExpense].totalWithholdingTax += withholdingTax;
                
                totalGrossAmount += grossAmount;
                totalRows++;
            }
        }
    });
    
    // Clear and rebuild tally display
    tallyGrid.innerHTML = '';
    
    // Sort categories by total amount (descending)
    const sortedCategories = Object.keys(expenseTally)
        .filter(category => expenseTally[category].count > 0)
        .sort((a, b) => expenseTally[b].totalGrossAmount - expenseTally[a].totalGrossAmount);
    
    // Add tally items for each category with expenses
    sortedCategories.forEach(category => {
        const data = expenseTally[category];
        
        // Determine category type for styling
        let categoryType = '';
        if (category === 'SALARIES AND WAGES' || category === 'SSS/PHILHEALTH/PAG-IBIG') {
            categoryType = 'nonvat';
        } else if (category.includes('SERVICES') || category === 'Rental') {
            categoryType = category === 'Rental' ? 'rental' : 'services';
        } else {
            categoryType = 'goods';
        }
        
        const tallyItem = document.createElement('div');
        tallyItem.className = `tally-item ${categoryType}`;
        
        // Format category name for display
        const displayCategory = category.replace(/–/g, '-').replace(/ {2,}/g, ' ');
        
        // Get rate percentage
        const ratePercentage = (data.rate * 100).toFixed(0);
        
        // Get icon based on category type
        let icon = '';
        if (categoryType === 'nonvat') {
            icon = 'fa-file-invoice-dollar';
        } else if (categoryType === 'services') {
            icon = 'fa-cogs';
        } else if (categoryType === 'goods') {
            icon = 'fa-box';
        } else if (categoryType === 'rental') {
            icon = 'fa-building';
        }
        
        tallyItem.innerHTML = `
            <div class="tally-category">
                <i class="fas ${icon}"></i>
                ${displayCategory}
            </div>
            <div class="tally-details">
                <div class="tally-amount">₱ ${data.totalGrossAmount.toFixed(2)}</div>
                <div class="tally-count">${data.count} ${data.count === 1 ? 'item' : 'items'}</div>
            </div>
            <div class="tally-rate">Withholding: ${ratePercentage}%</div>
            <div style="margin-top: 8px; font-size: 11px; color: #80cbc4;">
                W/Tax: ₱ ${data.totalWithholdingTax.toFixed(2)}
            </div>
        `;
        
        tallyGrid.appendChild(tallyItem);
    });
    
    // Add total tally item
    if (totalRows > 0) {
        const totalWithholdingTax = parseFloat(document.getElementById('totalWithholdingTax').textContent) || 0;
        
        const totalItem = document.createElement('div');
        totalItem.className = 'tally-item tally-total';
        totalItem.innerHTML = `
            <div class="tally-category">
                <i class="fas fa-chart-bar"></i>
                TOTAL ALL CATEGORIES
            </div>
            <div class="tally-details">
                <div class="tally-amount">₱ ${totalGrossAmount.toFixed(2)}</div>
                <div class="tally-count">${totalRows} ${totalRows === 1 ? 'row' : 'rows'}</div>
            </div>
            <div style="margin-top: 8px; font-size: 12px; color: #80ffd4; font-weight: 500;">
                Summarized across ${sortedCategories.length} expense categories
            </div>
            <div style="margin-top: 8px; font-size: 11px; color: #00ff9d; font-weight: 600;">
                Total Withholding Tax: ₱ ${totalWithholdingTax.toFixed(2)}
            </div>
        `;
        tallyGrid.appendChild(totalItem);
    } else {
        // Show empty state
        const emptyItem = document.createElement('div');
        emptyItem.className = 'tally-item tally-total';
        emptyItem.innerHTML = `
            <div class="tally-category">
                <i class="fas fa-info-circle"></i>
                NO EXPENSE DATA
            </div>
            <div style="color: #80cbc4; font-size: 14px; text-align: center; padding: 10px 0;">
                Add non-vatable expenses to see category totals here
            </div>
        `;
        tallyGrid.appendChild(emptyItem);
    }
}

// Helper function to calculate expense tally for export
function calculateExpenseTallyForExport() {
    const rows = document.querySelectorAll('#tableBody tr');
    const expenseTally = {};
    
    // Initialize categories with zero values
    Object.keys(expenseCategories).forEach(category => {
        expenseTally[category] = {
            count: 0,
            totalGrossAmount: 0,
            totalWithholdingTax: 0,
            rate: expenseCategories[category]
        };
    });
    
    // Process each row
    rows.forEach(row => {
        const grossAmountInput = row.querySelector('.gross-amount');
        const natureExpenseSelect = row.querySelector('.nature-expense');
        const withholdingTaxDiv = row.querySelector('.withholding-tax');
        
        if (grossAmountInput && natureExpenseSelect) {
            const grossAmount = parseFloat(grossAmountInput.value) || 0;
            const natureExpense = natureExpenseSelect.value;
            const withholdingTax = parseFloat(withholdingTaxDiv.textContent) || 0;
            
            if (natureExpense && expenseCategories[natureExpense] !== undefined) {
                expenseTally[natureExpense].count++;
                expenseTally[natureExpense].totalGrossAmount += grossAmount;
                expenseTally[natureExpense].totalWithholdingTax += withholdingTax;
            }
        }
    });
    
    return expenseTally;
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
    
    // Calculate expense tally for the report
    const expenseTally = calculateExpenseTallyForExport();
    const sortedCategories = Object.keys(expenseTally)
        .filter(category => expenseTally[category].count > 0)
        .sort((a, b) => expenseTally[b].totalGrossAmount - expenseTally[a].totalGrossAmount);
    
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
    const totalGrossAmount = parseFloat(document.getElementById('totalGrossAmount').textContent) || 0;
    const totalWithholdingTax = parseFloat(document.getElementById('totalWithholdingTax').textContent) || 0;
    const netPayable = parseFloat(document.getElementById('netPayable').textContent) || 0;
    
    let htmlContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Non-Vatable Expenses Report</x:Name>
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
                .col-transaction { width: 80px; }
                .col-tin { width: 130px; }
                .col-supplier { width: 150px; }
                .col-address { width: 170px; }
                .col-amount { width: 100px; }
                .col-nature { width: 180px; }
                .col-rate { width: 80px; }
                .col-withholding { width: 100px; }
                .col-net-payable { width: 120px; }
                .col-remarks { width: 120px; }
                
                /* Tally section styles */
                .tally-section {
                    margin-top: 25px;
                    background-color: #f8fcf9;
                    border: 1px solid #00cc7a;
                    padding: 15px;
                    border-radius: 8px;
                }
                
                .tally-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    font-size: 9pt;
                }
                
                .tally-table th {
                    background-color: #00804d;
                    color: white;
                    font-weight: bold;
                    padding: 8px 10px;
                    border: 1px solid #004d26;
                    text-align: center;
                }
                
                .tally-table td {
                    padding: 6px 10px;
                    border: 1px solid #cccccc;
                }
                
                .tally-category {
                    background-color: #f0f9f5;
                    font-weight: 600;
                    color: #006837;
                }
                
                .tally-total-row {
                    background-color: #e6f2ed;
                    font-weight: bold;
                    border-top: 2px solid #00cc7a;
                }
                
                .tally-total-row td {
                    border-top: 2px solid #00cc7a;
                }
                
                .tally-header {
                    background-color: #006837;
                    color: white;
                    font-weight: bold;
                    text-align: center;
                    padding: 10px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                
                /* Category type indicators */
                .category-nonvat {
                    border-left: 4px solid #666666;
                    background-color: #f5f5f5;
                }
                
                .category-services {
                    border-left: 4px solid #00b3ff;
                }
                
                .category-goods {
                    border-left: 4px solid #ff9900;
                }
                
                .category-rental {
                    border-left: 4px solid #9d00ff;
                }
                
                .tally-count {
                    text-align: center;
                }
                
                .tally-rate {
                    text-align: center;
                    color: #006837;
                    font-weight: 600;
                }
                
                .nonvat-note {
                    background-color: #fff9e6;
                    border: 1px solid #ffcc00;
                    padding: 8px;
                    border-radius: 5px;
                    margin: 10px 0;
                    font-size: 9pt;
                    color: #663300;
                }
            </style>
            <title>Non-Vatable Expenses Report - ${formattedDate}</title>
        </head>
        <body>
            <div class="report-title">NON-VATABLE EXPENSES DATA REPORT</div>
            
            <div class="nonvat-note">
                <strong>Note:</strong> This report contains Non-VAT transactions. No 12% Input Tax is applied to these expenses.
            </div>
            
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
            
            <div class="section-title">Non-Vatable Expenses Transactions</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-invoice">Invoice Number</th>
                        <th class="col-type">Invoice Type</th>
                        <th class="col-transaction">Transaction Type</th>
                        <th class="col-tin">TIN Number</th>
                        <th class="col-supplier">Supplier Name</th>
                        <th class="col-address">Address</th>
                        <th class="col-amount">Gross Amount</th>
                        <th class="col-nature">Nature of Expense</th>
                        <th class="col-rate">W/Tax Rate</th>
                        <th class="col-withholding">Withholding Tax</th>
                        <th class="col-net-payable">Gross - W/Tax</th>
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
        
        // Invoice Number
        const invoiceInput = row.querySelector('.invoice-number');
        const invoiceValue = invoiceInput ? invoiceInput.value : '';
        
        // Invoice Type
        const invoiceTypeSelect = row.querySelector('.invoice-type');
        const invoiceTypeValue = invoiceTypeSelect ? invoiceTypeSelect.value : '';
        
        // Transaction Type
        const transactionTypeSelect = row.querySelector('.transaction-type');
        const transactionTypeValue = transactionTypeSelect ? transactionTypeSelect.value : '';
        
        // TIN Number
        const tinInput = row.querySelector('.tin-number');
        const tinValue = tinInput ? tinInput.value : '';
        
        // Supplier Name
        const supplierInput = row.querySelector('.supplier-name');
        const supplierValue = supplierInput ? supplierInput.value : '';
        
        // Address
        const addressInput = row.querySelector('.address');
        const addressValue = addressInput ? addressInput.value : '';
        
        // Gross Amount
        const grossAmountInput = row.querySelector('.gross-amount');
        const grossAmountValue = grossAmountInput ? parseFloat(grossAmountInput.value) || 0 : 0;
        
        // Nature of Expense
        const natureExpenseSelect = row.querySelector('.nature-expense');
        const natureExpenseValue = natureExpenseSelect ? natureExpenseSelect.value : '';
        
        // Withholding Tax Rate
        const withholdingRateDiv = row.querySelector('.withholding-rate');
        const withholdingRateValue = withholdingRateDiv ? withholdingRateDiv.textContent : '0%';
        
        // Withholding Tax
        const withholdingTaxDiv = row.querySelector('.withholding-tax');
        const withholdingTaxValue = withholdingTaxDiv ? parseFloat(withholdingTaxDiv.textContent) || 0 : 0;
        
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
                <td class="text-center">${transactionTypeValue}</td>
                <td class="text-center">${tinValue}</td>
                <td class="text-left">${supplierValue}</td>
                <td class="text-left">${addressValue}</td>
                <td class="currency numeric">${grossAmountValue.toFixed(2)}</td>
                <td class="text-left">${natureExpenseValue}</td>
                <td class="text-center">${withholdingRateValue}</td>
                <td class="currency numeric">${withholdingTaxValue.toFixed(2)}</td>
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
                        <td class="currency numeric" style="font-weight: bold;">${totalGrossAmount.toFixed(2)}</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="currency numeric" style="font-weight: bold;">${totalWithholdingTax.toFixed(2)}</td>
                        <td class="currency numeric" style="font-weight: bold;">${netPayable.toFixed(2)}</td>
                        <td>-</td>
                    </tr>
                </tfoot>
            </table>
            
            <!-- EXPENSE CATEGORY TALLY SECTION -->
            <div class="tally-section">
                <div class="tally-header">
                    EXPENSE CATEGORY TALLY SUMMARY (NON-VAT)
                </div>
                
                <table class="tally-table">
                    <thead>
                        <tr>
                            <th width="40%">Nature of Expense</th>
                            <th width="10%">Category Type</th>
                            <th width="10%">W/Tax Rate</th>
                            <th width="10%">Item Count</th>
                            <th width="15%">Total Gross Amount</th>
                            <th width="15%">Total Withholding Tax</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    // Add tally rows
    let tallyTotalGross = 0;
    let tallyTotalWithholdingTax = 0;
    let tallyTotalItems = 0;
    
    sortedCategories.forEach(category => {
        const data = expenseTally[category];
        const displayCategory = category.replace(/–/g, '-').replace(/ {2,}/g, ' ');
        const ratePercentage = (data.rate * 100).toFixed(0);
        
        // Determine category type
        let categoryType = '';
        let categoryDisplay = '';
        if (category === 'SALARIES AND WAGES' || category === 'SSS/PHILHEALTH/PAG-IBIG') {
            categoryType = 'category-nonvat';
            categoryDisplay = 'NonVAT';
        } else if (category.includes('SERVICES') || category === 'Rental') {
            categoryType = category === 'Rental' ? 'category-rental' : 'category-services';
            categoryDisplay = category === 'Rental' ? 'Rental' : 'Services';
        } else if (category.includes('GOODS') || category.includes('MATERIALS') || category.includes('SUPPLIES')) {
            categoryType = 'category-goods';
            categoryDisplay = 'Goods';
        } else {
            categoryType = '';
            categoryDisplay = 'Other';
        }
        
        tallyTotalGross += data.totalGrossAmount;
        tallyTotalWithholdingTax += data.totalWithholdingTax;
        tallyTotalItems += data.count;
        
        htmlContent += `
            <tr class="${categoryType}">
                <td class="tally-category">${displayCategory}</td>
                <td class="text-center">${categoryDisplay}</td>
                <td class="tally-rate">${ratePercentage}%</td>
                <td class="tally-count">${data.count}</td>
                <td class="currency numeric">${data.totalGrossAmount.toFixed(2)}</td>
                <td class="currency numeric">${data.totalWithholdingTax.toFixed(2)}</td>
            </tr>
        `;
    });
    
    // Add total row for tally
    htmlContent += `
                        <tr class="tally-total-row">
                            <td colspan="3" class="text-right" style="font-weight: bold;">TOTAL:</td>
                            <td class="text-center" style="font-weight: bold;">${tallyTotalItems}</td>
                            <td class="currency numeric" style="font-weight: bold;">${tallyTotalGross.toFixed(2)}</td>
                            <td class="currency numeric" style="font-weight: bold;">${tallyTotalWithholdingTax.toFixed(2)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="totals-section">
                <div class="section-title">Financial Summary (Non-VAT)</div>
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Total Gross Amount:</td>
                        <td class="total-value">₱ ${totalGrossAmount.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Withholding Tax:</td>
                        <td class="total-value">₱ ${totalWithholdingTax.toFixed(2)}</td>
                    </tr>
                    <tr>
                        <td class="total-label" style="border-top: 1px solid #00cc7a; padding-top: 10px;">Net Amount Payable:</td>
                        <td class="total-value" style="border-top: 1px solid #00cc7a; padding-top: 10px; font-size: 11pt; color: #006837;">
                            ₱ ${netPayable.toFixed(2)}
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <p>Report generated on ${formattedDate} at ${formattedTime}</p>
                <p>Number of Expense Categories: ${sortedCategories.length}</p>
                <p>Total Non-VAT Transactions: ${rowNumber - 1} items across ${sortedCategories.length} categories</p>
                <p>Authorized by: ${authorizedEmployee} | Prepared by: Non-Vatable Expenses Data Tracker System</p>
                <p>This is a computer-generated report. No signature is required.</p>
                <p>© ${new Date().getFullYear()} Non-Vatable Expenses Data Tracker - Professional Non-VAT Expenses Tracking System</p>
            </div>
        </body>
        </html>
    `;
    
    // Create download link for XLS file
    const blob = new Blob([htmlContent], {type: 'application/vnd.ms-excel'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    const fileName = `Non_Vatable_Expenses_Report_${companyName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().split('T')[0]}.xls`;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    setTimeout(() => {
        URL.revokeObjectURL(url);
    }, 100);
    
    // Show success message
    alert(`✅ Excel report "${fileName}" has been generated successfully!\n\nFeatures:\n• Professional formatting\n• Company header\n• Detailed non-VAT transactions\n• Expense category tally\n• Financial summary\n• ${sortedCategories.length} expense categories analyzed\n• Print-ready layout`);
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