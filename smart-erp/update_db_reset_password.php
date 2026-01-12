<?php
// update_db_reset_password.php
require_once 'config/db.php';

// Add reset_token and reset_expiry columns to users table
$sql = "ALTER TABLE users 
        ADD COLUMN reset_token VARCHAR(255) NULL AFTER role,
        ADD COLUMN reset_expiry DATETIME NULL AFTER reset_token";

if ($conn->query($sql) === TRUE) {
    echo "Table users updated successfully";
} else {
    echo "Error updating table: " . $conn->error;
}

$conn->close();
?>