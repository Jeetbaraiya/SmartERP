<?php
// update_db_email_verification.php
// Create email_change_tokens table for email verification

require_once 'config/db.php';

// Create email_change_tokens table
$sql = "CREATE TABLE IF NOT EXISTS email_change_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    new_email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'email_change_tokens' created successfully.\n";
} else {
    echo "❌ Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>