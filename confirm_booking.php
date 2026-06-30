<?php
session_start();
include "database.php";

function alertAndGo($message, $target) {
    echo "<script>alert(" . json_encode($message) . "); window.location.href=" . json_encode($target) . ";</script>";
    exit();
}

function alertAndBack($message) {
    echo "<script>alert(" . json_encode($message) . "); window.history.back();</script>";
    exit();
}

if (!isset($_SESSION['user_id'])) {
    alertAndGo('Your session has expired. Please log in again.', 'login.html');
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: appointment.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$service_id = (int) ($_POST['service_id'] ?? 0);
$staff_id = (int) ($_POST['staff_id'] ?? 0);
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');

if ($service_id <= 0 || $staff_id <= 0 || $date === '' || $time === '') {
    alertAndBack('Please complete the booking form.');
}

$customerStmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($customerStmt, "i", $user_id);
mysqli_stmt_execute($customerStmt);
$customerResult = mysqli_stmt_get_result($customerStmt);
$customer = mysqli_fetch_assoc($customerResult);

if (!$customer) {
    $userStmt = mysqli_prepare($conn, "SELECT name FROM users WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($userStmt, "i", $user_id);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    $user = mysqli_fetch_assoc($userResult);

    if (!$user) {
        alertAndGo('User account was not found. Please log in again.', 'login.html');
    }

    $customerName = trim($user['name'] ?? '');
    if ($customerName === '') {
        $customerName = 'Customer';
    }

    $phone = '';
    $createCustomerStmt = mysqli_prepare($conn, "INSERT INTO customers (user_id, name, phone) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($createCustomerStmt, "iss", $user_id, $customerName, $phone);

    if (!mysqli_stmt_execute($createCustomerStmt)) {
        alertAndBack('Could not create your customer profile. Please try again.');
    }

    $customer = ['customer_id' => mysqli_insert_id($conn)];
}

$customer_id = (int) $customer['customer_id'];

$duplicateStmt = mysqli_prepare($conn, "
    SELECT appointment_id
    FROM appointments
    WHERE staff_id = ?
      AND appointment_date = ?
      AND appointment_time = ?
      AND status IN ('Pending', 'Confirmed')
    LIMIT 1
");
mysqli_stmt_bind_param($duplicateStmt, "iss", $staff_id, $date, $time);
mysqli_stmt_execute($duplicateStmt);
$duplicateResult = mysqli_stmt_get_result($duplicateStmt);

if (mysqli_fetch_assoc($duplicateResult)) {
    alertAndBack('That date and time is already booked. Please choose another schedule.');
}

$insertStmt = mysqli_prepare($conn, "
    INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status)
    VALUES (?, ?, ?, ?, ?, 'Pending')
");
mysqli_stmt_bind_param($insertStmt, "iiiss", $customer_id, $staff_id, $service_id, $date, $time);

if (mysqli_stmt_execute($insertStmt)) {
    alertAndGo('Appointment booked successfully! Please wait for admin confirmation.', 'profile.php');
}

echo "Database Booking Error: " . mysqli_error($conn);
?>
