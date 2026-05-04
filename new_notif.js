// ============================================================
// CENTERED NOTIFICATION SYSTEM FOR SUPPLIER/CUSTOMER REGISTRATION
// Shows big centered notification ONLY when:
// 1. New supplier/customer is added (Name + TIN)
// 2. TIN or Name already exists (conflict)
// ============================================================

(function() {
    // Create notification overlay if it doesn't exist
    let notificationOverlay = null;
    
    function createNotificationOverlay() {
        if (document.getElementById('global-notification-overlay')) return;
        
        const overlay = document.createElement('div');
        overlay.id = 'global-notification-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 20000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeInOverlay 0.3s ease;
        `;
        
        const notificationBox = document.createElement('div');
        notificationBox.id = 'global-notification-box';
        notificationBox.style.cssText = `
            background: linear-gradient(135deg, #0a1628 0%, #1a0a18 100%);
            border-radius: 24px;
            padding: 30px 40px;
            max-width: 500px;
            min-width: 350px;
            text-align: center;
            box-shadow: 0 0 50px rgba(0, 255, 157, 0.3), 0 20px 40px rgba(0, 0, 0, 0.5);
            border: 2px solid #00ff9d;
            animation: slideInNotification 0.3s ease;
            transform: scale(1);
        `;
        
        notificationBox.innerHTML = `
            <div id="notification-icon" style="font-size: 60px; margin-bottom: 20px;">
                <i class="fas fa-check-circle" style="color: #00ff9d;"></i>
            </div>
            <div id="notification-title" style="font-size: 24px; font-weight: bold; margin-bottom: 15px; color: #00ff9d;">
                Success!
            </div>
            <div id="notification-message" style="font-size: 16px; color: #d4eaff; margin-bottom: 25px; line-height: 1.5;">
                Supplier has been registered successfully.
            </div>
            <button id="notification-ok-btn" style="
                background: linear-gradient(135deg, #00ff9d, #00cc7a);
                border: none;
                color: #0a1628;
                font-size: 16px;
                font-weight: bold;
                padding: 12px 30px;
                border-radius: 40px;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: 0 0 15px rgba(0, 255, 157, 0.4);
                font-family: inherit;
            ">
                OK
            </button>
        `;
        
        overlay.appendChild(notificationBox);
        document.body.appendChild(overlay);
        notificationOverlay = overlay;
        
        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInOverlay {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideInNotification {
                from { 
                    opacity: 0;
                    transform: translateY(-50px) scale(0.9);
                }
                to { 
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        `;
        document.head.appendChild(style);
        
        // Add OK button event
        document.getElementById('notification-ok-btn').addEventListener('click', function() {
            notificationOverlay.style.display = 'none';
        });
        
        // Also close when clicking outside the box
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });
        
        // Escape key closes
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && notificationOverlay && notificationOverlay.style.display === 'flex') {
                notificationOverlay.style.display = 'none';
            }
        });
    }
    
    function showNotification(type, title, message, details = null) {
        createNotificationOverlay();
        
        const iconElement = document.getElementById('notification-icon');
        const titleElement = document.getElementById('notification-title');
        const messageElement = document.getElementById('notification-message');
        const okBtn = document.getElementById('notification-ok-btn');
        
        // Set icon and colors based on type
        if (type === 'success') {
            iconElement.innerHTML = '<i class="fas fa-check-circle" style="color: #00ff9d; font-size: 60px;"></i>';
            titleElement.style.color = '#00ff9d';
            okBtn.style.background = 'linear-gradient(135deg, #00ff9d, #00cc7a)';
            okBtn.style.color = '#0a1628';
        } else if (type === 'warning') {
            iconElement.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ffaa00; font-size: 60px;"></i>';
            titleElement.style.color = '#ffaa00';
            okBtn.style.background = 'linear-gradient(135deg, #ffaa00, #cc8800)';
            okBtn.style.color = '#0a1628';
        } else if (type === 'error') {
            iconElement.innerHTML = '<i class="fas fa-times-circle" style="color: #ff5555; font-size: 60px;"></i>';
            titleElement.style.color = '#ff5555';
            okBtn.style.background = 'linear-gradient(135deg, #ff5555, #cc4444)';
            okBtn.style.color = '#ffffff';
        }
        
        titleElement.textContent = title;
        
        // Build message with details if provided
        if (details) {
            messageElement.innerHTML = `
                ${message}<br>
                <div style="
                    background: rgba(255,255,255,0.08);
                    border-radius: 12px;
                    padding: 12px;
                    margin-top: 12px;
                    font-size: 13px;
                    text-align: left;
                    font-family: monospace;
                ">
                    ${details}
                </div>
            `;
        } else {
            messageElement.innerHTML = message;
        }
        
        notificationOverlay.style.display = 'flex';
        
        // Auto hide after 5 seconds for success
        if (type === 'success') {
            setTimeout(() => {
                if (notificationOverlay && notificationOverlay.style.display === 'flex') {
                    notificationOverlay.style.display = 'none';
                }
            }, 4000);
        }
    }
    
    // ========== HOOK INTO SUPPLIER/CUSTOMER REGISTRATION FUNCTIONS ==========
    
    // Track which names/TINs have already shown a notification to avoid spam
    const shownConflicts = new Set();
    
    function clearConflictCache() {
        setTimeout(() => {
            shownConflicts.clear();
        }, 3000);
    }
    
    // For SALES (vatable_sales.html)
    function hookSalesNotifications() {
        if (typeof window.addCustomerPair === 'function') {
            const originalAddCustomerPair = window.addCustomerPair;
            window.addCustomerPair = function(name, tin, salesType, address) {
                const existingNameCheck = window.vatCustomerDB?.list().find(p => p.name === name);
                const existingTinCheck = window.vatCustomerDB?.list().find(p => p.tin === tin);
                const conflictKey = `name:${name}`;
                const tinConflictKey = `tin:${tin}`;
                
                // Check for NAME conflict (same name, different TIN)
                if (existingNameCheck && existingNameCheck.tin !== tin) {
                    if (!shownConflicts.has(conflictKey)) {
                        shownConflicts.add(conflictKey);
                        showNotification('error', 'NAME ALREADY TAKEN', 
                            `The name "${name}" is already registered with a different TIN.`,
                            `📋 Existing: ${name} → ${existingNameCheck.tin}<br>⚠️ Attempted: ${name} → ${tin}`);
                        clearConflictCache();
                    }
                    return false;
                }
                
                // Check for TIN conflict (same TIN, different name)
                if (existingTinCheck && existingTinCheck.name !== name) {
                    if (!shownConflicts.has(tinConflictKey)) {
                        shownConflicts.add(tinConflictKey);
                        showNotification('error', 'TIN ALREADY TAKEN', 
                            `TIN ${tin} is already registered to a different customer.`,
                            `📋 Existing: ${existingTinCheck.name} → ${tin}<br>⚠️ Attempted: ${name} → ${tin}`);
                        clearConflictCache();
                    }
                    return false;
                }
                
                const result = originalAddCustomerPair(name, tin, salesType, address);
                
                // Show success for NEW registration (not update)
                if (result && !existingNameCheck) {
                    showNotification('success', 'NEW CUSTOMER REGISTERED', 
                        `✓ "${name}" has been added to the registered customers list.`,
                        `TIN: ${tin}`);
                }
                
                return result;
            };
            console.log('[Notifications] Hooked into addCustomerPair for Sales');
        }
    }
    
    // For PURCHASES (vatable_purchases.html)
    function hookPurchasesNotifications() {
        if (typeof window.addSupplierPair === 'function') {
            const originalAddSupplierPair = window.addSupplierPair;
            window.addSupplierPair = function(name, tin, address) {
                const existingNameCheck = window.vatSupplierDB?.list().find(p => p.name === name);
                const existingTinCheck = window.vatSupplierDB?.list().find(p => p.tin === tin);
                const conflictKey = `name:${name}`;
                const tinConflictKey = `tin:${tin}`;
                
                // Check for NAME conflict (same name, different TIN)
                if (existingNameCheck && existingNameCheck.tin !== tin) {
                    if (!shownConflicts.has(conflictKey)) {
                        shownConflicts.add(conflictKey);
                        showNotification('error', 'NAME ALREADY TAKEN', 
                            `The name "${name}" is already registered with a different TIN.`,
                            `📋 Existing: ${name} → ${existingNameCheck.tin}<br>⚠️ Attempted: ${name} → ${tin}`);
                        clearConflictCache();
                    }
                    return false;
                }
                
                // Check for TIN conflict (same TIN, different name)
                if (existingTinCheck && existingTinCheck.name !== name) {
                    if (!shownConflicts.has(tinConflictKey)) {
                        shownConflicts.add(tinConflictKey);
                        showNotification('error', 'TIN ALREADY TAKEN', 
                            `TIN ${tin} is already registered to a different supplier.`,
                            `📋 Existing: ${existingTinCheck.name} → ${tin}<br>⚠️ Attempted: ${name} → ${tin}`);
                        clearConflictCache();
                    }
                    return false;
                }
                
                const result = originalAddSupplierPair(name, tin, address);
                
                // Show success for NEW registration (not update)
                if (result && !existingNameCheck) {
                    showNotification('success', 'NEW SUPPLIER REGISTERED', 
                        `✓ "${name}" has been added to the registered suppliers list.`,
                        `TIN: ${tin}`);
                }
                
                return result;
            };
            console.log('[Notifications] Hooked into addSupplierPair for Purchases');
        }
    }
    
    // For EXPENSES (vatable_expenses.html)
    function hookExpensesNotifications() {
        if (typeof window.addExpensesSupplierPair === 'function') {
            const originalAddExpensesSupplierPair = window.addExpensesSupplierPair;
            window.addExpensesSupplierPair = function(name, tin, address) {
                const existingNameCheck = window.vatExpensesSupplierDB?.list().find(p => p.name === name);
                const existingTinCheck = window.vatExpensesSupplierDB?.list().find(p => p.tin === tin);
                const conflictKey = `name:${name}`;
                const tinConflictKey = `tin:${tin}`;
                
                // Check for NAME conflict (same name, different TIN)
                if (existingNameCheck && existingNameCheck.tin !== tin) {
                    if (!shownConflicts.has(conflictKey)) {
                        shownConflicts.add(conflictKey);
                        showNotification('error', 'NAME ALREADY TAKEN', 
                            `The name "${name}" is already registered with a different TIN.`,
                            `📋 Existing: ${name} → ${existingNameCheck.tin}<br>⚠️ Attempted: ${name} → ${tin}`);
                        clearConflictCache();
                    }
                    return false;
                }
                
                // Check for TIN conflict (same TIN, different name)
                if (existingTinCheck && existingTinCheck.name !== name) {
                    if (!shownConflicts.has(tinConflictKey)) {
                        shownConflicts.add(tinConflictKey);
                        showNotification('error', 'TIN ALREADY TAKEN', 
                            `TIN ${tin} is already registered to a different supplier.`,
                            `📋 Existing: ${existingTinCheck.name} → ${tin}<br>⚠️ Attempted: ${name} → ${tin}`);
                        clearConflictCache();
                    }
                    return false;
                }
                
                const result = originalAddExpensesSupplierPair(name, tin, address);
                
                // Show success for NEW registration (not update)
                if (result && !existingNameCheck) {
                    showNotification('success', 'NEW SUPPLIER REGISTERED', 
                        `✓ "${name}" has been added to the registered suppliers list.`,
                        `TIN: ${tin}`);
                }
                
                return result;
            };
            console.log('[Notifications] Hooked into addExpensesSupplierPair for Expenses');
        }
    }
    
    // ========== INITIALIZE BASED ON PAGE TYPE ==========
    function initNotifications() {
        createNotificationOverlay();
        
        const pageTitle = document.title.toLowerCase();
        
        if (pageTitle.includes('sales')) {
            hookSalesNotifications();
            console.log('[Notifications] Active for SALES - New customer registration only');
        } else if (pageTitle.includes('purchases')) {
            hookPurchasesNotifications();
            console.log('[Notifications] Active for PURCHASES - New supplier registration only');
        } else if (pageTitle.includes('expenses')) {
            hookExpensesNotifications();
            console.log('[Notifications] Active for EXPENSES - New supplier registration only');
        }
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotifications);
    } else {
        initNotifications();
    }
    
    // Expose functions globally
    window.showGlobalNotification = showNotification;
})();