// vat_summary_script.js - JavaScript for VAT Summary page

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set default date for report
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('reportDate').value = today.toISOString().split('T')[0];
    document.getElementById('reportPeriod').textContent = `${firstDayOfMonth} to ${today.toISOString().split('T')[0]}`;
    
    // Try to load the logo
    loadLogo();
    
    // Load saved company info if exists
    loadCompanyInfo();
    
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
    
    // Export to Excel button
    document.getElementById('exportBtn').addEventListener('click', exportToExcel);
    
    // Generate Summary button
    document.getElementById('generateReportBtn').addEventListener('click', generateSummary);
    
    // Add subtle background animation
    createBackgroundEffects();
    
    // Auto-generate summary on page load (demo data)
    setTimeout(generateSummary, 1000);
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
    localStorage.setItem('vatSummaryCompanyInfo', JSON.stringify(companyInfo));
}

// Load company info from localStorage
function loadCompanyInfo() {
    const savedInfo = localStorage.getItem('vatSummaryCompanyInfo');
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
    localStorage.removeItem('vatSummaryCompanyInfo');
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

// Function to generate summary (using demo data for now)
function generateSummary() {
    // In a real application, you would fetch data from localStorage or a database
    // For now, we'll use demo data
    
    // Demo data for Vatable Sales
    const vatableSalesGross = 1500000.00;
    const vatableSalesNet = vatableSalesGross / 1.12;
    const vatableSalesVAT = vatableSalesNet * 0.12;
    
    // Demo data for Non-Vatable Sales
    const nonVatableSalesGross = 500000.00;
    const nonVatableSalesNet = nonVatableSalesGross; // No VAT
    const nonVatableSalesVAT = 0.00;
    
    // Demo data for Vatable Purchases
    const vatablePurchasesGross = 800000.00;
    const vatablePurchasesNet = vatablePurchasesGross / 1.12;
    const vatablePurchasesVAT = vatablePurchasesNet * 0.12;
    
    // Demo data for Non-Vatable Purchases
    const nonVatablePurchasesGross = 300000.00;
    const nonVatablePurchasesNet = nonVatablePurchasesGross; // No VAT
    const nonVatablePurchasesVAT = 0.00;
    
    // Demo data for Vatable Expenses
    const vatableExpensesGross = 400000.00;
    const vatableExpensesNet = vatableExpensesGross / 1.12;
    const vatableExpensesVAT = vatableExpensesNet * 0.12;
    const vatableExpensesWHT = vatableExpensesNet * 0.02; // Assuming 2% WHT
    
    // Demo data for Non-Vatable Expenses
    const nonVatableExpensesGross = 200000.00;
    const nonVatableExpensesNet = nonVatableExpensesGross; // No VAT
    const nonVatableExpensesVAT = 0.00;
    const nonVatableExpensesWHT = nonVatableExpensesGross * 0.01; // Assuming 1% WHT
    
    // Demo data for CAPEX
    const capexGross = 1200000.00;
    const capexNet = capexGross / 1.12;
    const capexVAT = capexNet * 0.12;
    const capexWHT = 15000.00;
    const capexNetPayable = capexGross - capexWHT;
    
    // Demo data for Taxes & Licenses
    const taxesGross = 75000.00;
    const taxesNet = taxesGross; // No VAT on tax payments
    const taxesVAT = 0.00;
    const taxesWHT = 0.00;
    const taxesNetPayable = taxesGross;
    
    // Demo data for Purchase Returns
    const returnsGross = 50000.00;
    const returnsNet = returnsGross / 1.12;
    const returnsVAT = returnsNet * 0.12;
    const returnsWHT = 0.00;
    const returnsNetReceivable = returnsGross;
    
    // Update VAT Summary Table
    document.getElementById('vatableSalesGross').textContent = formatCurrency(vatableSalesGross);
    document.getElementById('vatableSalesNet').textContent = formatCurrency(vatableSalesNet);
    document.getElementById('vatableSalesVAT').textContent = formatCurrency(vatableSalesVAT);
    
    document.getElementById('nonVatableSalesGross').textContent = formatCurrency(nonVatableSalesGross);
    document.getElementById('nonVatableSalesNet').textContent = formatCurrency(nonVatableSalesNet);
    document.getElementById('nonVatableSalesVAT').textContent = formatCurrency(nonVatableSalesVAT);
    
    document.getElementById('vatablePurchasesGross').textContent = formatCurrency(vatablePurchasesGross);
    document.getElementById('vatablePurchasesNet').textContent = formatCurrency(vatablePurchasesNet);
    document.getElementById('vatablePurchasesVAT').textContent = formatCurrency(vatablePurchasesVAT);
    
    document.getElementById('nonVatablePurchasesGross').textContent = formatCurrency(nonVatablePurchasesGross);
    document.getElementById('nonVatablePurchasesNet').textContent = formatCurrency(nonVatablePurchasesNet);
    document.getElementById('nonVatablePurchasesVAT').textContent = formatCurrency(nonVatablePurchasesVAT);
    
    // Calculate and update totals
    const totalSalesGross = vatableSalesGross + nonVatableSalesGross;
    const totalSalesNet = vatableSalesNet + nonVatableSalesNet;
    const totalSalesVAT = vatableSalesVAT + nonVatableSalesVAT;
    
    const totalPurchasesGross = vatablePurchasesGross + nonVatablePurchasesGross;
    const totalPurchasesNet = vatablePurchasesNet + nonVatablePurchasesNet;
    const totalPurchasesVAT = vatablePurchasesVAT + nonVatablePurchasesVAT;
    
    document.getElementById('totalSalesGross').textContent = formatCurrency(totalSalesGross);
    document.getElementById('totalSalesNet').textContent = formatCurrency(totalSalesNet);
    document.getElementById('totalSalesVAT').textContent = formatCurrency(totalSalesVAT);
    
    document.getElementById('totalPurchasesGross').textContent = formatCurrency(totalPurchasesGross);
    document.getElementById('totalPurchasesNet').textContent = formatCurrency(totalPurchasesNet);
    document.getElementById('totalPurchasesVAT').textContent = formatCurrency(totalPurchasesVAT);
    
    // Update Expenses Summary Table
    document.getElementById('vatableExpensesGross').textContent = formatCurrency(vatableExpensesGross);
    document.getElementById('vatableExpensesNet').textContent = formatCurrency(vatableExpensesNet);
    document.getElementById('vatableExpensesVAT').textContent = formatCurrency(vatableExpensesVAT);
    document.getElementById('vatableExpensesWHT').textContent = formatCurrency(vatableExpensesWHT);
    
    document.getElementById('nonVatableExpensesGross').textContent = formatCurrency(nonVatableExpensesGross);
    document.getElementById('nonVatableExpensesNet').textContent = formatCurrency(nonVatableExpensesNet);
    document.getElementById('nonVatableExpensesVAT').textContent = formatCurrency(nonVatableExpensesVAT);
    document.getElementById('nonVatableExpensesWHT').textContent = formatCurrency(nonVatableExpensesWHT);
    
    const totalExpensesGross = vatableExpensesGross + nonVatableExpensesGross;
    const totalExpensesNet = vatableExpensesNet + nonVatableExpensesNet;
    const totalExpensesVAT = vatableExpensesVAT + nonVatableExpensesVAT;
    const totalExpensesWHT = vatableExpensesWHT + nonVatableExpensesWHT;
    
    document.getElementById('totalExpensesGross').textContent = formatCurrency(totalExpensesGross);
    document.getElementById('totalExpensesNet').textContent = formatCurrency(totalExpensesNet);
    document.getElementById('totalExpensesVAT').textContent = formatCurrency(totalExpensesVAT);
    document.getElementById('totalExpensesWHT').textContent = formatCurrency(totalExpensesWHT);
    
    // Update Other Transactions Table
    document.getElementById('capexGross').textContent = formatCurrency(capexGross);
    document.getElementById('capexNet').textContent = formatCurrency(capexNet);
    document.getElementById('capexVAT').textContent = formatCurrency(capexVAT);
    document.getElementById('capexWHT').textContent = formatCurrency(capexWHT);
    document.getElementById('capexNetPayable').textContent = formatCurrency(capexNetPayable);
    
    document.getElementById('taxesGross').textContent = formatCurrency(taxesGross);
    document.getElementById('taxesNet').textContent = formatCurrency(taxesNet);
    document.getElementById('taxesVAT').textContent = formatCurrency(taxesVAT);
    document.getElementById('taxesWHT').textContent = formatCurrency(taxesWHT);
    document.getElementById('taxesNetPayable').textContent = formatCurrency(taxesNetPayable);
    
    document.getElementById('returnsGross').textContent = formatCurrency(returnsGross);
    document.getElementById('returnsNet').textContent = formatCurrency(returnsNet);
    document.getElementById('returnsVAT').textContent = formatCurrency(returnsVAT);
    document.getElementById('returnsWHT').textContent = formatCurrency(returnsWHT);
    document.getElementById('returnsNetReceivable').textContent = formatCurrency(returnsNetReceivable);
    
    // Update VAT Payable Summary
    const totalOutputTax = vatableSalesVAT; // Output tax from sales
    const totalInputTax = vatablePurchasesVAT + vatableExpensesVAT + capexVAT + returnsVAT; // Input tax from purchases, expenses, capex, returns
    
    document.getElementById('totalOutputTax').textContent = formatCurrency(totalOutputTax);
    document.getElementById('totalInputTax').textContent = formatCurrency(totalInputTax);
    
    const vatPayable = totalOutputTax - totalInputTax;
    document.getElementById('vatPayable').textContent = formatCurrency(vatPayable);
    
    // Update Withholding Tax Summary
    // In a real system, these would come from the respective modules
    const totalWHTonSales = 25000.00; // Demo data
    const totalWHTonPurchases = 18000.00; // Demo data
    const totalWHTonExpenses = totalExpensesWHT;
    
    document.getElementById('totalWHTonSales').textContent = formatCurrency(totalWHTonSales);
    document.getElementById('totalWHTonPurchases').textContent = formatCurrency(totalWHTonPurchases);
    document.getElementById('totalWHTonExpenses').textContent = formatCurrency(totalWHTonExpenses);
    
    const totalNetWHT = totalWHTonSales + totalWHTonPurchases + totalWHTonExpenses;
    document.getElementById('totalNetWHT').textContent = formatCurrency(totalNetWHT);
    
    // Calculate Grand Total VAT Liability
    const grandTotalVAT = vatPayable + totalNetWHT;
    document.getElementById('grandTotalVAT').textContent = formatCurrency(grandTotalVAT);
    
    // Update Key Financial Indicators
    const totalGrossRevenue = totalSalesGross;
    const totalNetRevenue = totalSalesNet;
    const totalExpensesAmount = totalExpensesGross + capexGross + taxesGross - returnsGross;
    
    document.getElementById('totalGrossRevenue').textContent = formatCurrency(totalGrossRevenue);
    document.getElementById('totalNetRevenue').textContent = formatCurrency(totalNetRevenue);
    document.getElementById('totalExpensesAmount').textContent = formatCurrency(totalExpensesAmount);
    
    const grossProfit = totalGrossRevenue - totalPurchasesGross;
    const netProfitBeforeTax = grossProfit - totalExpensesAmount;
    
    document.getElementById('grossProfit').textContent = formatCurrency(grossProfit);
    document.getElementById('netProfitBeforeTax').textContent = formatCurrency(netProfitBeforeTax);
    
    // Show success message
    showNotification('VAT Summary generated successfully!', 'success');
}

// Function to format currency
function formatCurrency(amount) {
    return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Function to show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? 'rgba(0, 255, 157, 0.9)' : 'rgba(255, 51, 51, 0.9)'};
        color: ${type === 'success' ? '#002614' : '#ffffff'};
        border-radius: 8px;
        font-weight: 600;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .summary-section {
        margin-bottom: 30px;
        background: rgba(5, 20, 15, 0.7);
        border-radius: 10px;
        padding: 20px;
        border: 1px solid rgba(0, 255, 157, 0.2);
    }
    
    .summary-section h3 {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .summary-label {
        font-weight: 600;
        color: #a7ffeb;
        padding: 12px;
        background: rgba(0, 40, 30, 0.5);
    }
    
    .summary-value {
        padding: 12px;
        font-weight: 500;
        color: #00ff9d;
        text-align: center;
        background: rgba(0, 30, 20, 0.5);
        border-radius: 6px;
        font-family: 'Consolas', 'Courier New', monospace;
    }
    
    #vatSummaryTable,
    #expensesSummaryTable,
    #otherTransactionsTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    #vatSummaryTable th,
    #expensesSummaryTable th,
    #otherTransactionsTable th {
        background: rgba(0, 60, 40, 0.8);
        color: #a7ffeb;
        padding: 12px;
        text-align: center;
        border: 1px solid rgba(0, 255, 157, 0.2);
    }
    
    #vatSummaryTable td,
    #expensesSummaryTable td,
    #otherTransactionsTable td {
        padding: 12px;
        border: 1px solid rgba(0, 255, 157, 0.1);
        text-align: center;
    }
    
    #vatSummaryTable tbody tr:hover,
    #expensesSummaryTable tbody tr:hover,
    #otherTransactionsTable tbody tr:hover {
        background: rgba(0, 40, 30, 0.8);
    }
