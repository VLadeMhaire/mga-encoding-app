<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, 'client')");
        // For demo: store plain password. In production, use password_hash()
        $stmt->bind_param("sss", $username, $password, $email);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'client';
            header('Location: index.php');
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MGA&A Encoding App</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 1rem;
        }
        
        input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #2196f3;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background: #0d47a1;
            transform: scale(1.02);
        }
        
        .error {
            background: rgba(244, 67, 54, 0.3);
            border: 1px solid #f44336;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #90caf9;
            text-decoration: none;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Create Account</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Choose a username">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email (optional)">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a password">
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <div class="links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>