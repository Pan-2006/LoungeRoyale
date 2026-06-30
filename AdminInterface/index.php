<?php
session_start();

if (isset($_SESSION["user_id"]) && ($_SESSION["role"] ?? "") === "admin") {
    header("Location: Dashboard.php");
    exit();
}

header("Location: ../login.html");
exit();
?>
