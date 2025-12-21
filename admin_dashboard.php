<?php
require_once 'config.php';
requireAdmin();

// Fetch all submissions
$query = "
    SELECT i.*, u.username 
    FROM information i 
    JOIN users u ON i.user_id = u.id 
    ORDER BY i.submitted_at DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MGA&A Encoding App</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
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
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .btn-view {
            background: #4caf50;
            color: white;
        }
        
        .btn-export {
            background: #ff9800;
            color: white;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .export-btn {
            background: #2196f3;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .export-btn:hover {
            background: #0d47a1;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <header>
            <h1>Admin Dashboard</h1>
            <p>Monitor all user submissions and manage data</p>
        </header>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $result->num_rows; ?></div>
                <div>Total Submissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $user_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'client'")->fetch_assoc();
                    echo $user_count['count'];
                    ?>
                </div>
                <div>Total Clients</div>
            </div>
        </div>
        
        <a href="export_excel.php" class="export-btn">Export All to Excel</a>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>TIN Number</th>
                        <th>Month/Year</th>
                        <th>Submitted By</th>
                        <th>Contact</th>
                        <th>Submission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td><?php echo $row['tin_number']; ?></td>
                        <td><?php echo $row['month'] . ' ' . $row['year']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo $row['contact_number']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['submitted_at'])); ?></td>
                        <td>
                            <a href="view_submission.php?id=<?php echo $row['id']; ?>" class="btn btn-view">View</a>
                            <a href="export_user.php?user_id=<?php echo $row['user_id']; ?>" class="btn btn-export">Export</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>