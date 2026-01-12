<?php
// user/get_booked_slots.php
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['service_id']) || !isset($_GET['date'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$service_id = intval($_GET['service_id']);
$date = $conn->real_escape_string($_GET['date']);

// Fetch all booked slots for this service on this date
$sql = "SELECT booking_slot FROM service_requests WHERE service_id = $service_id AND booking_date = '$date' AND status != 'rejected'";
$result = $conn->query($sql);

$booked_slots = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['booking_slot']; // e.g. "09:00 - 10:00"
    }
}

echo json_encode($booked_slots);
?>
