<?php
require_once 'config/db.php';

// Add refund_status column
$sql = "ALTER TABLE service_requests ADD COLUMN refund_status ENUM('none', 'refunded') DEFAULT 'none'";

if ($conn->query($sql) === TRUE) {
    echo "Column 'refund_status' added successfully.<br>";
} else {
    echo "Error adding column: " . $conn->error . "<br>";
}

$conn->close();
?>