<?php
// update_db_discount_percent.php
require_once 'config/db.php';

// Rename column and change type slightly if needed (DECIMAL(5,2) is enough for 100.00)
$sql = "ALTER TABLE notices CHANGE COLUMN discount_amount discount_percent DECIMAL(5, 2) DEFAULT 0.00";

if ($conn->query($sql) === TRUE) {
    echo "Column changed to discount_percent successfully.";
} else {
    echo "Error changing column: " . $conn->error;
}
$conn->close();
?>