// ============================================================
// COMPANY INFORMATION MANAGEMENT - SHARED ACROSS ALL MODULES
// Handles company info sidebar, localStorage, TIN formatting
// Works with: Sales, Purchases, Expenses, and all other modules
// ============================================================

(function() {
    // Storage key will be determined by which module is using it
    let currentStorageKey = 'vatableCompanyInfo';
    let currentModuleName = 'Vatable Tracker';
    
    // Auto-detect which module we're in
    function detectModule() {
        // Vatable modules
        if (window.location.pathname.includes('vatable_sales.html')) {
            currentStorageKey = 'vatableSalesCompanyInfo';
            currentModuleName = 'Vatable Sales Tracker';
        } else if (window.location.pathname.includes('vatable_purchases.html')) {
            currentStorageKey = 'vatablePurchasesCompanyInfo';
            currentModuleName = 'Vatable Purchases Tracker';
        } else if (window.location.pathname.includes('vatable_expenses.html')) {
            currentStorageKey = 'vatableExpensesCompanyInfo';
            currentModuleName = 'Vatable Expenses Tracker';
        } 
        // Non-VAT modules
        else if (window.location.pathname.includes('non_vat_purchases.html')) {
            currentStorageKey = 'nonVatPurchasesCompanyInfo';
            currentModuleName = 'Non-VAT Purchases Tracker';
        } else if (window.location.pathname.includes('non_vat_expenses.html')) {
            currentStorageKey = 'nonVatExpensesCompanyInfo';
            currentModuleName = 'Non-VAT Expenses Tracker';
        } 
        // Other financial modules
        else if (window.location.pathname.includes('capex.html')) {
            currentStorageKey = 'capexCompanyInfo';
            currentModuleName = 'Capital Expenditure Tracker';
        } else if (window.location.pathname.includes('taxes_licenses.html')) {
            currentStorageKey = 'taxesLicensesCompanyInfo';
            currentModuleName = 'Taxes & Licenses Tracker';
        } else if (window.location.pathname.includes('purchase_returns.html')) {
            currentStorageKey = 'purchaseReturnsCompanyInfo';
            currentModuleName = 'Purchase Returns Tracker';
        } else if (window.location.pathname.includes('compensation.html')) {
            currentStorageKey = 'compensationCompanyInfo';
            currentModuleName = 'Compensation Tracker';
        } 
        // Default fallback
        else {
            currentStorageKey = 'vatableCompanyInfo';
            currentModuleName = 'Vatable Tracker';
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
    
    // NEW: Update all date columns in the table with the selected date
    function updateAllDatesInTable() {
        const reportDate = document.getElementById('reportDate')?.value;
        if (!reportDate) return;
        
        // Get all date input elements in the table (first column of each row)
        const dateInputs = document.querySelectorAll('#tableBody tr td:first-child input.date-input, #tableBody tr td:first-child input[type="date"]');
        
        if (dateInputs.length === 0) {
            console.log('No date inputs found in table');
            return;
        }
        
        let updatedCount = 0;
        
        dateInputs.forEach(dateInput => {
            if (dateInput && dateInput.value !== reportDate) {
                const oldValue = dateInput.value;
                dateInput.value = reportDate;
                
                // Trigger input and change events to update any calculations
                const inputEvent = new Event('input', { bubbles: true });
                const changeEvent = new Event('change', { bubbles: true });
                dateInput.dispatchEvent(inputEvent);
                dateInput.dispatchEvent(changeEvent);
                
                updatedCount++;
            }
        });
        
        // Show feedback to user
        if (updatedCount > 0) {
            const message = `✓ Updated ${updatedCount} row${updatedCount !== 1 ? 's' : ''} with date: ${reportDate}`;
            if (typeof showAutoFillFeedback === 'function') {
                showAutoFillFeedback(null, message);
            } else if (typeof showToast === 'function') {
                showToast(message, 'success');
            } else {
                console.log(message);
            }
        }
        
        // Also update any summary row date if exists
        const summaryDateCells = document.querySelectorAll('#summaryRow td:first-child');
        // Summary row usually doesn't have date, but just in case
        
        // Trigger any stats update if needed
        if (typeof updateAllStats === 'function') {
            setTimeout(() => updateAllStats(), 100);
        }
    }
    
    // NEW: Update all dates when user confirms
    function promptUpdateAllDates() {
        const reportDate = document.getElementById('reportDate')?.value;
        if (!reportDate) {
            if (typeof showToast === 'function') {
                showToast('Please select a date first', 'warning');
            }
            return;
        }
        
        const dateInputs = document.querySelectorAll('#tableBody tr td:first-child input.date-input, #tableBody tr td:first-child input[type="date"]');
        const rowCount = dateInputs.length;
        
        if (rowCount === 0) {
            if (typeof showToast === 'function') {
                showToast('No rows found in the table', 'info');
            }
            return;
        }
        
        // Ask for confirmation
        if (confirm(`Update all ${rowCount} row(s) with date: ${reportDate}?`)) {
            updateAllDatesInTable();
        }
    }
    
    // NEW: Auto-update dates when report date changes (optional - can be disabled)
    let autoUpdateEnabled = true;
    let lastReportDate = '';
    
    function checkAndAutoUpdateDates() {
        if (!autoUpdateEnabled) return;
        
        const currentDate = document.getElementById('reportDate')?.value;
        if (!currentDate) return;
        
        // Only auto-update if there are rows and date actually changed
        if (lastReportDate && lastReportDate !== currentDate) {
            const dateInputs = document.querySelectorAll('#tableBody tr td:first-child input.date-input, #tableBody tr td:first-child input[type="date"]');
            if (dateInputs.length > 0) {
                // Auto-update without confirmation
                let updatedCount = 0;
                dateInputs.forEach(dateInput => {
                    if (dateInput && dateInput.value !== currentDate) {
                        dateInput.value = currentDate;
                        const inputEvent = new Event('input', { bubbles: true });
                        const changeEvent = new Event('change', { bubbles: true });
                        dateInput.dispatchEvent(inputEvent);
                        dateInput.dispatchEvent(changeEvent);
                        updatedCount++;
                    }
                });
                
                if (updatedCount > 0 && typeof showAutoFillFeedback === 'function') {
                    showAutoFillFeedback(null, `✓ Auto-updated ${updatedCount} row(s) to ${currentDate}`);
                }
            }
        }
        lastReportDate = currentDate;
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
    
    // Function to ensure right sidebar is expanded/visible
    function ensureSidebarVisible() {
        // Check if we're on any supported page
        const supportedPages = [
            'vatable_sales.html', 'vatable_purchases.html', 'vatable_expenses.html',
            'non_vat_purchases.html', 'non_vat_expenses.html', 'capex.html',
            'taxes_licenses.html', 'purchase_returns.html', 'compensation.html'
        ];
        
        const isSupportedPage = supportedPages.some(page => window.location.pathname.includes(page));
        
        if (!isSupportedPage) {
            return;
        }
        
        // Get sidebar elements
        const rightSidebar = document.getElementById('rightSidebar');
        const body = document.body;
        
        if (!rightSidebar) {
            console.log('Right sidebar not found, retrying...');
            setTimeout(ensureSidebarVisible, 100);
            return;
        }
        
        // Make sure right sidebar is expanded
        if (!rightSidebar.classList.contains('sidebar-expanded')) {
            rightSidebar.classList.add('sidebar-expanded');
            body.classList.add('right-sidebar-expanded');
            
            // Update header button appearance if it exists
            const headerRightSidebarToggle = document.getElementById('headerRightSidebarToggle');
            if (headerRightSidebarToggle) {
                const icon = headerRightSidebarToggle.querySelector('i');
                if (icon) icon.style.transform = 'rotate(-90deg)';
            }
        }
        
        // Update right sidebar toggle button appearance
        const rightSidebarToggle = document.getElementById('rightSidebarToggle');
        if (rightSidebarToggle) {
            const icon = rightSidebarToggle.querySelector('i');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
        
        console.log('Sidebar visibility ensured for ' + currentModuleName);
    }
    
    // Function to create and show initial fill-up modal/welcome screen
    function showInitialFillUpForm() {
        // Check if we're on any supported page
        const supportedPages = [
            'vatable_sales.html', 'vatable_purchases.html', 'vatable_expenses.html',
            'non_vat_purchases.html', 'non_vat_expenses.html', 'capex.html',
            'taxes_licenses.html', 'purchase_returns.html', 'compensation.html'
        ];
        
        const isSupportedPage = supportedPages.some(page => window.location.pathname.includes(page));
        
        if (!isSupportedPage) {
            console.log('Not on a supported page, skipping modal');
            return;
        }
        
        // Detect module for proper messaging
        detectModule();
        
        // Check if company info is already filled (has at least name and TIN)
        const savedInfo = localStorage.getItem(currentStorageKey);
        let hasCompanyInfo = false;
        
        if (savedInfo) {
            try {
                const companyInfo = JSON.parse(savedInfo);
                if (companyInfo.name && companyInfo.name.trim() !== '' && 
                    companyInfo.tin && companyInfo.tin.trim() !== '') {
                    hasCompanyInfo = true;
                    console.log('Company info exists for ' + currentModuleName + ', skipping modal');
                }
            } catch(e) {
                console.warn('Error parsing saved company info', e);
            }
        }
        
        // Check if modal already exists
        if (document.getElementById('initialCompanyInfoModal')) {
            console.log('Modal already exists');
            return;
        }
        
        // If company info is already filled, just ensure sidebar is visible and exit
        if (hasCompanyInfo) {
            ensureSidebarVisible();
            return;
        }
        
        console.log('Showing initial fill-up modal for ' + currentModuleName);
        
        // Create modal overlay for initial fill-up
        const modal = document.createElement('div');
        modal.id = 'initialCompanyInfoModal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(8px);
            z-index: 20000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        `;
        
        // Add animation style if not already present
        if (!document.getElementById('modalAnimations')) {
            const style = document.createElement('style');
            style.id = 'modalAnimations';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
                @keyframes slideUp {
                    from { transform: translateY(30px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                @keyframes pulseGlow {
                    0% { box-shadow: 0 0 0 0 rgba(0, 255, 157, 0.4); }
                    70% { box-shadow: 0 0 0 10px rgba(0, 255, 157, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(0, 255, 157, 0); }
                }
            `;
            document.head.appendChild(style);
        }
        
        // General title for all modules
        const modalTitle = 'Company Information';
        const welcomeMessage = 'Please fill in your company information to get started. This information will be saved for future sessions.';
        
        modal.innerHTML = `
            <div style="background: linear-gradient(135deg, #0a1628 0%, #1a0a18 50%, #2a0a15 100%); border: 2px solid #00ff9d; border-radius: 24px; max-width: 550px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 0 50px rgba(0, 255, 157, 0.3); animation: slideUp 0.4s ease;">
                <div style="padding: 25px 30px; border-bottom: 1px solid rgba(0, 255, 157, 0.3); background: linear-gradient(135deg, rgba(0, 255, 157, 0.1), transparent);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-building" style="font-size: 28px; color: #00ff9d;"></i>
                        <h2 style="color: #00ff9d; margin: 0; font-weight: 700;">${modalTitle}</h2>
                    </div>
                    <p style="color: #80cbc4; margin-top: 10px; font-size: 14px;">${welcomeMessage}</p>
                </div>
                
                <div style="padding: 25px 30px;">
                    <div style="display: grid; gap: 18px;">
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-building" style="margin-right: 6px;"></i>Company Name <span style="color: #ff6b6b;">*</span>
                            </label>
                            <input type="text" id="initialCompanyName" placeholder="Enter your company name" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px; transition: all 0.2s;">
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-id-card"></i> TIN Number <span style="color: #ff6b6b;">*</span>
                            </label>
                            <input type="text" id="initialCompanyTIN" placeholder="000-000-000-000" maxlength="15" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px; font-family: monospace;">
                            <small style="color: #80cbc4; font-size: 11px; margin-top: 5px; display: block;">Format: 000-000-000-000 (12 digits)</small>
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-map-marker-alt"></i> Company Address <span style="color: #ff6b6b;">*</span>
                            </label>
                            <input type="text" id="initialCompanyAddress" placeholder="Enter company address" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-briefcase"></i> Line of Business <span style="color: #ff6b6b;">*</span>
                            </label>
                            <input type="text" id="initialLineOfBusiness" placeholder="e.g., Retail, Wholesale, Services" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-phone"></i> Telephone Number
                            </label>
                            <input type="tel" id="initialTelephone" placeholder="Enter telephone number" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-calendar-alt"></i> Report Date
                            </label>
                            <input type="date" id="initialReportDate" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-user-check"></i> Authorized Employee <span style="color: #ff6b6b;">*</span>
                            </label>
                            <input type="text" id="initialAuthorizedEmployee" placeholder="Enter authorized employee name" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; color: #4db8ff; margin-bottom: 6px; font-size: 13px; font-weight: 600;">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" id="initialEmail" placeholder="Enter email address" style="width: 100%; padding: 12px 15px; background: rgba(10, 20, 30, 0.8); border: 1px solid rgba(0, 255, 157, 0.3); border-radius: 12px; color: #e0e0ff; font-size: 14px;">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button id="saveInitialInfoBtn" style="flex: 1; background: linear-gradient(135deg, #00cc7a, #00995a); border: none; padding: 14px; border-radius: 40px; color: white; font-weight: 700; font-size: 16px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-save"></i> Save & Continue
                        </button>
                        <button id="skipInitialInfoBtn" style="background: rgba(100, 100, 100, 0.3); border: 1px solid rgba(255, 255, 255, 0.2); padding: 14px; border-radius: 40px; color: #ccc; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-times"></i> Skip
                        </button>
                    </div>
                </div>
                
                <div style="padding: 15px 30px; border-top: 1px solid rgba(0, 255, 157, 0.2); background: rgba(0, 0, 0, 0.3); border-radius: 0 0 24px 24px;">
                    <p style="color: #80cbc4; font-size: 11px; text-align: center; margin: 0;">
                        <i class="fas fa-info-circle"></i> Required fields marked with <span style="color: #ff6b6b;">*</span> must be filled. You can always update this information in the Company Info sidebar.
                    </p>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Set default date
        const today = new Date().toISOString().split('T')[0];
        const initialReportDate = document.getElementById('initialReportDate');
        if (initialReportDate) {
            initialReportDate.value = today;
        }
        
        // Format TIN as user types
        const tinInput = document.getElementById('initialCompanyTIN');
        if (tinInput) {
            tinInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 12) value = value.substring(0, 12);
                
                if (value.length > 9) value = value.substring(0, 9) + '-' + value.substring(9);
                if (value.length > 6) value = value.substring(0, 6) + '-' + value.substring(6);
                if (value.length > 3) value = value.substring(0, 3) + '-' + value.substring(3);
                
                if (this.value !== value) this.value = value;
            });
        }
        
        // Focus animation on inputs
        const inputs = modal.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = '#00ff9d';
                this.style.boxShadow = '0 0 10px rgba(0, 255, 157, 0.3)';
                this.style.animation = 'pulseGlow 0.5s ease';
            });
            input.addEventListener('blur', function() {
                this.style.borderColor = 'rgba(0, 255, 157, 0.3)';
                this.style.boxShadow = 'none';
            });
        });
        
        // Save button handler
        const saveBtn = document.getElementById('saveInitialInfoBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                // Get values
                const companyName = document.getElementById('initialCompanyName')?.value.trim() || '';
                const companyTIN = document.getElementById('initialCompanyTIN')?.value.trim() || '';
                const companyAddress = document.getElementById('initialCompanyAddress')?.value.trim() || '';
                const lineOfBusiness = document.getElementById('initialLineOfBusiness')?.value.trim() || '';
                const telephone = document.getElementById('initialTelephone')?.value.trim() || '';
                const reportDate = document.getElementById('initialReportDate')?.value || today;
                const authorizedEmployee = document.getElementById('initialAuthorizedEmployee')?.value.trim() || '';
                const email = document.getElementById('initialEmail')?.value.trim() || '';
                
                // Validate required fields
                if (!companyName) {
                    showInputError('initialCompanyName', 'Company Name is required');
                    return;
                }
                if (!companyTIN) {
                    showInputError('initialCompanyTIN', 'TIN Number is required');
                    return;
                }
                if (!companyAddress) {
                    showInputError('initialCompanyAddress', 'Company Address is required');
                    return;
                }
                if (!lineOfBusiness) {
                    showInputError('initialLineOfBusiness', 'Line of Business is required');
                    return;
                }
                if (!authorizedEmployee) {
                    showInputError('initialAuthorizedEmployee', 'Authorized Employee is required');
                    return;
                }
                
                // Save to main form fields
                if (document.getElementById('companyName')) document.getElementById('companyName').value = companyName;
                if (document.getElementById('companyTIN')) document.getElementById('companyTIN').value = companyTIN;
                if (document.getElementById('companyAddress')) document.getElementById('companyAddress').value = companyAddress;
                if (document.getElementById('lineOfBusiness')) document.getElementById('lineOfBusiness').value = lineOfBusiness;
                if (document.getElementById('telephone')) document.getElementById('telephone').value = telephone;
                if (document.getElementById('reportDate')) document.getElementById('reportDate').value = reportDate;
                if (document.getElementById('authorizedEmployee')) document.getElementById('authorizedEmployee').value = authorizedEmployee;
                if (document.getElementById('email')) document.getElementById('email').value = email;
                
                // Save to localStorage
                saveCompanyInfo();
                
                // Update all dates in table with the selected report date
                updateAllDatesInTable();
                
                // Ensure sidebar is visible
                ensureSidebarVisible();
                
                // Close modal with animation
                modal.style.animation = 'fadeOut 0.2s ease';
                setTimeout(() => {
                    modal.remove();
                }, 200);
                
                // Show success message
                if (typeof showAutoFillFeedback === 'function') {
                    showAutoFillFeedback(null, '✓ Company information saved successfully!');
                } else if (typeof showToast === 'function') {
                    showToast('Company information saved successfully!', 'success');
                } else {
                    alert('Company information saved successfully!');
                }
            });
        }
        
        // Skip button handler
        const skipBtn = document.getElementById('skipInitialInfoBtn');
        if (skipBtn) {
            skipBtn.addEventListener('click', function() {
                // Just ensure sidebar is visible
                ensureSidebarVisible();
                
                // Close modal
                modal.style.animation = 'fadeOut 0.2s ease';
                setTimeout(() => {
                    modal.remove();
                }, 200);
            });
        }
        
        function showInputError(inputId, message) {
            const input = document.getElementById(inputId);
            if (input) {
                input.style.borderColor = '#ff6b6b';
                input.style.boxShadow = '0 0 10px rgba(255, 107, 107, 0.5)';
                
                // Remove existing error message
                const existingError = input.parentElement?.querySelector('.error-message');
                if (existingError) existingError.remove();
                
                // Add error message
                const errorSpan = document.createElement('div');
                errorSpan.className = 'error-message';
                errorSpan.style.cssText = 'color: #ff6b6b; font-size: 11px; margin-top: 5px;';
                errorSpan.textContent = message;
                input.parentElement?.appendChild(errorSpan);
                
                // Remove error after 3 seconds
                setTimeout(() => {
                    if (input.style.borderColor === 'rgb(255, 107, 107)') {
                        input.style.borderColor = 'rgba(0, 255, 157, 0.3)';
                        input.style.boxShadow = 'none';
                    }
                    if (errorSpan.parentElement) errorSpan.remove();
                }, 3000);
            }
        }
    }
    
    // Initialize company info sidebar
    function initCompanyInfo() {
        console.log('Initializing company info...');
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
        
        // NEW: Add date update functionality to report date picker
        if (reportDate) {
            // Add a button next to the date picker for manual update
            const dateGroup = reportDate.closest('.info-group');
            if (dateGroup && !dateGroup.querySelector('.update-dates-btn')) {
                const updateBtn = document.createElement('button');
                updateBtn.type = 'button';
                updateBtn.className = 'sidebar-btn';
                updateBtn.style.marginTop = '8px';
                updateBtn.style.width = '100%';
                updateBtn.style.background = 'linear-gradient(135deg, rgba(80, 180, 255, 0.2), rgba(60, 150, 220, 0.3))';
                updateBtn.innerHTML = '<i class="fas fa-calendar-alt"></i> <span class="sidebar-btn-text">Apply Date to All Rows</span>';
                updateBtn.addEventListener('click', promptUpdateAllDates);
                dateGroup.appendChild(updateBtn);
            }
            
            // Auto-update when date changes (optional - uncomment if desired)
            // reportDate.addEventListener('change', checkAndAutoUpdateDates);
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
        
        // Show initial fill-up form for any supported page
        setTimeout(() => {
            showInitialFillUpForm();
        }, 500);
    }
    
    // Expose functions globally
    window.saveCompanyInfo = saveCompanyInfo;
    window.loadCompanyInfo = loadCompanyInfo;
    window.clearCompanyInfo = clearCompanyInfo;
    window.formatTIN = formatTIN;
    window.validateTIN = validateTIN;
    window.initCompanyInfo = initCompanyInfo;
    window.ensureSidebarVisible = ensureSidebarVisible;
    window.updateAllDatesInTable = updateAllDatesInTable;
    window.promptUpdateAllDates = promptUpdateAllDates;
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded - initializing company info');
            initCompanyInfo();
        });
    } else {
        console.log('Document already loaded - initializing company info');
        initCompanyInfo();
    }
})();