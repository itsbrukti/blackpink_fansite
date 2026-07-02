<?php
// config/db.php - Database Configuration

$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty
$db_name = 'blackpink_fansite';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Note: DO NOT close the connection here - it will be closed after all operations
?>