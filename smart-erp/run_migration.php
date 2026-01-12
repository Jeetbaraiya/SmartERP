<?php
// run_migration.php - Helper script to run the email_change_requests migration
require_once 'config/db.php';

echo "Running migration for email_change_requests table...\n\n";

$sql = "CREATE TABLE IF NOT EXISTS `email_change_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `old_email` varchar(100) NOT NULL,
  `new_email` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `email_change_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'email_change_requests' created successfully!\n";
    echo "✓ Migration completed.\n\n";
    echo "You can now use the email change approval system.\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>