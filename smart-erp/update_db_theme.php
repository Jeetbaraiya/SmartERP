<?php
// update_db_theme.php
require_once 'config/db.php';

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'theme_preference'");

if ($check->num_rows == 0) {
    // Add the column
    $sql = "ALTER TABLE users ADD COLUMN theme_preference ENUM('light', 'dark', 'system') DEFAULT 'system' AFTER role";
    if ($conn->query($sql) === TRUE) {
        echo "<h1>Success: theme_preference column added to users table.</h1>";
    } else {
        echo "<h1>Error: " . $conn->error . "</h1>";
    }
} else {
    echo "<h1>Column already exists. No action needed.</h1>";
}
?>