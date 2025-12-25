<?php
include '../includes/db.php';
include 'auth.php';
check_login();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);
    $prize_pool = $conn->real_escape_string($_POST['prize_pool']);
    $entry_fee = $conn->real_escape_string($_POST['entry_fee']);
    $max_slots = intval($_POST['max_slots']);
    $status = $conn->real_escape_string($_POST['status']);
    $type = $conn->real_escape_string($_POST['type']);
    
    // Handling File Uploads
    $image_url = "https://placehold.co/600x400/1a1a1a/00ff88?text=BGMI+Tournament";
    $qr_code = "";

    function uploadFile($fileKey, $targetDir) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
            if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
            $ext = pathinfo($_FILES[$fileKey]["name"], PATHINFO_EXTENSION);
            $filename = uniqid($fileKey . "_") . "." . $ext;
            if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $targetDir . $filename)) {
                return "assets/uploads/" . $filename;
            }
        }
        return null;
    }

    $uploaded_image = uploadFile('image_file', "../assets/uploads/");
    if ($uploaded_image) $image_url = $uploaded_image;

    $uploaded_qr = uploadFile('qr_file', "../assets/uploads/");
    if ($uploaded_qr) $qr_code = $uploaded_qr;

    if (empty($message)) {
        $sql = "INSERT INTO tournaments (title, description, date, prize_pool, entry_fee, max_slots, status, type, image_url, qr_code) 
                VALUES ('$title', '$description', '$date', '$prize_pool', '$entry_fee', $max_slots, '$status', '$type', '$image_url', '$qr_code')";

        if ($conn->query($sql) === TRUE) {
            header("Location: tournaments.php");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Tournament - Admin</title>
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
        .form-container {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #b3b3b3; }
        .form-control {
            width: 100%;
            padding: 10px;
            background: #0f0f0f;
            border: 1px solid #333;
            color: #fff;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 class="text-primary text-center" style="margin-bottom: 40px;">BGMI<span style="color:#fff">ADMIN</span></h2>
    <a href="dashboard.php" class="nav-item">Dashboard</a>
    <a href="tournaments.php" class="nav-item active">Manage Tournaments</a>
    <a href="registrations.php" class="nav-item">View Registrations</a>
    <a href="users.php" class="nav-item">Manage Users</a>
    <a href="../index.php" class="nav-item" target="_blank">View Site</a>
    <a href="logout.php" class="nav-item" style="color: #ff4444;">Logout</a>
</div>

<div class="main-content">
    <h2 class="section-title">Create Tournament</h2>
    
    <div class="form-container">
        <?php if($message) echo "<p style='color:red;'>$message</p>"; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tournament Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="date" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Prize Pool</label>
                <input type="text" name="prize_pool" class="form-control" placeholder="e.g. ₹10,000" required>
            </div>

            <div class="form-group">
                <label>Entry Fee</label>
                <input type="text" name="entry_fee" class="form-control" placeholder="e.g. ₹100 or Free" required>
            </div>

            <div class="form-group">
                <label>Max Slots (Registration Limit)</label>
                <input type="number" name="max_slots" class="form-control" value="100" min="1" required>
                <small style="color: #888;">Maximum number of teams/players allowed to register</small>
            </div>
            
            <div class="form-group">
                <label>Tournament Type</label>
                <select name="type" class="form-control">
                    <option value="squad">Squad</option>
                    <option value="duo">Duo</option>
                    <option value="solo">Solo</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="open">Open</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tournament Banner (Image)</label>
                <input type="file" name="image_file" class="form-control" accept="image/*">
                <small style="color:#666;">Recommended size: 1200x600.</small>
            </div>

            <div class="form-group">
                <label>Payment Scanner (QR Code)</label>
                <input type="file" name="qr_file" class="form-control" accept="image/*">
                <small style="color:#666;">Will be shown to users for paid tournaments.</small>
            </div>
            
            <button type="submit" class="btn">Create Tournament</button>
        </form>
    </div>
</div>

</body>
</html>
