<?php
// File: admin/customer_history.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$cid = (int)($_GET['id'] ?? 0);
if(!$cid){ header("Location: customers.php"); exit(); }

$customer = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT customers.*, users.email FROM customers
     JOIN users ON customers.user_id = users.user_id
     WHERE customers.customer_id='$cid'"
));

if(!$customer){ header("Location: customers.php"); exit(); }

$history = mysqli_query($conn, "
SELECT appointments.appointment_date, appointments.appointment_time,
       services.service_name, staff.staff_name, appointments.status
FROM appointments
JOIN services ON appointments.service_id = services.service_id
JOIN staff    ON appointments.staff_id   = staff.staff_id
WHERE appointments.customer_id='$cid'
ORDER BY appointments.appointment_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client History — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <div style="margin-bottom:24px;">
    <a href="customers.php" style="color:var(--gold-light);text-decoration:none;font-size:.85rem;">← Back to Clients</a>
  </div>

  <div class="profile-card" style="margin-bottom:32px;">
    <p class="greeting">Client</p>
    <h1 class="admin-name"><?php echo htmlspecialchars(strtoupper($customer['name'])); ?></h1>
    <p class="admin-email"><?php echo htmlspecialchars($customer['email']); ?></p>
    <?php if($customer['phone']): ?>
    <p class="admin-phone"><?php echo htmlspecialchars($customer['phone']); ?></p>
    <?php endif; ?>
  </div>

  <h2 style="color:var(--gold-light);font-family:'Cinzel',serif;font-size:1.1rem;margin-bottom:16px;">
    APPOINTMENT HISTORY
  </h2>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>DATE</th><th>TIME</th><th>SERVICE</th><th>TECHNICIAN</th><th>STATUS</th>
        </tr>
      </thead>
      <tbody>
        <?php if($history && mysqli_num_rows($history) > 0):
          while($row = mysqli_fetch_assoc($history)):
            $st = strtolower($row['status']);
        ?>
        <tr>
          <td><span class="date-pill"><?php echo date('n/j/Y',strtotime($row['appointment_date'])); ?></span></td>
          <td><?php echo date('g:iA',strtotime($row['appointment_time'])); ?></td>
          <td><?php echo htmlspecialchars($row['service_name']); ?></td>
          <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
          <td class="status-cell status-<?php echo $st; ?>"><?php echo strtoupper($row['status']); ?></td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr><td colspan="5" style="text-align:center;padding:24px;color:#555;">No appointment history.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>
