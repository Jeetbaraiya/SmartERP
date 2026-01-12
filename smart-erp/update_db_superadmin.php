<?php
// update_db_superadmin.php
require_once 'config/db.php';

echo "<h2>Starting Super Admin & Level Access Database Update...</h2>";

// 1. Update USERS table (level, status)
$check_level = $conn->query("SHOW COLUMNS FROM `users` LIKE 'level'");
if ($check_level->num_rows == 0) {
    // Default level 5 (User)
    $sql1 = "ALTER TABLE `users` 
             ADD COLUMN `level` INT(1) NOT NULL DEFAULT 5,
             ADD COLUMN `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'";
    if ($conn->query($sql1) === TRUE) {
        echo "✅ Added 'level' and 'status' columns to 'users' table.<br>";
    } else {
        echo "❌ Error updating 'users' table: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ 'users' table already has new columns.<br>";
}

// 2. Update SERVICE_REQUESTS table (approval_stage)
$check_stage = $conn->query("SHOW COLUMNS FROM `service_requests` LIKE 'approval_stage'");
if ($check_stage->num_rows == 0) {
    // Default stage 4 (Pending Service Manager approval)
    // Flow: L4 -> L3 -> L2 -> L1
    $sql2 = "ALTER TABLE `service_requests` 
             ADD COLUMN `approval_stage` INT(1) NOT NULL DEFAULT 4";
    if ($conn->query($sql2) === TRUE) {
        echo "✅ Added 'approval_stage' column to 'service_requests' table.<br>";
    } else {
        echo "❌ Error updating 'service_requests' table: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ 'service_requests' table already has 'approval_stage'.<br>";
}

// 3. Create Default Super Admin (Level 1)
$sa_email = 'superadmin@smartresidence.com';
$sa_pass = password_hash('password123', PASSWORD_DEFAULT);
$sa_name = 'Super Admin';

// Check if exists
$check_sa = $conn->query("SELECT id FROM users WHERE email='$sa_email'");
if ($check_sa->num_rows == 0) {
    $sql3 = "INSERT INTO users (name, email, password, role, level, status) 
             VALUES ('$sa_name', '$sa_email', '$sa_pass', 'admin', 1, 'active')";
    if ($conn->query($sql3) === TRUE) {
        echo "✅ Created Default Super Admin (Level 1): $sa_email / password123<br>";
    } else {
        echo "❌ Error creating Super Admin: " . $conn->error . "<br>";
    }
} else {
    // Ensure existing superadmin is Level 1
    $conn->query("UPDATE users SET level=1, role='admin' WHERE email='$sa_email'");
    echo "ℹ️ Super Admin account verified/updated.<br>";
}

echo "<h3>Database Update Complete!</h3>";
?>