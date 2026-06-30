<?php
// File: admin/appointments.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$search     = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterDate   = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';

$where = [];
$search_safe = mysqli_real_escape_string($conn, $search);
$status_safe = mysqli_real_escape_string($conn, $filterStatus);
$date_safe   = mysqli_real_escape_string($conn, $filterDate);

if($search_safe !== ''){
    $where[] = "(customers.name LIKE '%$search_safe%' OR services.service_name LIKE '%$search_safe%' OR users.email LIKE '%$search_safe%')";
}
if($status_safe !== ''){
    $where[] = "appointments.status = '$status_safe'";
}
if($date_safe !== ''){
    $where[] = "appointments.appointment_date = '$date_safe'";
}

$whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
SELECT
  appointments.appointment_id,
  appointments.appointment_date,
  appointments.appointment_time,
  customers.name,
  users.email,
  services.service_name,
  staff.staff_name,
  appointments.status
FROM appointments
JOIN customers ON appointments.customer_id = customers.customer_id
JOIN users     ON customers.user_id = users.user_id
JOIN services  ON appointments.service_id  = services.service_id
JOIN staff     ON appointments.staff_id    = staff.staff_id
$whereClause
ORDER BY appointments.appointment_date DESC, appointments.appointment_time ASC
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Bookings — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <h1 class="section-title">MANAGE ALL BOOKINGS</h1>

  <!-- Filter Bar -->
  <form method="GET" class="filter-bar" style="margin-bottom:24px;">
    <label>filter:</label>
    <select name="status" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <option value="Pending"   <?php echo $filterStatus=='Pending'   ? 'selected':'' ?>>Pending</option>
      <option value="Confirmed" <?php echo $filterStatus=='Confirmed' ? 'selected':'' ?>>Confirmed</option>
      <option value="Completed" <?php echo $filterStatus=='Completed' ? 'selected':'' ?>>Completed</option>
      <option value="Cancelled" <?php echo $filterStatus=='Cancelled' ? 'selected':'' ?>>Cancelled</option>
    </select>
    <input type="date" name="filter_date" value="<?php echo htmlspecialchars($filterDate); ?>">
    <input type="text" name="search" placeholder="Search client or service…" value="<?php echo htmlspecialchars($search); ?>" style="min-width:200px;">
    <button type="submit">Search</button>
    <a href="appointments.php" style="color:var(--text-muted);font-size:.82rem;text-decoration:none;margin-left:6px;">Clear</a>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>DATE</th>
          <th>TIME</th>
          <th>CLIENT NAME</th>
          <th>EMAIL</th>
          <th>SERVICE</th>
          <th>TECHNICIAN ASSIGNED</th>
          <th>STATUS</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php if($result && mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
            $st = strtolower($row['status']);
            $stClass = 'status-' . $st;
        ?>
        <tr>
          <td><span class="date-pill"><?php echo date('n/j/Y', strtotime($row['appointment_date'])); ?></span></td>
          <td><?php echo date('g:iA', strtotime($row['appointment_time'])); ?></td>
          <td><?php echo htmlspecialchars(strtoupper($row['name'])); ?></td>
          <td>
            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"
               style="color:var(--black);text-decoration:underline;font-size:.8rem;">
              <?php echo htmlspecialchars($row['email']); ?>
            </a>
          </td>
          <td><?php echo htmlspecialchars(strtoupper($row['service_name'])); ?></td>
          <td><?php echo htmlspecialchars(strtoupper($row['staff_name'])); ?></td>
          <td class="status-cell <?php echo $stClass; ?>">
            <?php echo strtoupper($row['status']); ?>
          </td>
          <td>
            <?php if($row['status'] == 'Pending'): ?>
              <a href="confirm.php?id=<?php echo $row['appointment_id']; ?>"
                 class="action-btn btn-confirm"
                 onclick="return confirm('Confirm this booking?')">Confirm</a>
            <?php endif; ?>
            <?php if(in_array($row['status'], ['Pending','Confirmed'])): ?>
              <a href="complete.php?id=<?php echo $row['appointment_id']; ?>"
                 class="action-btn btn-complete"
                 onclick="return confirm('Mark as completed?')">Complete</a>
              <a href="cancel.php?id=<?php echo $row['appointment_id']; ?>"
                 class="action-btn btn-cancel"
                 onclick="return confirm('Cancel this booking?')">Cancel</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile;
        else: ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:32px;color:#555;">
            No bookings found.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>
