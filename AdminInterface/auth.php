<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../database.php";


function require_admin(): void {
    if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
        $_SESSION["login_error"] = "Please sign in with an administrator account.";
        header("Location: login.php");
        exit();
    }
}


function current_admin(mysqli $conn): array {
    $id = (int)($_SESSION["user_id"] ?? 0);
    $stmt = mysqli_prepare($conn, "SELECT user_id, name, email, role FROM users WHERE user_id = ? AND role = 'admin' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    if (!$admin) {
        $_SESSION = [];
        session_destroy();
        header("Location: login.php");
        exit();
    }
    return $admin;
}


function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}


function peso($value): string {
    return "₱" . number_format((float)($value ?? 0), 2);
}


function one_value(mysqli $conn, string $sql, string $types = "", array $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_row(mysqli_stmt_get_result($stmt));
    return $row ? $row[0] : 0;
}


function asset_path(string $filename): string {
    return "../assests/" . rawurlencode($filename);
}


function service_image(?string $category): string {
    $category = strtolower(trim((string)$category));
    if (str_contains($category, "hand")) return asset_path("hand services.png");
    if (str_contains($category, "foot")) return asset_path("foot services.png");
    if (str_contains($category, "lash")) return asset_path("lash services.png");
    if (str_contains($category, "kiddie") || str_contains($category, "other")) return asset_path("kiddie services & others.png");
    if (str_contains($category, "royale") || str_contains($category, "deluxe")) return asset_path("royale deluxe package.png");
    return asset_path("img design services.png");
}
?>
'@
