<?php
include 'includes/db.php';
// Get first user
$res = $conn->query("SELECT id, status FROM users LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $uid = $row['id'];
    $old_status = $row['status'];
    $new_status = ($old_status == 'active') ? 'disabled' : 'active';
    
    echo "Current Status for user $uid: $old_status\n";
    echo "Attempting to change to: $new_status\n";
    
    if ($conn->query("UPDATE users SET status = '$new_status' WHERE id = $uid")) {
        echo "Update successful!\n";
        $res2 = $conn->query("SELECT status FROM users WHERE id = $uid");
        $new_row = $res2->fetch_assoc();
        echo "New Status: " . $new_row['status'] . "\n";
    } else {
        echo "Update failed: " . $conn->error . "\n";
    }
} else {
    echo "No users found.\n";
}
?>
