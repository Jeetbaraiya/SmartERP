<?php
require_once 'config/db.php';

$services = [
    ['Gardening Service', 'Lawn mowing and garden maintenance.', 600.00],
    ['Home Painting', 'Wall painting and texture consultation.', 2500.00],
    ['Sofa Dry Cleaning', 'Deep cleaning for 5-seater sofa set.', 800.00],
    ['RO Purifier Service', 'Filter change and servicing for RO systems.', 450.00],
    ['CCTV Repair', 'Security camera maintenance and repair.', 1000.00]
];

foreach ($services as $svc) {
    $name = $svc[0];
    $desc = $svc[1];
    $price = $svc[2];

    // Check if exists
    $check = $conn->query("SELECT id FROM services WHERE name='$name'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO services (name, description, price, status) VALUES ('$name', '$desc', '$price', 'active')";
        if ($conn->query($sql) === TRUE) {
            echo "Added: $name<br>";
        } else {
            echo "Error adding $name: " . $conn->error . "<br>";
        }
    } else {
        echo "Skipped (Already Exists): $name<br>";
    }
}

$conn->close();
?>