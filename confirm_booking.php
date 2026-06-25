<?php
session_start();
include "database.php";

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Your session has expired. Please log in again.'); window.location.href='login.html';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: customer/booking.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$service_id = (int) ($_POST['service_id'] ?? 0);
$staff_id = (int) ($_POST['staff_id'] ?? 0);
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');

if ($service_id <= 0 || $staff_id <= 0 || $date === '' || $time === '') {
    echo "<script>alert('Please complete the booking form.'); window.history.back();</script>";
    exit();
}

$customerStmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($customerStmt, "i", $user_id);
mysqli_stmt_execute($customerStmt);
$customerResult = mysqli_stmt_get_result($customerStmt);
$customer = mysqli_fetch_assoc($customerResult);

if (!$customer) {
    echo "<script>alert('Please complete your profile details before scheduling.'); window.location.href='customer/profile.php';</script>";
    exit();
}

$customer_id = (int) $customer['customer_id'];
$insertStmt = mysqli_prepare($conn, "
    INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status)
    VALUES (?, ?, ?, ?, ?, 'Pending')
");
mysqli_stmt_bind_param($insertStmt, "iiiss", $customer_id, $staff_id, $service_id, $date, $time);

if (mysqli_stmt_execute($insertStmt)) {
    echo "<script>alert('Appointment booked successfully!'); window.location.href='customer/my_appointments.php';</script>";
    exit();
}

echo "Database Booking Error: " . mysqli_error($conn);
?>