`;
document.head.appendChild(style);

// Function to export to Excel
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
    
    // Get all summary data
    const vatableSalesGross = document.getElementById('vatableSalesGross').textContent;
    const vatableSalesNet = document.getElementById('vatableSalesNet').textContent;
    const vatableSalesVAT = document.getElementById('vatableSalesVAT').textContent;
    
    const nonVatableSalesGross = document.getElementById('nonVatableSalesGross').textContent;
    const nonVatableSalesNet = document.getElementById('nonVatableSalesNet').textContent;
    const nonVatableSalesVAT = document.getElementById('nonVatableSalesVAT').textContent;
    
    const totalSalesGross = document.getElementById('totalSalesGross').textContent;
    const totalSalesNet = document.getElementById('totalSalesNet').textContent;
    const totalSalesVAT = document.getElementById('totalSalesVAT').textContent;
    
    const vatablePurchasesGross = document.getElementById('vatablePurchasesGross').textContent;
    const vatablePurchasesNet = document.getElementById('vatablePurchasesNet').textContent;
    const vatablePurchasesVAT = document.getElementById('vatablePurchasesVAT').textContent;
    
    const nonVatablePurchasesGross = document.getElementById('nonVatablePurchasesGross').textContent;
    const nonVatablePurchasesNet = document.getElementById('nonVatablePurchasesNet').textContent;
    const nonVatablePurchasesVAT = document.getElementById('nonVatablePurchasesVAT').textContent;
    
    const totalPurchasesGross = document.getElementById('totalPurchasesGross').textContent;
    const totalPurchasesNet = document.getElementById('totalPurchasesNet').textContent;
    const totalPurchasesVAT = document.getElementById('totalPurchasesVAT').textContent;
    
    const vatableExpensesGross = document.getElementById('vatableExpensesGross').textContent;
    const vatableExpensesNet = document.getElementById('vatableExpensesNet').textContent;
    const vatableExpensesVAT = document.getElementById('vatableExpensesVAT').textContent;
    const vatableExpensesWHT = document.getElementById('vatableExpensesWHT').textContent;
    
    const nonVatableExpensesGross = document.getElementById('nonVatableExpensesGross').textContent;
    const nonVatableExpensesNet = document.getElementById('nonVatableExpensesNet').textContent;
    const nonVatableExpensesVAT = document.getElementById('nonVatableExpensesVAT').textContent;
    const nonVatableExpensesWHT = document.getElementById('nonVatableExpensesWHT').textContent;
    
    const totalExpensesGross = document.getElementById('totalExpensesGross').textContent;
    const totalExpensesNet = document.getElementById('totalExpensesNet').textContent;
    const totalExpensesVAT = document.getElementById('totalExpensesVAT').textContent;
    const totalExpensesWHT = document.getElementById('totalExpensesWHT').textContent;
    
    const capexGross = document.getElementById('capexGross').textContent;
    const capexNet = document.getElementById('capexNet').textContent;
    const capexVAT = document.getElementById('capexVAT').textContent;
    const capexWHT = document.getElementById('capexWHT').textContent;
    const capexNetPayable = document.getElementById('capexNetPayable').textContent;
    
    const taxesGross = document.getElementById('taxesGross').textContent;
    const taxesNet = document.getElementById('taxesNet').textContent;
    const taxesVAT = document.getElementById('taxesVAT').textContent;
    const taxesWHT = document.getElementById('taxesWHT').textContent;
    const taxesNetPayable = document.getElementById('taxesNetPayable').textContent;
    
    const returnsGross = document.getElementById('returnsGross').textContent;
    const returnsNet = document.getElementById('returnsNet').textContent;
    const returnsVAT = document.getElementById('returnsVAT').textContent;
    const returnsWHT = document.getElementById('returnsWHT').textContent;
    const returnsNetReceivable = document.getElementById('returnsNetReceivable').textContent;
    
    const totalOutputTax = document.getElementById('totalOutputTax').textContent;
    const totalInputTax = document.getElementById('totalInputTax').textContent;
    const vatPayable = document.getElementById('vatPayable').textContent;
    const totalWHTonSales = document.getElementById('totalWHTonSales').textContent;
    const totalWHTonPurchases = document.getElementById('totalWHTonPurchases').textContent;
    const totalWHTonExpenses = document.getElementById('totalWHTonExpenses').textContent;
    const totalNetWHT = document.getElementById('totalNetWHT').textContent;
    const grandTotalVAT = document.getElementById('grandTotalVAT').textContent;
    
    const totalGrossRevenue = document.getElementById('totalGrossRevenue').textContent;
    const totalNetRevenue = document.getElementById('totalNetRevenue').textContent;
    const totalExpensesAmount = document.getElementById('totalExpensesAmount').textContent;
    const grossProfit = document.getElementById('grossProfit').textContent;
    const netProfitBeforeTax = document.getElementById('netProfitBeforeTax').textContent;
    
    let htmlContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="UTF-8">
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>VAT Summary Report</x:Name>
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
                
                .section-title {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #006837;
                    margin-top: 25px;
                    margin-bottom: 10px;
                    padding-bottom: 5px;
                    border-bottom: 2px solid #00cc7a;
                }
                
                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
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
                
                .summary-section {
                    margin-bottom: 25px;
                    background-color: #f0f9f5;
                    border: 1px solid #00cc7a;
                    padding: 15px;
                    border-radius: 8px;
                }
                
                .summary-label {
                    font-weight: bold;
                    color: #006837;
                    background-color: #e6f7f0;
                    padding: 8px;
                }
                
                .summary-value {
                    text-align: right;
                    font-family: 'Consolas', 'Courier New', monospace;
                    padding: 8px;
                    background-color: #ffffff;
                }
                
                .highlight {
                    background-color: #e6f7f0;
                }
                
                .total-row {
                    background-color: #fff2cc;
                    font-weight: bold;
                }
                
                .footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #cccccc;
                    color: #666666;
                    font-size: 9pt;
                    text-align: center;
                }
            </style>
            <title>VAT Summary Report - ${formattedDate}</title>
        </head>
        <body>
            <div class="report-title">COMPREHENSIVE VAT SUMMARY REPORT</div>
            
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
                    <tr>
                        <td class="company-label">Reporting Period:</td>
                        <td class="company-value" colspan="3">${document.getElementById('reportPeriod').textContent}</td>
                    </tr>
                </table>
            </div>
            
            <div class="section-title">VAT SUMMARY</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Vatable Sales</th>
                        <th>Non-Vatable Sales</th>
                        <th>Total Sales</th>
                        <th>Vatable Purchases</th>
                        <th>Non-Vatable Purchases</th>
                        <th>Total Purchases</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="summary-label">Gross Amount</td>
                        <td class="currency numeric">${vatableSalesGross}</td>
                        <td class="currency numeric">${nonVatableSalesGross}</td>
                        <td class="currency numeric">${totalSalesGross}</td>
                        <td class="currency numeric">${vatablePurchasesGross}</td>
                        <td class="currency numeric">${nonVatablePurchasesGross}</td>
                        <td class="currency numeric">${totalPurchasesGross}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Net Amount</td>
                        <td class="currency numeric">${vatableSalesNet}</td>
                        <td class="currency numeric">${nonVatableSalesNet}</td>
                        <td class="currency numeric">${totalSalesNet}</td>
                        <td class="currency numeric">${vatablePurchasesNet}</td>
                        <td class="currency numeric">${nonVatablePurchasesNet}</td>
                        <td class="currency numeric">${totalPurchasesNet}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">VAT Amount (12%)</td>
                        <td class="currency numeric">${vatableSalesVAT}</td>
                        <td class="currency numeric">${nonVatableSalesVAT}</td>
                        <td class="currency numeric">${totalSalesVAT}</td>
                        <td class="currency numeric">${vatablePurchasesVAT}</td>
                        <td class="currency numeric">${nonVatablePurchasesVAT}</td>
                        <td class="currency numeric">${totalPurchasesVAT}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="section-title">EXPENSES SUMMARY</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Vatable Expenses</th>
                        <th>Non-Vatable Expenses</th>
                        <th>Total Expenses</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="summary-label">Gross Amount</td>
                        <td class="currency numeric">${vatableExpensesGross}</td>
                        <td class="currency numeric">${nonVatableExpensesGross}</td>
                        <td class="currency numeric">${totalExpensesGross}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Net Amount</td>
                        <td class="currency numeric">${vatableExpensesNet}</td>
                        <td class="currency numeric">${nonVatableExpensesNet}</td>
                        <td class="currency numeric">${totalExpensesNet}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">VAT Amount (12%)</td>
                        <td class="currency numeric">${vatableExpensesVAT}</td>
                        <td class="currency numeric">${nonVatableExpensesVAT}</td>
                        <td class="currency numeric">${totalExpensesVAT}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Withholding Tax</td>
                        <td class="currency numeric">${vatableExpensesWHT}</td>
                        <td class="currency numeric">${nonVatableExpensesWHT}</td>
                        <td class="currency numeric">${totalExpensesWHT}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="section-title">CAPITAL EXPENDITURES & OTHER TRANSACTIONS</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction Type</th>
                        <th>Gross Amount</th>
                        <th>Net Amount</th>
                        <th>VAT Amount</th>
                        <th>Withholding Tax</th>
                        <th>Net Payable/Receivable</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="summary-label">Capital Expenditures (CAPEX)</td>
                        <td class="currency numeric">${capexGross}</td>
                        <td class="currency numeric">${capexNet}</td>
                        <td class="currency numeric">${capexVAT}</td>
                        <td class="currency numeric">${capexWHT}</td>
                        <td class="currency numeric">${capexNetPayable}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Taxes & Licenses</td>
                        <td class="currency numeric">${taxesGross}</td>
                        <td class="currency numeric">${taxesNet}</td>
                        <td class="currency numeric">${taxesVAT}</td>
                        <td class="currency numeric">${taxesWHT}</td>
                        <td class="currency numeric">${taxesNetPayable}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Purchase Returns</td>
                        <td class="currency numeric">${returnsGross}</td>
                        <td class="currency numeric">${returnsNet}</td>
                        <td class="currency numeric">${returnsVAT}</td>
                        <td class="currency numeric">${returnsWHT}</td>
                        <td class="currency numeric">${returnsNetReceivable}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="section-title">VAT PAYABLE SUMMARY</div>
            <div class="summary-section">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; font-weight: bold; width: 70%;">Total Output Tax (VAT on Sales)</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalOutputTax}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Total Input Tax (VAT on Purchases)</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalInputTax}</td>
                    </tr>
                    <tr style="border-top: 2px solid #006837;">
                        <td style="padding: 15px; font-weight: bold; font-size: 14pt;">VAT Payable/(Refundable)</td>
                        <td style="padding: 15px; text-align: right; font-weight: bold; font-size: 14pt; font-family: 'Consolas', 'Courier New', monospace;">₱ ${vatPayable}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Total Withholding Tax on Sales</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalWHTonSales}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Total Withholding Tax on Purchases</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalWHTonPurchases}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Total Withholding Tax on Expenses</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalWHTonExpenses}</td>
                    </tr>
                    <tr style="border-top: 2px solid #006837;">
                        <td style="padding: 15px; font-weight: bold; font-size: 14pt;">Total Net Withholding Tax</td>
                        <td style="padding: 15px; text-align: right; font-weight: bold; font-size: 14pt; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalNetWHT}</td>
                    </tr>
                    <tr style="background-color: #006837; color: white;">
                        <td style="padding: 15px; font-weight: bold; font-size: 16pt;">GRAND TOTAL VAT LIABILITY</td>
                        <td style="padding: 15px; text-align: right; font-weight: bold; font-size: 16pt; font-family: 'Consolas', 'Courier New', monospace;">₱ ${grandTotalVAT}</td>
                    </tr>
                </table>
            </div>
            
            <div class="section-title">KEY FINANCIAL INDICATORS</div>
            <div class="summary-section">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; font-weight: bold; width: 70%;">Total Gross Revenue</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalGrossRevenue}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Total Net Revenue</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalNetRevenue}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Total Expenses</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${totalExpensesAmount}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold;">Gross Profit</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; font-family: 'Consolas', 'Courier New', monospace;">₱ ${grossProfit}</td>
                    </tr>
                    <tr style="border-top: 2px solid #006837; background-color: #e6f7f0;">
                        <td style="padding: 15px; font-weight: bold; font-size: 14pt;">Net Profit Before Tax</td>
                        <td style="padding: 15px; text-align: right; font-weight: bold; font-size: 14pt; font-family: 'Consolas', 'Courier New', monospace;">₱ ${netProfitBeforeTax}</td>
                    </tr>
                </table>
            </div>
            
            <div class="footer">
                <p>Report generated on ${formattedDate} at ${formattedTime}</p>
                <p>Authorized by: ${authorizedEmployee} | Prepared by: VAT Summary Report System</p>
                <p>This is a computer-generated report. No signature is required.</p>
                <p>© ${new Date().getFullYear()} VAT Summary Report - Comprehensive VAT Financial Analysis System</p>
            </div>
        </body>
        </html>
    `;
    
    // Create download link for XLS file
    const blob = new Blob([htmlContent], {type: 'application/vnd.ms-excel'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    const fileName = `VAT_Summary_Report_${companyName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().split('T')[0]}.xls`;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    setTimeout(() => {
        URL.revokeObjectURL(url);
    }, 100);
    
    // Show success message
    showNotification(`Excel report "${fileName}" has been generated successfully!`, 'success');
}