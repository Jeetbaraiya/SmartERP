<?php
require_once 'config/db.php';

$services = [
    ['House Cleaning', 'Full home deep cleaning service.', 1200.00],
    ['AC Repair', 'Air conditioner service and repair.', 500.00],
    ['Car Wash', 'Exterior and interior car washing.', 350.00],
    ['Pest Control', 'Termite and bug pest control treatment.', 1500.00],
    ['Carpenter', 'Furniture repair and assembly.', 400.00]
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