<?php
require_once 'config.php';
requireLogin();

$is_admin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MGA&A Encoding App</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.3);
            padding: 10px 20px;
            border-radius: 25px;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .category-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            background: linear-gradient(135deg, rgba(33,150,243,0.3) 0%, rgba(13,71,161,0.3) 100%);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            border-color: rgba(255,255,255,0.3);
        }
        
        .category-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: linear-gradient(135deg, rgba(100,100,100,0.15) 0%, rgba(80,80,80,0.05) 100%);
        }
        
        .category-card.disabled:hover {
            transform: none;
            background: linear-gradient(135deg, rgba(100,100,100,0.15) 0%, rgba(80,80,80,0.05) 100%);
            box-shadow: none;
            border-color: rgba(255,255,255,0.1);
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .category-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .category-desc {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .admin-panel {
            background: linear-gradient(135deg, rgba(183,28,28,0.3) 0%, rgba(198,40,40,0.2) 100%);
            grid-column: 1 / -1;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .client-panel {
            background: linear-gradient(135deg, rgba(33, 150, 243, 0.3) 0%, rgba(13, 71, 161, 0.2) 100%);
            grid-column: 1 / -1;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .admin-panel h2, .client-panel h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .admin-buttons, .client-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0d47a1;
            transform: scale(1.05);
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-success:hover {
            background: #2e7d32;
            transform: scale(1.05);
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b71c1c;
            transform: scale(1.05);
        }
        
        .btn-logout {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-logout:hover {
            background: white;
            color: #1a237e;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            opacity: 0.7;
        }
        
        .welcome-message {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .user-type {
            font-size: 0.9rem;
            opacity: 0.8;
            font-style: italic;
        }
        
        .admin-notice {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(255, 193, 7, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>MGA&A Encoding App</h1>
            <p class="subtitle">Data Encoding and Management System</p>
            
            <div class="user-info">
                <div class="welcome-message">Welcome, <?php echo $_SESSION['username']; ?>!</div>
                <div class="user-type">(<?php echo ucfirst($_SESSION['user_type']); ?>)</div>
                <a href="logout.php" class="btn btn-logout" style="margin-top: 10px; padding: 8px 20px; font-size: 0.9rem;">Logout</a>
            </div>
        </header>
        
        <?php if ($is_admin): ?>
        <div class="admin-notice">
            <strong>Administrator Access:</strong> You have view-only access to all data. Encoding functions are disabled.
        </div>
        <?php endif; ?>
        
        <div class="main-content">
            <?php if (!$is_admin): ?>
            <!-- Regular User/Client Encoding Options (Hidden for Admin) -->
            <a href="information.php" class="category-card">
                <div class="category-icon">🏢</div>
                <h3 class="category-title">Information</h3>
                <p class="category-desc">Company details, TIN, Contact Information</p>
            </a>
            
            <a href="vatable_sales.php" class="category-card">
                <div class="category-icon">💰</div>
                <h3 class="category-title">Vatable Sales</h3>
                <p class="category-desc">VAT-able sales transactions</p>
            </a>
            
            <a href="non_vat_sales.php" class="category-card">
                <div class="category-icon">📊</div>
                <h3 class="category-title">Non-VAT Sales</h3>
                <p class="category-desc">Non-VAT sales transactions</p>
            </a>
            
            <a href="vatable_purchases.php" class="category-card">
                <div class="category-icon">🛒</div>
                <h3 class="category-title">Vatable Purchases</h3>
                <p class="category-desc">VAT-able purchase transactions</p>
            </a>
            
            <a href="non_vat_purchases.php" class="category-card">
                <div class="category-icon">📦</div>
                <h3 class="category-title">Non-VAT Purchases</h3>
                <p class="category-desc">Non-VAT purchase transactions</p>
            </a>
            
            <a href="vatable_expenses.php" class="category-card">
                <div class="category-icon">💸</div>
                <h3 class="category-title">Vatable Expenses</h3>
                <p class="category-desc">VAT-able expense transactions</p>
            </a>
            
            <a href="non_vat_expenses.php" class="category-card">
                <div class="category-icon">📝</div>
                <h3 class="category-title">Non-VAT Expenses</h3>
                <p class="category-desc">Non-VAT expense transactions</p>
            </a>
            
            <a href="capex.php" class="category-card">
                <div class="category-icon">🏗️</div>
                <h3 class="category-title">CAPEX</h3>
                <p class="category-desc">Capital expenditure records</p>
            </a>
            
            <a href="taxes_licenses.php" class="category-card">
                <div class="category-icon">📑</div>
                <h3 class="category-title">Taxes & Licenses</h3>
                <p class="category-desc">Tax and license payments</p>
            </a>
            
            <div class="client-panel">
                <h2>My Data Management</h2>
                <div class="client-buttons">
                    <a href="my_submissions.php" class="btn btn-success">View My Submissions</a>
                    <a href="export_my_data.php" class="btn btn-success">Export My Data to Excel</a>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Admin View-Only Options -->
            <a href="admin_dashboard.php" class="category-card">
                <div class="category-icon">📊</div>
                <h3 class="category-title">Admin Dashboard</h3>
                <p class="category-desc">Manage and review all data</p>
            </a>
            
            <a href="export_all.php" class="category-card">
                <div class="category-icon">📤</div>
                <h3 class="category-title">Export All Data</h3>
                <p class="category-desc">Export complete database</p>
            </a>
            
            <a href="manage_users.php" class="category-card">
                <div class="category-icon">👥</div>
                <h3 class="category-title">Manage Users</h3>
                <p class="category-desc">Add, edit, or delete users</p>
            </a>
            
            <a href="reports.php" class="category-card">
                <div class="category-icon">📈</div>
                <h3 class="category-title">Reports</h3>
                <p class="category-desc">Generate system reports</p>
            </a>
            <?php endif; ?>
            
            <?php if ($is_admin): ?>
            <div class="admin-panel">
                <h2>Administrator Controls</h2>
                <div class="admin-buttons">
                    <a href="admin_dashboard.php" class="btn btn-primary">Admin Dashboard</a>
                    <a href="export_all.php" class="btn btn-primary">Export All Data</a>
                    <a href="manage_users.php" class="btn btn-danger">Manage Users</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <footer>
            <p>MGA&A Encoding App © <?php echo date('Y'); ?>. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>