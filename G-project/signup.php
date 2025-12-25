<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $bgmi_id = $conn->real_escape_string($_POST['bgmi_id']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "<div class='alert error'>Email already registered! <a href='login.php'>Login here</a></div>";
    } else {
        $sql = "INSERT INTO users (username, email, password, bgmi_id, phone) VALUES ('$username', '$email', '$password', '$bgmi_id', '$phone')";
        if ($conn->query($sql) === TRUE) {
            $user_id = $conn->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['bgmi_id'] = $bgmi_id;
            $_SESSION['email'] = $email;
            
            header("Location: index.php");
            exit();
        } else {
            $message = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
    }
}

include 'includes/header.php';
?>

<div class="container section">
    <div class="form-container" style="max-width: 500px; margin: 0 auto;">
        <h2 class="section-title text-center">Create Account</h2>
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username (IGN)</label>
                <input type="text" name="username" class="form-control" required placeholder="e.g. Mortal">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="********">
            </div>
            <div class="form-group">
                <label>BGMI ID (Character ID)</label>
                <input type="text" name="bgmi_id" class="form-control" required placeholder="512345678">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required placeholder="9876543210">
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Sign Up</button>
            
            <p class="text-center" style="margin-top: 20px; color: #888;">
                Already have an account? <a href="login.php" class="text-primary">Login</a>
            </p>
        </form>
    </div>
</div>

<style>
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #888; margin-bottom: 5px; font-size: 0.9rem; }
.alert { padding: 10px; text-align: center; margin-bottom: 15px; border-radius: 4px; }
.success { color: #00ff88; border: 1px solid #00ff88; background: rgba(0,255,136,0.1); }
.error { color: #ff4444; border: 1px solid #ff4444; background: rgba(255,68,68,0.1); }
</style>

<?php include 'includes/footer.php'; ?>
