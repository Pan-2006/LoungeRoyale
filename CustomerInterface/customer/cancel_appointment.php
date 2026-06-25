<?php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$appointment_id = (int) ($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    header("Location: my_appointments.php");
    exit();
}

$stmt = mysqli_prepare($conn, "
    UPDATE appointments
    JOIN customers ON appointments.customer_id = customers.customer_id
    SET appointments.status = 'Cancelled'
    WHERE appointments.appointment_id = ?
    AND customers.user_id = ?
    AND appointments.status = 'Pending'
");
mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);
mysqli_stmt_execute($stmt);

header("Location: my_appointments.php");
exit();
?>
