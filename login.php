<?php
session_start();
require_once "database.php";

function came_from_admin(): bool {
    $referer = $_SERVER["HTTP_REFERER"] ?? "";
    return stripos($referer, "/admin/") !== false;
}

function redirect_login(string $message): void {
    $target = came_from_admin() ? "admin/login.php" : "login.html";
    header("Location: " . $target . "?error=" . urlencode($message));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    redirect_login("Please enter your email and password.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT user_id, name, email, password, role FROM users WHERE email = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user["password"])) {
    redirect_login("Incorrect email or password.");
}

session_regenerate_id(true);

$_SESSION["user_id"] = (int)$user["user_id"];
$_SESSION["role"] = $user["role"];
$_SESSION["name"] = $user["name"];

if ($user["role"] === "admin") {
    header("Location: AdminInterface/index.php");
    exit();
}

header("Location: index.html");
exit();
?>
