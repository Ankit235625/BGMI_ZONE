<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_GET['error']) && $_GET['error'] == 'disabled') {
    $message = "<div class='alert error'>Your session was terminated because your account has been disabled.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] == 'disabled') {
            $message = "<div class='alert error'>Your account has been disabled by admin.</div>";
        } elseif (password_verify($password, $user['password'])) {
            // Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['bgmi_id'] = $user['bgmi_id'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to original page or index
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header("Location: $redirect");
            exit();
        } else {
            $message = "<div class='alert error'>Invalid Password</div>";
        }
    } else {
        $message = "<div class='alert error'>No account found with this email</div>";
    }
}

include 'includes/header.php';
?>

<div class="container section">
    <div class="form-container" style="max-width: 400px; margin: 50px auto;">
        <h2 class="section-title text-center">Login</h2>
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="********">
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Login</button>
            
            <p class="text-center" style="margin-top: 20px; color: #888;">
                New here? <a href="signup.php" class="text-primary">Create Account</a>
            </p>
        </form>
    </div>
</div>

<style>
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #888; margin-bottom: 5px; font-size: 0.9rem; }
.alert { padding: 10px; text-align: center; margin-bottom: 15px; border-radius: 4px; }
.error { color: #ff4444; border: 1px solid #ff4444; background: rgba(255,68,68,0.1); }
</style>

<?php include 'includes/footer.php'; ?>
