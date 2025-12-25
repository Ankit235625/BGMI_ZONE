<?php
include 'includes/db.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$check = "SELECT * FROM admin WHERE username='$username'";
$result = $conn->query($check);

if ($result->num_rows > 0) {
    // Update existing
    $sql = "UPDATE admin SET password='$hashed_password' WHERE username='$username'";
    if ($conn->query($sql) === TRUE) {
        echo "Admin password reset successfully to: $password<br>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    // Insert new
    $sql = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created successfully with password: $password<br>";
    } else {
        echo "Error creating record: " . $conn->error;
    }
}

// Verify
$finalCheck = $conn->query("SELECT * FROM admin WHERE username='$username'");
$row = $finalCheck->fetch_assoc();
echo "Current Admin Hash in DB: " . $row['password'] . "<br>";
echo "Test Verify: " . (password_verify($password, $row['password']) ? "MATCH" : "MISMATCH");
?>
