<?php
include "database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = "customer";

if ($name === '' || $email === '' || $password === '') {
    echo "<script>alert('Please complete all required fields.'); window.history.back();</script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address.'); window.history.back();</script>";
    exit();
}

$check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($check, "s", $email);
mysqli_stmt_execute($check);
$existing = mysqli_stmt_get_result($check);

if (mysqli_num_rows($existing) > 0) {
    echo "<script>alert('An account with this email already exists. Please sign in instead.'); window.location.href='login.html';</script>";
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$userStmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($userStmt, "ssss", $name, $email, $hashedPassword, $role);

if (!mysqli_stmt_execute($userStmt)) {
    echo "User error: " . mysqli_error($conn);
    exit();
}

$user_id = mysqli_insert_id($conn);
$phone = "";

$customerStmt = mysqli_prepare($conn, "INSERT INTO customers (user_id, name, phone) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($customerStmt, "iss", $user_id, $name, $phone);

if (mysqli_stmt_execute($customerStmt)) {
    echo "<script>alert('Registration successful!'); window.location.href='login.html';</script>";
    exit();
}

echo "Customer error: " . mysqli_error($conn);
?>
