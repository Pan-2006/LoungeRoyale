<?php
session_start();
include "../database.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Services - The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>
<?php include "navbar.php"; ?>
<main class="admin-marketing-page">
  <section class="admin-services-banner"></section>
  <section class="admin-services-menu">
    <article><h2>Hand Services</h2><p>Classic Manicure</p><p>Orly Breathable Manicure</p><p>Coucou Gel Manicure</p><a href="services_admin.php">Manage</a></article>
    <article><h2>Foot Services</h2><p>Classic Pedicure</p><p>Orly Breathable Pedicure</p><p>Coucou Gel Pedicure</p><a href="services_admin.php">Manage</a></article>
    <article><h2>Royale Deluxe Packages</h2><p>Deluxe Royale 1</p><p>Deluxe Royale 2</p><p>Deluxe Royale 3</p><a href="services_admin.php">Manage</a></article>
    <article><h2>Wax Service</h2><p>Eyebrow</p><p>Underarm</p><p>Full Leg</p><a href="services_admin.php">Manage</a></article>
  </section>
</main>
</body>
</html>
