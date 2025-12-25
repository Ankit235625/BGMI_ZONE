<?php
include 'includes/db.php';

// Reset admin password to 'admin123' with proper hashing
$new_password = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE admin SET password='$new_password' WHERE username='admin'");

echo "Admin password has been reset to: admin123\n";
echo "Hashed password: $new_password\n";
?>
