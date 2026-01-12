<?php
// fix_documents_schema.php
require_once 'config/db.php';

echo "<h2>Fixing User Documents Schema</h2>";

// 1. Check if table 'user_documents' exists
$checkTable = $conn->query("SHOW TABLES LIKE 'user_documents'");

if ($checkTable->num_rows == 0) {
    echo "Table 'user_documents' missing. Creating it...<br>";
    $sql = "CREATE TABLE `user_documents` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `document_type` varchar(50) NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `notes` text DEFAULT NULL,
      `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
      `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `user_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Table 'user_documents' created successfully.<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
} else {
    echo "Table 'user_documents' exists. Checking columns...<br>";

    // 2. Check if 'document_type' column exists
    $checkCol = $conn->query("SHOW COLUMNS FROM `user_documents` LIKE 'document_type'");
    if ($checkCol->num_rows == 0) {
        echo "Column 'document_type' missing. Adding it...<br>";
        $sql = "ALTER TABLE `user_documents` ADD COLUMN `document_type` varchar(50) NOT NULL AFTER `user_id`";
        if ($conn->query($sql) === TRUE) {
            echo "✅ Column 'document_type' added successfully.<br>";
        } else {
            echo "❌ Error adding column: " . $conn->error . "<br>";
        }
    } else {
        echo "✅ Column 'document_type' already exists.<br>";
    }
}
echo "<h3>Done. Try uploading again.</h3>";
?>