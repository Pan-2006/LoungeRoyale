<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.html");
    exit();
}

if (isset($_SESSION['role']) && $_SESSION['role'] !== "customer") {
    echo "Access denied.";
    exit();
}
?>

<h1>Customer Dashboard</h1>

<a href="booking.html">Book Appointment</a>
<br><br>

<a href="my_appointments.php">My Appointments</a>
<br><br>

<a href="profile.php">My Profile</a>
<br><br>

<a href="../logout.php">Logout</a>
