<?php
// update_db_discount.php
require_once 'config/db.php';

$sql = "ALTER TABLE notices ADD COLUMN discount_amount DECIMAL(10, 2) DEFAULT 0.00";

if ($conn->query($sql) === TRUE) {
    echo "Column discount_amount added successfully.";
} else {
    // Ignore error if column exists
    echo "Error adding column (might already exist): " . $conn->error;
}
$conn->close();
?>