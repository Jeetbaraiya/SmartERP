<?php
require_once 'config/db.php';

// Add booking_date and booking_slot to service_requests
$sql = "ALTER TABLE service_requests
        ADD COLUMN booking_date DATE AFTER service_id,
        ADD COLUMN booking_slot VARCHAR(50) AFTER booking_date";

if ($conn->query($sql) === TRUE) {
    echo "Table 'service_requests' updated successfully with booking fields.<br>";
} else {
    echo "Error updating table: " . $conn->error . "<br>";
}

$conn->close();
?>