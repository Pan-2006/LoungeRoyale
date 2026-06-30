<?php
// File: admin/complete.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];

    mysqli_query($conn, "UPDATE appointments SET status='Completed' WHERE appointment_id='$id'");

    // Add to sales if not already recorded
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT sale_id FROM sales WHERE appointment_id='$id'"));
    if(!$check){
        $priceRow = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT services.price FROM appointments
             JOIN services ON appointments.service_id = services.service_id
             WHERE appointments.appointment_id='$id'"
        ));
        if($priceRow){
            $total = $priceRow['price'];
            mysqli_query($conn, "INSERT INTO sales (appointment_id, total_amount, sale_date)
                                 VALUES ('$id', '$total', CURDATE())");
        }
    }
}

header("Location: appointments.php");
exit();
