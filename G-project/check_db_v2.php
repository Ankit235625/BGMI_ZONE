<?php
include 'includes/db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "--- DB Check ---\n";

// List Tables
echo "Tables:\n";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    echo " - " . $row[0] . "\n";
}

// Check Foreign Keys specifically for tournaments
echo "\nReferences to tournaments table:\n";
$res2 = $conn->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME 
                      FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                      WHERE REFERENCED_TABLE_NAME = 'tournaments' 
                      AND REFERENCED_TABLE_SCHEMA = 'bgmi_tournament'");
while($row2 = $res2->fetch_assoc()) {
    print_r($row2);
}

// Check an example delete
echo "\nTesting a Dry Run Delete on ID 9999 (Non-existent):\n";
try {
    $conn->begin_transaction();
    $conn->query("DELETE FROM registrations WHERE tournament_id=9999");
    $conn->query("DELETE FROM notifications WHERE tournament_id=9999");
    $conn->query("DELETE FROM tournaments WHERE id=9999");
    echo "Dry run queries executed without error.\n";
    $conn->commit();
} catch (Exception $e) {
    echo "Error during Dry Run: " . $e->getMessage() . "\n";
    $conn->rollback();
}
?>
