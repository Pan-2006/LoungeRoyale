<?php
// File: admin/confirm.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE appointments SET status='Confirmed' WHERE appointment_id='$id'");
}

header("Location: appointments.php");
exit();
