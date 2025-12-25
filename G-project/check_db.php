<?php
include 'includes/db.php';
$res = $conn->query("DESCRIBE users");
echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
}
echo "</table>";
?>
