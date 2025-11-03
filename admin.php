<?php
// Simple authentication (enhance for production)
session_start();

$adminUsername = 'admin';
$adminPassword = 'admin123'; // Change this!

// Login logic
if (isset($_POST['login'])) {
    if ($_POST['username'] === $adminUsername && $_POST['password'] === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Invalid credentials";
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check if logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Database configuration for InfinityFree
// Get these values from your InfinityFree Control Panel > MySQL Databases
$servername = "sql200.infinityfree.com";  // Change to your host (e.g., sql305.infinityfree.com)
$username = "if0_40320548";  // Your database username (starts with if0_)
$password = "l7fHf5cziYI1";  // Your database password - FILL THIS IN
$dbname = "if0_40320548_students";  // Your database name

$registrations = array();

if ($isLoggedIn) {
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Fetch all registrations
        $sql = "SELECT * FROM registrations ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $registrations[] = $row;
            }
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Registrations</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .login-box {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .login-box h2 {
            text-align: center;
            color: #667eea;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .admin-panel {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .header h1 {
            color: #667eea;
        }
        
        .logout-btn {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-box h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-box p {
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .no-records {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .scroll-container {
            overflow-x: auto;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$isLoggedIn): ?>
            <div class="login-box">
                <h2>Admin Login</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn">Login</button>
                </form>
            </div>
        <?php else: ?>
            <div class="admin-panel">
                <div class="header">
                    <h1>Registration Management</h1>
                    <a href="?logout" class="logout-btn">Logout</a>
                </div>
                
                <div class="stats">
                    <div class="stat-box">
                        <h3><?php echo count($registrations); ?></h3>
                        <p>Total Registrations</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo date('M d, Y'); ?></h3>
                        <p>Current Date</p>
                    </div>
                </div>
                
                <?php if (isset($dbError)): ?>
                    <div class="error">Database Error: <?php echo $dbError; ?></div>
                <?php endif; ?>
                
                <?php if (empty($registrations)): ?>
                    <div class="no-records">No registrations found.</div>
                <?php else: ?>
                    <div class="scroll-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>DOB</th>
                                    <th>Gender</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Qualification</th>
                                    <th>Registered On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $reg): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($reg['id']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['dob']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['city']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['state']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['qualification']); ?></td>
                                        <td><?php echo htmlspecialchars($reg['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>