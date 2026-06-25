<?php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id'])){
    echo "<script>alert('Please login first.'); window.location.href='../login.html';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: booking.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$service_id = (int) ($_POST['service_id'] ?? 0);
$staff_id = (int) ($_POST['staff_id'] ?? 0);
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');

if ($service_id <= 0 || $date === '' || $time === '') {
    echo "<script>alert('Please complete the appointment form.'); window.history.back();</script>";
    exit();
}

$selectedDate = DateTime::createFromFormat('Y-m-d', $date);
$today = new DateTime('today');

if (!$selectedDate || $selectedDate->format('Y-m-d') !== $date) {
    echo "<script>alert('Please choose a valid appointment date.'); window.history.back();</script>";
    exit();
}

if ($selectedDate < $today) {
    echo "<script>alert('Please choose today or a future date.'); window.history.back();</script>";
    exit();
}

if ((int) $selectedDate->format('N') === 1) {
    echo "<script>alert('The Lounge Royale is closed every Monday. Please choose another date.'); window.history.back();</script>";
    exit();
}

if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time) || $time < '11:00' || $time > '20:00') {
    echo "<script>alert('Please choose a time between 11:00 AM and 8:00 PM.'); window.history.back();</script>";
    exit();
}

$customerStmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($customerStmt, "i", $user_id);
mysqli_stmt_execute($customerStmt);
$customerResult = mysqli_stmt_get_result($customerStmt);

if(mysqli_num_rows($customerResult) === 0){
    echo "<script>alert('Customer profile not found. Please complete your profile.'); window.location.href='profile.php';</script>";
    exit();
}

$customer = mysqli_fetch_assoc($customerResult);
$customer_id = (int) $customer['customer_id'];

if($staff_id === 0){
    $availableStmt = mysqli_prepare($conn, "
        SELECT staff_id
        FROM staff
        WHERE staff_id NOT IN (
            SELECT staff_id
            FROM appointments
            WHERE appointment_date = ?
            AND appointment_time = ?
            AND status != 'Cancelled'
        )
        ORDER BY staff_id
        LIMIT 1
    ");
    mysqli_stmt_bind_param($availableStmt, "ss", $date, $time);
    mysqli_stmt_execute($availableStmt);
    $available = mysqli_stmt_get_result($availableStmt);

    if(mysqli_num_rows($available) === 0){
        echo "<script>alert('No technicians are available at the selected date and time. Please choose another schedule.'); window.history.back();</script>";
        exit();
    }

    $recommended = mysqli_fetch_assoc($available);
    $staff_id = (int) $recommended['staff_id'];
}

$checkStmt = mysqli_prepare($conn, "
    SELECT appointment_id
    FROM appointments
    WHERE staff_id = ?
    AND appointment_date = ?
    AND appointment_time = ?
    AND status != 'Cancelled'
    LIMIT 1
");
mysqli_stmt_bind_param($checkStmt, "iss", $staff_id, $date, $time);
mysqli_stmt_execute($checkStmt);
$check = mysqli_stmt_get_result($checkStmt);

if(mysqli_num_rows($check) > 0){
    echo "<script>alert('Selected technician is not available on the chosen date and time. Please choose another technician or schedule.'); window.history.back();</script>";
    exit();
}

$insertStmt = mysqli_prepare($conn, "
    INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status)
    VALUES (?, ?, ?, ?, ?, 'Pending')
");
mysqli_stmt_bind_param($insertStmt, "iiiss", $customer_id, $staff_id, $service_id, $date, $time);

if(mysqli_stmt_execute($insertStmt)){
    echo "<script>alert('Appointment booked successfully!'); window.location.href='my_appointments.php';</script>";
    exit();
}

echo "Database Booking Error: " . mysqli_error($conn);
?>
