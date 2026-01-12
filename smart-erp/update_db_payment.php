<?php
// update_db_payment.php
require_once 'config/db.php';

// Add payment_method column
$sql1 = "ALTER TABLE service_requests ADD COLUMN payment_method ENUM('cash', 'upi') DEFAULT 'cash'";
if ($conn->query($sql1) === TRUE) {
    echo "Column 'payment_method' added successfully.<br>";
} else {
    echo "Error adding 'payment_method': " . $conn->error . "<br>";
}

// Add payment_status column
$sql2 = "ALTER TABLE service_requests ADD COLUMN payment_status ENUM('pending', 'paid') DEFAULT 'pending'";
if ($conn->query($sql2) === TRUE) {
    echo "Column 'payment_status' added successfully.<br>";
} else {
    echo "Error adding 'payment_status': " . $conn->error . "<br>";
}

echo "Database update complete.";
?>