<?php
// 1. Core Connection Settings
$host = "127.0.0.1";       // Direct pathway to your local machine
$user = "root";            // Default XAMPP username
$password = "";            // Default XAMPP password is blank
$db = "lounge_royale";     // Your database name
$port = 3306;              // Your active XAMPP port

// 2. Establish connection to your active MySQL server
$conn = mysqli_connect($host, $user, $password, $db, $port);

// 3. Verify connection health
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>