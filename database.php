<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$db = "lounge_royale";
$ports = [3306, 3307];

$conn = null;

foreach ($ports as $port) {
    $conn = @mysqli_connect($host, $user, $password, $db, $port);
    if ($conn) {
        break;
    }
}

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
