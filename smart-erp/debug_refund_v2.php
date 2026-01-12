<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...<br>";

if (!file_exists('config/db.php')) {
    die("config/db.php not found!");
}

require_once 'config/db.php';

if (!isset($conn)) {
    die("\$conn variable is not set!");
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully.<br>";

$sql = "SELECT id, status, payment_status, refund_status FROM service_requests";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Status</th><th>Payment Status</th><th>Refund Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['payment_status'] . "</td>";
        echo "<td>" . $row['refund_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No results found.";
}
?>