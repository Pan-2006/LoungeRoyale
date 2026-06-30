<?php
// File: admin/delete_service.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM services WHERE service_id='$id'");
}

header("Location: services_admin.php");
exit();
