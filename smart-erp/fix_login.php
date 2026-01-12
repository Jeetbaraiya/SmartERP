<?php
require_once 'config/db.php';

$box_pass = 'admin123';
$hash = password_hash($box_pass, PASSWORD_DEFAULT);
$email = 'admin@example.com';

echo "Resetting password for $email to '$box_pass'...\n";

// Update password
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    echo "Success! Password updated.\n";
    echo "New Hash: " . $hash . "\n";
} else {
    echo "Error updating password: " . $conn->error . "\n";
}

// Verify it exists
$res = $conn->query("SELECT * FROM users WHERE email = '$email'");
if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo "User found. ID: " . $row['id'] . ", Role: " . $row['role'] . "\n";
    if (password_verify($box_pass, $row['password'])) {
        echo "VERIFICATION PASSED: password_verify returns TRUE.\n";
    } else {
        echo "VERIFICATION FAILED: password_verify returns FALSE.\n";
    }
} else {
    echo "Error: Admin user not found in DB!\n";
}
?>