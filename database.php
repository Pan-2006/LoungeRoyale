<?php
mysqli_report(MYSQLI_REPORT_OFF);

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($isLocal) {
    $host = "127.0.0.1";
    $user = "root";
    $password = "";
    $db = "lounge_royale";
    $port = 3306;
} else {
    $host = "YOUR_ONLINE_DB_HOST";
    $user = "YOUR_ONLINE_DB_USERNAME";
    $password = "YOUR_ONLINE_DB_PASSWORD";
    $db = "YOUR_ONLINE_DB_NAME";
    $port = 3306;
}

$conn = mysqli_connect($host, $user, $password, $db, $port);

if (!$conn) {
    die("Database connection failed.");
}

mysqli_set_charset($conn, "utf8mb4");
?>