<?php
require_once 'config/db.php';
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'theme_preference'");
if ($result->num_rows > 0) {
    echo "Column exists";
} else {
    echo "Column missing";
}
?>