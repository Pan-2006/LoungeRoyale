<?php

$host = "localhost";
$user = "root";
$password = "";
$db = "lounge_royale";

$conn = mysqli_connect($host, $user, $password, $db, 3307);

if ($conn) {

} else {
    echo "Failed: " . mysqli_connect_error();
}

?>