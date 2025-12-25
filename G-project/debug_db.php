<?php
include 'includes/db.php';

echo "### TABLES ###\n";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    $table = $row[0];
    echo "Table: $table\n";
    $res2 = $conn->query("DESCRIBE $table");
    while($row2 = $res2->fetch_assoc()) {
        echo "  - {$row2['Field']} ({$row2['Type']})\n";
    }
}

echo "\n### CONSTRAINTS ###\n";
$res3 = $conn->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                      FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                      WHERE REFERENCED_TABLE_SCHEMA = 'bgmi_tournament'");
while($row3 = $res3->fetch_assoc()) {
    print_r($row3);
}
?>
