<?php
require_once 'config.php';
requireAdmin();

// Handle user actions
if (isset($_GET['action'])) {
    $user_id = intval($_GET['id']);
    
    if ($_GET['action'] == 'delete' && $user_id != 1) { // Prevent deleting admin
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND id != 1");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = "User deleted successfully!";
        } else {
            $error = "Error deleting user.";
        }
    } elseif ($_GET['action'] == 'toggle_status') {
        // You can add user status field if needed
    }
}

// Fetch all users
$users_query = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MGA&A Encoding App</title>
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
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 25px;
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
        
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e65100;
            transform: scale(1.05);
        }
        
        .table-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        th {
            background: rgba(33, 150, 243, 0.3);
            font-weight: bold;
        }
        
        tr:hover {
            background: rgba(255,255,255,0.05);
        }
        
        .user-type {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .user-type.admin {
            background: rgba(183, 28, 28, 0.3);
            color: #ffcdd2;
        }
        
        .user-type.client {
            background: rgba(33, 150, 243, 0.3);
            color: #bbdefb;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            backdrop-filter: blur(5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2196f3;
        }
        
        .action-icons {
            display: flex;
            gap: 10px;
        }
        
        .icon-btn {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <header>
            <h1>User Management</h1>
            <p>Manage all user accounts in the system</p>
        </header>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $users_query->num_rows; ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $admin_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'")->fetch_assoc();
                    echo $admin_count['count'];
                    ?>
                </div>
                <div>Admin Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $client_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'client'")->fetch_assoc();
                    echo $client_count['count'];
                    ?>
                </div>
                <div>Client Users</div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="export_all.php" class="btn btn-success">Export All Data</a>
            <a href="admin_dashboard.php" class="btn btn-primary">View Submissions</a>
            <a href="javascript:void(0)" onclick="showAddUserModal()" class="btn btn-warning">Add New User</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_query->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($user['username']); ?>
                            <?php if ($user['id'] == 1): ?>
                                <span style="color: #ff9800; font-size: 0.9rem;">(Main Admin)</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['email'] ? htmlspecialchars($user['email']) : 'N/A'; ?></td>
                        <td>
                            <span class="user-type <?php echo $user['user_type']; ?>">
                                <?php echo ucfirst($user['user_type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-icons">
                                <?php if ($user['id'] != 1): ?>
                                <a href="view_user.php?id=<?php echo $user['id']; ?>" class="icon-btn btn-primary" title="View Details">👁️</a>
                                <a href="manage_users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                   class="icon-btn btn-danger" 
                                   title="Delete User"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">🗑️</a>
                                <?php else: ?>
                                <span style="opacity: 0.5; font-size: 0.9rem;">Protected</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%); padding: 30px; border-radius: 15px; width: 90%; max-width: 500px; border: 2px solid rgba(255,255,255,0.2);">
            <h2 style="margin-bottom: 20px;">Add New User</h2>
            <form method="POST" action="add_user.php" id="addUserForm">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Username</label>
                    <input type="text" name="username" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: white;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Email</label>
                    <input type="email" name="email" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: white;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: white;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px;">User Type</label>
                    <select name="user_type" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.5); color: white;">
                        <option value="client">Client</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="hideAddUserModal()" style="padding: 10px 20px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 10px 20px; background: #4caf50; color: white; border: none; border-radius: 5px; cursor: pointer;">Add User</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }
        
        function hideAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('addUserModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAddUserModal();
            }
        });
    </script>
</body>
</html>