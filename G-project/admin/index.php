<?php
session_start();
include '../includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - BGMI Zone</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box {
            background: #1a1a1a;
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #333;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        .form-control {
            width: 93%; /* adjusted for padding */
            padding: 12px;
            margin-bottom: 20px;
            background: #0f0f0f;
            border: 1px solid #333;
            color: #fff;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="login-box text-center">
    <h2 class="text-primary" style="margin-bottom: 20px;">Admin Login</h2>
    <?php if($error) echo "<p style='color:red; margin-bottom:15px;'>$error</p>"; ?>
    
    <form method="POST">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" class="btn" style="width:100%">Login</button>
    </form>
     
</div>

</body>
</html>
