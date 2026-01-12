<?php
// update_db_phase2.php
require_once 'config/db.php';

echo "<h2>Starting Phase 2 Database Update...</h2>";

// 1. NOTICES TABLE
$sql1 = "CREATE TABLE IF NOT EXISTS `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql1) === TRUE) {
    echo "✅ Table 'notices' created/checked.<br>";
} else {
    echo "❌ Error creating 'notices': " . $conn->error . "<br>";
}

// 2. COMPLAINTS TABLE
$sql2 = "CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql2) === TRUE) {
    echo "✅ Table 'complaints' created/checked.<br>";
} else {
    echo "❌ Error creating 'complaints': " . $conn->error . "<br>";
}

// 3. VISITORS TABLE
$sql3 = "CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `visitor_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `visit_date` date NOT NULL,
  `pass_code` varchar(10) NOT NULL,
  `status` enum('generated','entered','expired') NOT NULL DEFAULT 'generated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `visitors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql3) === TRUE) {
    echo "✅ Table 'visitors' created/checked.<br>";
} else {
    echo "❌ Error creating 'visitors': " . $conn->error . "<br>";
}

// 4. FEEDBACK (Modify service_requests)
// Check if column exists first to avoid error
$check = $conn->query("SHOW COLUMNS FROM `service_requests` LIKE 'rating'");
if ($check->num_rows == 0) {
    $sql4 = "ALTER TABLE `service_requests` 
             ADD COLUMN `rating` int(1) DEFAULT NULL,
             ADD COLUMN `review` text DEFAULT NULL";
    if ($conn->query($sql4) === TRUE) {
        echo "✅ Table 'service_requests' updated for Feedback.<br>";
    } else {
        echo "❌ Error updating 'service_requests': " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ Table 'service_requests' already has feedback columns.<br>";
}

echo "<h3>Update Complete! You can delete this file now.</h3>";
?>