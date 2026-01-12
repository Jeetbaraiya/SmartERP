<?php
// update_db_v5.php
require_once 'config/db.php';

$sql = "ALTER TABLE service_requests 
        ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL,
        MODIFY COLUMN payment_method ENUM('cash', 'razorpay') DEFAULT 'cash'";

if ($conn->query($sql)) {
    echo "service_requests table updated successfully.";
} else {
    echo "Error updating table: " . $conn->error;
}
?>