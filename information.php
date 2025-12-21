<?php
require_once 'config.php';
requireLogin();

// Prevent admin users from accessing encoding forms
if (isAdmin()) {
    header('Location: index.php');
    exit();
}

// Check if user already has company information
$user_id = $_SESSION['user_id'];
$check_stmt = $conn->prepare("SELECT id FROM information WHERE user_id = ?");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

$has_existing_info = $check_result->num_rows > 0;
$existing_info_id = null;

if ($has_existing_info) {
    $existing_info = $check_result->fetch_assoc();
    $existing_info_id = $existing_info['id'];
    
    // If user is trying to access the form but already has info, redirect to edit page
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: edit_data.php?type=information&id=' . $existing_info_id);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check again to prevent double submission
    $check_stmt = $conn->prepare("SELECT id FROM information WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "You have already submitted company information. You can only submit once.";
    } else {
        $company_name = sanitizeInput($_POST['company_name']);
        $address = sanitizeInput($_POST['address']);
        $tin_number = $_POST['tin_number'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $authorized_employee = sanitizeInput($_POST['authorized_employee']);
        $contact_number = sanitizeInput($_POST['contact_number']);
        $email = sanitizeInput($_POST['email']);
        
        // Validate TIN number (12 digits)
        if (!validateTIN($tin_number)) {
            $error = "TIN Number must be exactly 12 digits (numbers only).";
        } else {
            $stmt = $conn->prepare("INSERT INTO information (user_id, company_name, address, tin_number, month, year, authorized_employee, contact_number, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssss", $user_id, $company_name, $address, $tin_number, $month, $year, $authorized_employee, $contact_number, $email);
            
            if ($stmt->execute()) {
                $success = "Information saved successfully! You cannot submit another information form.";
                logActivity("created_company_info", "Company: " . $company_name);
                
                // Set flag to prevent form display
                $has_existing_info = true;
                $existing_info_id = $stmt->insert_id;
            } else {
                $error = "Error saving information. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Information - MGA&A Encoding App</title>
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
            max-width: 800px;
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
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .required::after {
            content: " *";
            color: #f44336;
        }
        
        input, textarea {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        select {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            font-size: 1rem;
        }
        
        select option {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #2196f3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.3);
        }
        
        input::placeholder, textarea::placeholder {
            color: rgba(255,255,255,0.7);
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
        
        .info-box {
            background: rgba(33, 150, 243, 0.2);
            border: 1px solid rgba(33, 150, 243, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .info-box h3 {
            color: #90caf9;
            margin-bottom: 10px;
        }
        
        .info-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e65100;
            transform: scale(1.05);
        }
        
        .input-hint {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <header>
            <h1>Company Information</h1>
            <p>Enter your company details and contact information</p>
        </header>
        
        <div class="form-container">
            <?php if ($has_existing_info): ?>
                <div class="info-box">
                    <div class="info-icon">✅</div>
                    <h3>Company Information Already Submitted</h3>
                    <p>You have already submitted your company information. Each account can only have one information entry.</p>
                    <p>You can view or edit your existing information.</p>
                    
                    <div class="action-buttons">
                        <a href="my_submissions.php" class="btn btn-primary">View My Submissions</a>
                        <a href="edit_data.php?type=information&id=<?php echo $existing_info_id; ?>" class="btn btn-warning">Edit Information</a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (isset($success)): ?>
                    <div class="message success"><?php echo $success; ?></div>
                    <div class="action-buttons">
                        <a href="my_submissions.php" class="btn btn-primary">View My Submissions</a>
                        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                <?php else: ?>
                    <?php if (isset($error)): ?>
                        <div class="message error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div class="info-box">
                        <div class="info-icon">📝</div>
                        <h3>Important Notice</h3>
                        <p>Each account can only submit company information <strong>once</strong>.</p>
                        <p>Please ensure all information is correct before submitting.</p>
                    </div>
                    
                    <form method="POST" action="" id="companyForm" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="company_name" class="required">Company Name</label>
                            <input type="text" id="company_name" name="company_name" required placeholder="Enter company name">
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="required">Address</label>
                            <textarea id="address" name="address" required placeholder="Enter complete address"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="tin_number" class="required">TIN Number</label>
                            <input type="text" id="tin_number" name="tin_number" 
                                   required 
                                   pattern="[0-9]{12}"
                                   maxlength="12"
                                   placeholder="000000000000"
                                   oninput="validateTIN(this)">
                            <div class="input-hint">Enter 12 digits only (numbers only)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="month" class="required">Month</label>
                            <select id="month" name="month" required>
                                <option value="">Select Month</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="year" class="required">Year</label>
                            <input type="number" id="year" name="year" 
                                   required 
                                   min="2000" 
                                   max="<?php echo date('Y') + 5; ?>" 
                                   placeholder="<?php echo date('Y'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="authorized_employee">Authorized Employee</label>
                            <input type="text" id="authorized_employee" name="authorized_employee" placeholder="Enter authorized employee name">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="tel" id="contact_number" name="contact_number" 
                                   pattern="[0-9+\-\s\(\)]{7,15}"
                                   placeholder="Enter contact number (e.g., 09171234567)">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter email address">
                        </div>
                        
                        <div style="text-align: center; margin-top: 40px;">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Information</button>
                        </div>
                    </form>
                <?php endif; ?>
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
        
        function validateForm() {
            const companyName = document.getElementById('company_name');
            const address = document.getElementById('address');
            const tin = document.getElementById('tin_number');
            const month = document.getElementById('month');
            const year = document.getElementById('year');
            const contact = document.getElementById('contact_number');
            const email = document.getElementById('email');
            
            // Validate required fields
            if (!companyName.value.trim()) {
                alert('Please enter company name');
                companyName.focus();
                return false;
            }
            
            if (!address.value.trim()) {
                alert('Please enter address');
                address.focus();
                return false;
            }
            
            // Validate TIN
            if (!/^\d{12}$/.test(tin.value)) {
                alert('TIN Number must be exactly 12 digits');
                tin.focus();
                return false;
            }
            
            if (!month.value) {
                alert('Please select month');
                month.focus();
                return false;
            }
            
            if (!year.value) {
                alert('Please enter year');
                year.focus();
                return false;
            }
            
            const currentYear = new Date().getFullYear();
            if (parseInt(year.value) < 2000 || parseInt(year.value) > currentYear + 5) {
                alert('Year must be between 2000 and ' + (currentYear + 5));
                year.focus();
                return false;
            }
            
            // Validate contact number if provided
            if (contact.value && !/^[0-9+\-\s\(\)]{7,15}$/.test(contact.value)) {
                alert('Please enter a valid contact number');
                contact.focus();
                return false;
            }
            
            // Validate email if provided
            if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                alert('Please enter a valid email address');
                email.focus();
                return false;
            }
            
            // Confirm submission (since user can only submit once)
            return confirm('Are you sure you want to submit? You can only submit company information once. This action cannot be undone.');
        }
        
        // Set current year as default
        document.addEventListener('DOMContentLoaded', function() {
            const currentYear = new Date().getFullYear();
            document.getElementById('year').value = currentYear;
            
            // Set current month as default
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const currentMonth = months[new Date().getMonth()];
            const monthSelect = document.getElementById('month');
            
            for (let i = 0; i < monthSelect.options.length; i++) {
                if (monthSelect.options[i].value === currentMonth) {
                    monthSelect.selectedIndex = i;
                    break;
                }
            }
        });
    </script>
</body>
</html>