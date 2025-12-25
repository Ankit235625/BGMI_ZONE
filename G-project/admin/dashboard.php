<?php
include '../includes/db.php';
include 'auth.php';
check_login();

// Fetch counts
$tournaments_count = $conn->query("SELECT count(*) as total FROM tournaments")->fetch_assoc()['total'];
$registrations_count = $conn->query("SELECT count(*) as total FROM registrations")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            background: #1a1a1a;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            border-right: 1px solid #333;
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
        }
        .nav-item {
            display: block;
            padding: 15px;
            color: #b3b3b3;
            font-weight: 600;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary-color);
        }
        .stat-card {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #333;
            text-align: center;
        }
        .stat-number {
            font-size: 3rem;
            color: var(--primary-color);
            margin: 10px 0;
            font-weight: bold;
        }
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 class="text-primary text-center" style="margin-bottom: 40px;">BGMI<span style="color:#fff">ADMIN</span></h2>
    <a href="dashboard.php" class="nav-item active">Dashboard</a>
    <a href="tournaments.php" class="nav-item">Manage Tournaments</a>
    <a href="registrations.php" class="nav-item">View Registrations</a>
    <a href="users.php" class="nav-item">Manage Users</a>
    <a href="../index.php" class="nav-item" target="_blank">View Site</a>
    <a href="logout.php" class="nav-item" style="color: #ff4444;">Logout</a>
</div>

<div class="main-content">
    <h2 class="section-title">Dashboard Overview</h2>
    
    <div class="grid-stats">
        <div class="stat-card">
            <h3>Total Tournaments</h3>
            <div class="stat-number"><?php echo $tournaments_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Teams</h3>
            <div class="stat-number"><?php echo $registrations_count; ?></div>
        </div>
    </div>
    
    <h3>Quick Actions</h3>
    <div style="margin-top: 20px;">
        <a href="create_tournament.php" class="btn">Create New Tournament</a>
    </div>
</div>

</body>
</html>
