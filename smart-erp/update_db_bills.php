<?php
// update_db_bills.php
require_once 'config/db.php';

echo "<h2>Starting Bills Module Database Update...</h2>";

// 1. BILLS TABLE
$sql = "CREATE TABLE IF NOT EXISTS `bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `due_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'bills' created/checked.<br>";
} else {
    echo "❌ Error creating 'bills': " . $conn->error . "<br>";
}

echo "<h3>Update Complete!</h3>";
?>