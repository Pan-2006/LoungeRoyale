<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "<script>alert('Please enter your email and password.'); window.history.back();</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT user_id, password, role FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    if ($password === $user['password'] || password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === "customer") {
            header("Location: customer/dashboard_customer.php");
            exit();
        }

        header("Location: index.html");
        exit();
    }
}

echo "<script>alert('Invalid email or password.'); window.history.back();</script>";
exit();
?>
