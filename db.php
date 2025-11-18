<?php
$host = "localhost";   // Database host
$user = "root";        // Database username (default in XAMPP is root)
$pass = "";            // Database password (default in XAMPP is empty)
$db   = "document_db"; // Database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
