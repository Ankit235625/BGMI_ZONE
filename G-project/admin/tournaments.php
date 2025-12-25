<?php
ob_start();
include '../includes/db.php';
include 'auth.php';
check_login();

$error = "";
$success = "";

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['delete_id']);
    
    // 1. Delete associated registrations & notifications first
    $q1 = $conn->query("DELETE FROM registrations WHERE tournament_id=$id");
    $q2 = $conn->query("DELETE FROM notifications WHERE tournament_id=$id");
    
    // 2. Delete the tournament
    if ($conn->query("DELETE FROM tournaments WHERE id=$id")) {
         ob_clean();
         header("Location: tournaments.php?msg=deleted");
         exit();
    } else {
         $error = "Database Error: " . $conn->error;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $success = "Tournament deleted successfully!";
}

// Handle Room Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_room'])) {
    $t_id = intval($_POST['tournament_id']);
    $r_id = $conn->real_escape_string($_POST['room_id']);
    $r_pass = $conn->real_escape_string($_POST['room_password']);
    
    $sql = "UPDATE tournaments SET room_id='$r_id', room_password='$r_pass' WHERE id=$t_id";
    if ($conn->query($sql)) {
        $success = "Room Details Updated!";
        
        // 1. Fetch Tournament Info for notification
        $t_info_res = $conn->query("SELECT title, date FROM tournaments WHERE id = $t_id");
        $t_info = $t_info_res->fetch_assoc();
        $t_name = $t_info['title'];
        $t_date = date('M d, H:i', strtotime($t_info['date']));

        // 2. Fetch all registered & paid users
        $users_query = "SELECT user_id, (SELECT email FROM users WHERE id = r.user_id) as email 
                       FROM registrations r WHERE tournament_id = $t_id AND payment_status = 'completed'";
        $users_res = $conn->query($users_query);
        
        $user_ids = [];
        $email_list = [];
        while($u = $users_res->fetch_assoc()) { 
            $user_ids[] = $u['user_id'];
            if($u['email']) $email_list[] = $u['email'];
        }

        if (!empty($user_ids)) {
            // 3. Insert On-Site Notifications (Stored as clean text for better DB readability)
            $notification_msg = $conn->real_escape_string("Room Details for $t_name ($t_date): ID: $r_id, Pass: $r_pass");
            foreach($user_ids as $uid) {
                $conn->query("INSERT INTO notifications (user_id, tournament_id, message) VALUES ($uid, $t_id, '$notification_msg')");
            }
            $success .= " & Notifications sent to " . count($user_ids) . " players.";
        }

        // 4. Send Email (Optional if checked)
        if (isset($_POST['send_email']) && !empty($email_list)) {
            $to = implode(", ", $email_list);
            $subject = "Room Details for $t_name ($t_date)";
            $msg = "Hello Warrior!\n\nTournament: $t_name\nDate: $t_date\nRoom ID: $r_id\nRoom Password: $r_pass\n\nBe ready! GLHF!";
            $headers = "From: admin@bgmizone.com";
            
            if (@mail($to, $subject, $msg, $headers)) {
                $success .= " + Emails Sent.";
            } else {
                $error = "Room updated and notifications saved, but email server failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tournaments - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #0f0f0f; color: #fff; }
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
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        th { background: #222; }
        .badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .badge-open { background: rgba(0, 255, 136, 0.2); color: #00ff88; }
        .badge-closed { background: rgba(255, 0, 0, 0.2); color: #ff4444; }
        .badge-ongoing { background: rgba(255, 204, 0, 0.2); color: #ffcc00; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert-error { background: rgba(255, 0, 0, 0.1); border: 1px solid red; color: red; }
        .alert-success { background: rgba(0, 255, 136, 0.1); border: 1px solid #00ff88; color: #00ff88; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 class="text-primary text-center" style="margin-bottom: 40px;">BGMI<span style="color:#fff">ADMIN</span></h2>
    <a href="dashboard.php" class="nav-item">Dashboard</a>
    <a href="tournaments.php" class="nav-item active">Manage Tournaments</a>
    <a href="registrations.php" class="nav-item">View Registrations</a>
    <a href="../index.php" class="nav-item" target="_blank">View Site</a>
    <a href="logout.php" class="nav-item" style="color: #ff4444;">Logout</a>
</div>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 class="section-title" style="margin-bottom: 0;">Manage Tournaments</h2>
        <a href="create_tournament.php" class="btn">Create New</a>
    </div>

    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Date</th>
                <th>Prize Pool</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM tournaments ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $statusClass = 'badge-' . $row['status'];
                    $deleteUrl = "?delete=" . $row['id'];
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['title']) . "</td>
                        <td><span class='badge' style='background: #333;'>" . ucfirst($row['type']) . "</span></td>
                        <td>" . date('M d, Y', strtotime($row['date'])) . "</td>
                        <td>{$row['prize_pool']}</td>
                        <td><span class='badge $statusClass'>{$row['status']}</span></td>
                        <td>
                            <div style='background: #222; padding: 10px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #444;'>
                                <form method='POST'>
                                    <input type='hidden' name='tournament_id' value='{$row['id']}'>
                                    <input type='text' name='room_id' value='{$row['room_id']}' placeholder='Room ID' class='form-control' style='margin-bottom: 5px; font-size: 0.8rem; padding: 5px;'>
                                    <input type='text' name='room_password' value='{$row['room_password']}' placeholder='Password' class='form-control' style='margin-bottom: 5px; font-size: 0.8rem; padding: 5px;'>
                                    
                                    <label style='font-size: 0.75rem; color: #888; display: block; margin: 5px 0;'>
                                        <input type='checkbox' name='send_email'> Send Email to Players
                                    </label>
                                    
                                    <button type='submit' name='update_room' class='btn' style='font-size: 0.7rem; padding: 5px 10px; width: 100%; border-radius: 2px;'>Save & Notify</button>
                                </form>
                            </div>

                            <form method='POST' action='tournaments.php' onsubmit='return confirm(\"Are you sure?\")'>
                                <input type='hidden' name='action' value='delete'>
                                <input type='hidden' name='delete_id' value='{$row['id']}'>
                                <button type='submit' style='background:none; border:none; color: #ff4444; cursor:pointer; font-size: 0.8rem; text-decoration: underline;'>Delete Tournament</button>
                            </form>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No tournaments found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
