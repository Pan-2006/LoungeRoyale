<?php
if (!isset($pageTitle)) { $pageTitle = "Admin"; }
if (!isset($activePage)) { $activePage = ""; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?> | The Lounge Royale</title>
  <link rel="stylesheet" href="style.css">
  <script src="admin.js" defer></script>
</head>
<body>
<div class="page-shell">
<header class="admin-topbar">
  <a href="Dashboard.php">
    <img class="brand-logo" src="../assets/main logo.png" alt="The Lounge Royale">
  </a>

  <a class="profile-dot" href="profile.php">●</a>

  <nav class="admin-nav">
    <a href="../index.html">Home</a>
    <a href="../index.html#about">About</a>
    <a href="services_admin.php" class="<?php echo $activePage == 'services' ? 'active' : ''; ?>">Services</a>
    <a href="Dashboard.php" class="<?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">Admin Dashboard</a>
    <a href="appointments.php" class="<?php echo $activePage == 'appointments' ? 'active' : ''; ?>">Manage Bookings</a>
    <a href="customers.php" class="<?php echo $activePage == 'customers' ? 'active' : ''; ?>">Manage Clients</a>
    <a href="staffs.php" class="<?php echo $activePage == 'staffs' ? 'active' : ''; ?>">Staffs</a>
  </nav>

  <a class="logout-btn" href="../logout.php">Logout</a>
</header>

<main class="admin-main">
