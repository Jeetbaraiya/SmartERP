<?php
// seed_admins.php
require_once 'config/db.php';

$admins = [
    [
        'name' => 'Central Admin',
        'email' => 'admin2@smartresidence.com',
        'password' => 'password123',
        'level' => 2,
        'role' => 'admin',
        'phone' => '9876543212'
    ],
    [
        'name' => 'Branch Admin',
        'email' => 'admin3@smartresidence.com',
        'password' => 'password123',
        'level' => 3,
        'role' => 'admin',
        'phone' => '9876543213'
    ],
    [
        'name' => 'Service Manager',
        'email' => 'admin4@smartresidence.com',
        'password' => 'password123',
        'level' => 4,
        'role' => 'admin',
        'phone' => '9876543214'
    ]
];

echo "Seeding Admins...\n";

foreach ($admins as $admin) {
    $email = $admin['email'];
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");

    if ($check->num_rows == 0) {
        $name = $admin['name'];
        $password = password_hash($admin['password'], PASSWORD_DEFAULT);
        $level = $admin['level'];
        $role = $admin['role'];
        $phone = $admin['phone'];

        $sql = "INSERT INTO users (name, email, password, phone, role, level, status, created_at) 
                VALUES ('$name', '$email', '$password', '$phone', '$role', $level, 'active', NOW())";

        if ($conn->query($sql)) {
            echo "[SUCCESS] Created Level $level Admin: $email\n";
        } else {
            echo "[ERROR] Failed to create $email: " . $conn->error . "\n";
        }
    } else {
        echo "[SKIP] Admin $email already exists.\n";
    }
}

echo "Done.\n";
?>