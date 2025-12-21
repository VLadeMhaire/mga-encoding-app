<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if it's admin
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['user_type'] = 'admin';
        header('Location: index.php');
        exit();
    }
    
    // Check for regular users
    $stmt = $conn->prepare("SELECT id, username, password, user_type FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Check password (plain text match since we store it plain)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MGA&A Encoding App</title>
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
        
        .login-container {
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
        
        .demo-credentials {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            font-size: 0.9rem;
        }
        
        /* Fix for dropdown visibility */
        select {
            color: white !important;
            background: rgba(0, 0, 0, 0.7) !important;
        }
        
        select option {
            background: #1a237e !important;
            color: white !important;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>MGA&A Encoding App</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="links">
            <p>Don't have an account? <a href="register.php">Sign up here</a></p>
        </div>
        
        <div class="demo-credentials">
            <p><strong>Demo Credentials:</strong></p>
            <p>Admin: admin / password123</p>
            <p>Client: client / client123</p>
        </div>
    </div>
</body>
</html>