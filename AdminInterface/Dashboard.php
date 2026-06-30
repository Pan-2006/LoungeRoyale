<?php
// File: admin/Dashboard.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// --- Date/filter params ---
$selectedDate  = isset($_GET['date'])  ? $_GET['date']  : date('Y-m-d');
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear  = isset($_GET['year'])  ? $_GET['year']  : date('Y');

// --- Stats ---
$totalBookings   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments"))['total'];
$confirmedCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status='Confirmed'"))['total'];
$pendingCount    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status='Pending'"))['total'];

$dailySales   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) AS total FROM sales WHERE sale_date='$selectedDate'"))['total'];
$weeklySales  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) AS total FROM sales WHERE YEARWEEK(sale_date,1)=YEARWEEK('$selectedDate',1)"))['total'];
$monthlySales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) AS total FROM sales WHERE MONTH(sale_date)='$selectedMonth' AND YEAR(sale_date)='$selectedYear'"))['total'];
$yearlySales  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) AS total FROM sales WHERE YEAR(sale_date)='$selectedYear'"))['total'];

// --- Today's Appointments ---
$todaySql = "
SELECT 
  customers.name,
  appointments.appointment_time,
  services.service_name,
  staff.staff_name,
  appointments.status
FROM appointments
JOIN customers ON appointments.customer_id = customers.customer_id
JOIN services  ON appointments.service_id  = services.service_id
JOIN staff     ON appointments.staff_id    = staff.staff_id
WHERE appointments.appointment_date = '$selectedDate'
ORDER BY appointments.appointment_time ASC
";
$todayResult = mysqli_query($conn, $todaySql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <!-- Date filter form -->
  <form method="GET" style="margin-bottom:20px; display:flex; gap:14px; align-items:center; flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:8px;">
      <label style="color:var(--text-light);font-size:.82rem;font-weight:600;">Date:</label>
      <input type="date" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>"
             style="background:var(--dark-card);border:1px solid var(--gold);color:var(--white);padding:6px 12px;border-radius:8px;font-family:'Raleway',sans-serif;font-size:.82rem;outline:none;">
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <label style="color:var(--text-light);font-size:.82rem;font-weight:600;">Month:</label>
      <select name="month" style="background:var(--dark-card);border:1px solid var(--gold);color:var(--white);padding:6px 12px;border-radius:8px;font-family:'Raleway',sans-serif;font-size:.82rem;outline:none;">
        <?php
        for($m=1;$m<=12;$m++){
          $mn = str_pad($m,2,'0',STR_PAD_LEFT);
          $mname = date("F", mktime(0,0,0,$m,1));
          $sel = ($selectedMonth == $mn) ? 'selected' : '';
          echo "<option value='$mn' $sel>$mname</option>";
        }
        ?>
      </select>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <label style="color:var(--text-light);font-size:.82rem;font-weight:600;">Year:</label>
      <select name="year" style="background:var(--dark-card);border:1px solid var(--gold);color:var(--white);padding:6px 12px;border-radius:8px;font-family:'Raleway',sans-serif;font-size:.82rem;outline:none;">
        <?php
        for($y=2024;$y<=2035;$y++){
          $sel = ($selectedYear == $y) ? 'selected' : '';
          echo "<option value='$y' $sel>$y</option>";
        }
        ?>
      </select>
    </div>
    <button type="submit" style="background:var(--gold-btn);color:var(--black);border:none;padding:8px 20px;border-radius:8px;font-weight:700;font-size:.82rem;cursor:pointer;font-family:'Raleway',sans-serif;">View</button>
  </form>

  <h1 class="section-title">ADMIN DASHBOARD OVERVIEW</h1>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-value"><?php echo $totalBookings; ?></div>
      <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo $confirmedCount; ?></div>
      <div class="stat-label">Confirmed</div>
    </div>
    <div class="stat-card">
      <div class="stat-value"><?php echo $pendingCount; ?></div>
      <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">₱<?php echo number_format($dailySales,0); ?></div>
      <div class="stat-label">Daily Sales</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">₱<?php echo number_format($weeklySales,0); ?></div>
      <div class="stat-label">Weekly Sales</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">₱<?php echo number_format($monthlySales,0); ?></div>
      <div class="stat-label">Monthly Sales</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">₱<?php echo number_format($yearlySales,0); ?></div>
      <div class="stat-label">Yearly Sales</div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="dash-actions">
    <a href="appointments.php" class="dash-btn">MANAGE BOOKINGS</a>
    <a href="customers.php" class="dash-btn">MANAGE CLIENTS</a>
    <a href="staffs.php" class="dash-btn">STAFFS</a>
    <a href="services_admin.php" class="dash-btn">MANAGE SERVICES</a>
    <a href="sales.php" class="dash-btn">SALES REPORT</a>
    <a href="walkin_sale.php" class="dash-btn">WALK-IN SALE</a>
  </div>

</div><!-- /.admin-page -->

<!-- Today's Appointments -->
<div style="background:var(--sand);padding:40px 48px;">
  <div style="max-width:1400px;margin:0 auto;">
    <h2 class="today-title" style="font-family:'Cinzel',serif;font-size:1.6rem;font-weight:700;color:var(--black);letter-spacing:.04em;margin-bottom:24px;">
      TODAY'S APPOINTMENT
    </h2>

    <?php if($todayResult && mysqli_num_rows($todayResult) > 0): ?>
    <table class="today-table">
      <thead>
        <tr>
          <th>NAME</th>
          <th>TIME</th>
          <th>SERVICE</th>
          <th>TECHNICIAN ASSIGNED</th>
          <th>STATUS</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($todayResult)): 
          $st = strtolower($row['status']);
        ?>
        <tr>
          <td><?php echo htmlspecialchars(strtoupper($row['name'])); ?></td>
          <td><?php echo date('g:iA', strtotime($row['appointment_time'])); ?></td>
          <td><?php echo htmlspecialchars(strtoupper($row['service_name'])); ?></td>
          <td><?php echo htmlspecialchars(strtoupper($row['staff_name'])); ?></td>
          <td>
            <span class="status-badge <?php echo $st; ?>">
              <?php echo strtoupper($row['status']); ?>
            </span>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p style="color:var(--black);font-size:.9rem;padding:20px 0;">No appointments for <?php echo htmlspecialchars($selectedDate); ?>.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
