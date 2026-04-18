// ============================================================
// COMPANY INFORMATION MANAGEMENT - SHARED ACROSS ALL MODULES
// Handles company info sidebar, localStorage, TIN formatting
// Works with: Sales, Purchases, Expenses modules
// ============================================================

(function() {
    // Storage key will be determined by which module is using it
    let currentStorageKey = 'vatableCompanyInfo';
    
    // Auto-detect which module we're in
    function detectModule() {
        if (window.location.pathname.includes('vatable_sales.html')) {
            currentStorageKey = 'vatableSalesCompanyInfo';
        } else if (window.location.pathname.includes('vatable_purchases.html')) {
            currentStorageKey = 'vatablePurchasesCompanyInfo';
        } else if (window.location.pathname.includes('vatable_expenses.html')) {
            currentStorageKey = 'vatableExpensesCompanyInfo';
        } else {
            currentStorageKey = 'vatableCompanyInfo';
        }
    }
    
    // Save company info to localStorage
    function saveCompanyInfo() {
        const companyInfo = {
            name: document.getElementById('companyName')?.value || '',
            tin: document.getElementById('companyTIN')?.value || '',
            address: document.getElementById('companyAddress')?.value || '',
            business: document.getElementById('lineOfBusiness')?.value || '',
            telephone: document.getElementById('telephone')?.value || '',
            date: document.getElementById('reportDate')?.value || '',
            employee: document.getElementById('authorizedEmployee')?.value || '',
            email: document.getElementById('email')?.value || ''
        };
        localStorage.setItem(currentStorageKey, JSON.stringify(companyInfo));
    }
    
    // Load company info from localStorage
    function loadCompanyInfo() {
        detectModule();
        const savedInfo = localStorage.getItem(currentStorageKey);
        if (savedInfo) {
            const companyInfo = JSON.parse(savedInfo);
            if (document.getElementById('companyName')) document.getElementById('companyName').value = companyInfo.name || '';
            if (document.getElementById('companyTIN')) document.getElementById('companyTIN').value = companyInfo.tin || '';
            if (document.getElementById('companyAddress')) document.getElementById('companyAddress').value = companyInfo.address || '';
            if (document.getElementById('lineOfBusiness')) document.getElementById('lineOfBusiness').value = companyInfo.business || '';
            if (document.getElementById('telephone')) document.getElementById('telephone').value = companyInfo.telephone || '';
            if (document.getElementById('reportDate')) document.getElementById('reportDate').value = companyInfo.date || new Date().toISOString().split('T')[0];
            if (document.getElementById('authorizedEmployee')) document.getElementById('authorizedEmployee').value = companyInfo.employee || '';
            if (document.getElementById('email')) document.getElementById('email').value = companyInfo.email || '';
        }
    }
    
    // Clear company info
    function clearCompanyInfo() {
        if (document.getElementById('companyName')) document.getElementById('companyName').value = '';
        if (document.getElementById('companyTIN')) document.getElementById('companyTIN').value = '';
        if (document.getElementById('companyAddress')) document.getElementById('companyAddress').value = '';
        if (document.getElementById('lineOfBusiness')) document.getElementById('lineOfBusiness').value = '';
        if (document.getElementById('telephone')) document.getElementById('telephone').value = '';
        if (document.getElementById('reportDate')) document.getElementById('reportDate').value = new Date().toISOString().split('T')[0];
        if (document.getElementById('authorizedEmployee')) document.getElementById('authorizedEmployee').value = '';
        if (document.getElementById('email')) document.getElementById('email').value = '';
        localStorage.removeItem(currentStorageKey);
        
        // Show feedback
        if (typeof showAutoFillFeedback === 'function') {
            showAutoFillFeedback(null, '✓ Company information cleared');
        } else if (typeof showToast === 'function') {
            showToast('Company information cleared', 'success');
        }
    }
    
    // Format TIN input (adds dashes)
    let tinTimeout;
    function formatTIN(input) {
        if (!input) return;
        if (tinTimeout) clearTimeout(tinTimeout);
        
        tinTimeout = setTimeout(() => {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 12) value = value.substring(0, 12);
            
            if (value.length > 9) value = value.substring(0, 9) + '-' + value.substring(9);
            if (value.length > 6) value = value.substring(0, 6) + '-' + value.substring(6);
            if (value.length > 3) value = value.substring(0, 3) + '-' + value.substring(3);
            
            if (input.value !== value) input.value = value;
        }, 100);
    }
    
    // Validate TIN input
    function validateTIN(input) {
        if (!input) return;
        
        const value = input.value.replace(/\D/g, '');
        const validationMessage = input.nextElementSibling;
        
        if (value.length === 12) {
            if (validationMessage) validationMessage.style.display = 'none';
            input.style.borderColor = '#00ff9d';
            input.style.boxShadow = '0 0 5px rgba(0, 255, 157, 0.5)';
        } else if (value.length > 0) {
            if (validationMessage) validationMessage.style.display = 'block';
            input.style.borderColor = '#ff6666';
            input.style.boxShadow = '0 0 5px rgba(255, 102, 102, 0.5)';
        } else {
            if (validationMessage) validationMessage.style.display = 'none';
            input.style.borderColor = 'rgba(0, 255, 157, 0.3)';
            input.style.boxShadow = 'none';
        }
    }
    
    // Initialize company info sidebar
    function initCompanyInfo() {
        detectModule();
        loadCompanyInfo();
        
        // Add event listeners for company info inputs
        const companyInputs = document.querySelectorAll('.company-info-sidebar input');
        companyInputs.forEach(input => {
            input.addEventListener('input', saveCompanyInfo);
            input.addEventListener('change', saveCompanyInfo);
        });
        
        // Format TIN input
        const companyTIN = document.getElementById('companyTIN');
        if (companyTIN) {
            companyTIN.addEventListener('input', function(e) {
                formatTIN(e.target);
            });
            companyTIN.addEventListener('blur', function(e) {
                validateTIN(e.target);
            });
        }
        
        // Set default date if empty
        const reportDate = document.getElementById('reportDate');
        if (reportDate && !reportDate.value) {
            reportDate.value = new Date().toISOString().split('T')[0];
        }
        
        // Attach clear buttons
        const clearButtons = ['clearInfoBtn', 'clearInfoBtnSidebar'];
        clearButtons.forEach(id => {
            const btn = document.getElementById(id);
            if (btn && !btn.hasAttribute('data-listener-attached')) {
                btn.setAttribute('data-listener-attached', 'true');
                btn.addEventListener('click', function(e) {
                    if (confirm('Are you sure you want to clear all company information?')) {
                        clearCompanyInfo();
                    }
                });
            }
        });
    }
    
    // Expose functions globally
    window.saveCompanyInfo = saveCompanyInfo;
    window.loadCompanyInfo = loadCompanyInfo;
    window.clearCompanyInfo = clearCompanyInfo;
    window.formatTIN = formatTIN;
    window.validateTIN = validateTIN;
    window.initCompanyInfo = initCompanyInfo;
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCompanyInfo);
    } else {
        initCompanyInfo();
    }
})();