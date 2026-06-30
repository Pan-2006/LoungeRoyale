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
<title>Admin Home - The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css?v=admin-hand-match-customer">
</head>
<body>
<?php include "navbar.php"; ?>
<main class="admin-marketing-page">
  <section class="admin-hero">
    <div class="admin-hero-copy">
      <h1>Welcome To<br>The Lounge Royale</h1>
      <p>Experience luxury beauty services in a comfortable and elegant setting.</p>
      <a class="admin-gold-button" href="appointments.php">Manage Bookings</a>
    </div>
    <img class="admin-hero-ribbon" src="../assets/ribbon_design.png" alt="">
    <img class="admin-hero-hands" src="../assets/hands.png" alt="Elegant manicure hands">
  </section>
  <section class="admin-service-preview">
    <img class="admin-section-logo" src="../assets/logo for home_services.png" alt="The Lounge Royale">
    <div class="admin-service-grid">
      <a href="services_admin.php"><img src="../assets/hand services.png" alt="Hand services"><span>Nail Care</span></a>
      <a href="services_admin.php"><img src="../assets/foot services.png" alt="Foot services"><span>Foot Spa</span></a>
      <a href="services_admin.php"><img src="../assets/lash services.png" alt="Lash services"><span>Lash Services</span></a>
      <a href="services_admin.php"><img src="../assets/img design services.png" alt="Waxing services"><span>Waxing</span></a>
    </div>
  </section>
</main>
</body>
</html>
