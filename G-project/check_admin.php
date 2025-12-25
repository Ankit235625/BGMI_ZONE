<?php
include 'includes/db.php';

echo "=== Admin Table Check ===\n";
$res = $conn->query("SELECT id, username FROM admin");
if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']}, Username: {$row['username']}\n";
    }
} else {
    echo "No admin users found!\n";
}
?>
