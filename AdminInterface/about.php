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
<title>Admin About - The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>
<?php include "navbar.php"; ?>
<main class="admin-marketing-page admin-about-page">
  <section class="admin-about-layout">
    <div>
      <p class="admin-eyebrow">About Us</p>
      <h1>The Lounge Royale</h1>
      <p>Welcome to The Lounge Royale, a luxury beauty lounge for nail care, foot spa, lash services, waxing, deluxe packages, and customer pampering.</p>
      <p>This admin view keeps the same brand presentation while giving staff fast access to bookings, clients, services, sales, and reports.</p>
      <a class="admin-gold-button" href="Dashboard.php">Admin Dashboard</a>
    </div>
    <img src="../assets/img design services.png" alt="The Lounge Royale interior">
  </section>

  <section class="admin-about-story">
    <div>
      <p class="admin-eyebrow">Our Standard</p>
      <h2>Luxury service, organized care</h2>
      <p>The Lounge Royale was built around comfort, elegance, and polished beauty service. Every appointment should feel calm for the client and manageable for the team, from booking to completion.</p>
    </div>
    <div class="admin-about-card-grid">
      <article>
        <h3>Client Experience</h3>
        <p>Customers can browse services, book appointments, and review their scheduled visits through the customer interface.</p>
      </article>
      <article>
        <h3>Staff Workflow</h3>
        <p>Admins can monitor bookings, update appointment status, manage client records, and keep daily operations clear.</p>
      </article>
      <article>
        <h3>Service Quality</h3>
        <p>The system supports nail care, foot spa, lash services, waxing, deluxe packages, and customer pampering.</p>
      </article>
    </div>
  </section>

  <section class="admin-about-operations">
    <div class="admin-about-panel">
      <h2>What this admin side handles</h2>
      <ul>
        <li>View and confirm customer appointment requests.</li>
        <li>Manage client records and customer history.</li>
        <li>Track services, staff assignments, sales, and reports.</li>
        <li>Keep the customer and admin sides connected through the same database.</li>
      </ul>
    </div>
    <div class="admin-about-panel admin-about-panel-gold">
      <h2>Location</h2>
      <p>20A Sandoval Ave, beside House of Dimsum.</p>
      <p>Designed for a lounge-like atmosphere where clients can unwind, refresh, and feel their best.</p>
      <a class="admin-gold-button compact" href="appointments.php">Manage Bookings</a>
    </div>
  </section>

  <section class="admin-about-map">
    <div>
      <p class="admin-eyebrow">Find Us</p>
      <h2>Visit The Lounge Royale</h2>
      <p>20A Sandoval Ave, beside House of Dimsum.</p>
    </div>
    <iframe
      title="The Lounge Royale location map"
      src="https://www.google.com/maps?q=20A%20Sandoval%20Ave%20beside%20House%20of%20Dimsum&output=embed"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"></iframe>
  </section>
</main>
</body>
</html>
