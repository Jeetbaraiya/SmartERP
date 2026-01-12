<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/db.php';

echo "<h2>Service Requests Dump</h2>";
$sql = "SELECT id, status, payment_status, refund_status FROM service_requests";
$res = $conn->query($sql);

if ($res) {
    echo "<table border='1'><tr><th>ID</th><th>Status</th><th>Payment Status</th><th>Refund Status</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>'{$row['payment_status']}'</td>"; // Quoted to see whitespace/case
        echo "<td>'{$row['refund_status']}'</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Query Failed: " . $conn->error;
}
?>