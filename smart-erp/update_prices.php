<?php
// update_prices.php
require_once 'config/db.php';

$sql = "UPDATE services SET price = price + 50";

if ($conn->query($sql) === TRUE) {
    echo "Services updated successfully. " . $conn->affected_rows . " rows affected.";
} else {
    echo "Error updating prices: " . $conn->error;
}
$conn->close();
?>