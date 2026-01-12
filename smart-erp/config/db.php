<?php
// config/db.php
// Database connection configuration

// Production Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../php_error.log');

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$dbname = 'smart_residence_erp';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Timezone
date_default_timezone_set('Asia/Kolkata');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Helper to get Role Name by Level
function get_role_name($level)
{
    if ($level == 1)
        return "Super Admin";
    // Deprecated Levels 2, 3, 4
    return "Resident";
}
?>