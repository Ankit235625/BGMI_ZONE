<?php
ob_start();
include '../includes/db.php';
include 'auth.php';
check_login();

$success = "";
$error = "";

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $conn->real_escape_string($_POST['new_status']);
    
    $update_sql = "UPDATE users SET status = '$new_status' WHERE id = $user_id";
    if ($conn->query($update_sql)) {
        ob_clean();
        header("Location: users.php?msg=" . ($new_status == 'active' ? 'enabled' : 'disabled'));
        exit();
    } else {
        $error = "Error updating status: " . $conn->error;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'enabled') $success = "User enabled successfully!";
    if ($_GET['msg'] == 'disabled') $success = "User disabled successfully!";
}

// Fetch all users
$users_sql = "SELECT id, username, email, bgmi_id, phone, status, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #0a0a0a; padding: 20px; border-right: 1px solid #333; }
        .main-content { flex: 1; padding: 40px; background: #050505; }
        .nav-link { display: block; padding: 12px; color: #888; text-decoration: none; margin-bottom: 5px; border-radius: 4px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,180,0,0.1); color: #ffb400; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(255,255,255,0.02); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #222; }
        th { color: #ffb400; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge-active { background: rgba(0,255,136,0.1); color: #00ff88; border: 1px solid #00ff88; }
        .badge-disabled { background: rgba(255,68,68,0.1); color: #ff4444; border: 1px solid #ff4444; }
        
        .btn-sm { padding: 5px 10px; font-size: 0.75rem; }
        .btn-disable { background: #ff4444; color: #fff; border: none; }
        .btn-enable { background: #00ff88; color: #000; border: none; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2 style="color: #ffb400; margin-bottom: 30px;">BGMI<span style="color:#fff">ZONE</span></h2>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="tournaments.php" class="nav-link">Tournaments</a>
            <a href="view_registrations.php" class="nav-link">Registrations</a>
            <a href="users.php" class="nav-link active">Manage Users</a>
            <a href="logout.php" class="nav-link" style="margin-top: 50px; color: #ff4444;">Logout</a>
        </div>
        
        <div class="main-content">
            <h2 class="section-title">Manage <span class="text-primary">Users</span></h2>
            
            <?php if($success): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>BGMI ID</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['bgmi_id']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <?php if($row['status'] == 'active'): ?>
                                            <input type="hidden" name="new_status" value="disabled">
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-disable" onclick="return confirm('Disable this user?')">Disable</button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="active">
                                            <button type="submit" name="toggle_status" class="btn btn-sm btn-enable">Enable</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
