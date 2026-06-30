<?php
// File: admin/staffs.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

// Handle add staff
if(isset($_POST['add_staff'])){
    $sn = mysqli_real_escape_string($conn, trim($_POST['staff_name']));
    $sa = mysqli_real_escape_string($conn, trim($_POST['age'] ?? ''));
    $sad = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
    $sp = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $se = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    mysqli_query($conn, "INSERT INTO staff (staff_name, age, address, phone, email) VALUES ('$sn','$sa','$sad','$sp','$se')");
    header("Location: staffs.php");
    exit();
}

// Handle delete staff
if(isset($_GET['delete'])){
    $did = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM staff WHERE staff_id='$did'");
    header("Location: staffs.php");
    exit();
}

// Fetch all staff
$staffResult = mysqli_query($conn, "SELECT * FROM staff ORDER BY staff_name ASC");
$staffList = [];
while($s = mysqli_fetch_assoc($staffResult)) $staffList[] = $s;

// Days of the week for schedule
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// Build a weekly schedule: for each day, which staff have bookings today (this week)
// We'll show staff names per day from appointments this week
$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd   = date('Y-m-d', strtotime('sunday this week'));

$schedSql = "
SELECT DAYNAME(appointment_date) AS day_name, staff.staff_name
FROM appointments
JOIN staff ON appointments.staff_id = staff.staff_id
WHERE appointment_date BETWEEN '$weekStart' AND '$weekEnd'
  AND appointments.status NOT IN ('Cancelled')
GROUP BY DAYNAME(appointment_date), staff.staff_id
ORDER BY FIELD(DAYNAME(appointment_date),'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), staff.staff_name
";
$schedResult = mysqli_query($conn, $schedSql);

$schedule = [];
while($row = mysqli_fetch_assoc($schedResult)){
    $schedule[$row['day_name']][] = $row['staff_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staffs — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:14px;">
    <h1 class="section-title" style="margin-bottom:0;">STAFFS OVERVIEW</h1>
    <button class="action-btn btn-gold" onclick="document.getElementById('addStaffModal').classList.add('open')">
      + Add Staff
    </button>
  </div>

  <div class="staffs-layout">

    <!-- Staff List -->
    <div class="staff-list">
      <?php if(empty($staffList)): ?>
        <p style="color:var(--text-muted);">No staff found. Add your first staff member.</p>
      <?php endif; ?>
      <?php foreach($staffList as $s): ?>
      <div class="staff-card">
        <div class="staff-name"><?php echo htmlspecialchars($s['staff_name']); ?></div>
        <p>
          <?php if(!empty($s['age'])): ?>Age: <?php echo htmlspecialchars($s['age']); ?><br><?php endif; ?>
          <?php if(!empty($s['address'])): ?>Address: <?php echo htmlspecialchars($s['address']); ?><br><?php endif; ?>
          <?php if(!empty($s['phone'])): ?>Contact No.: <?php echo htmlspecialchars($s['phone']); ?><br><?php endif; ?>
          <?php if(!empty($s['email'])): ?>Email: <?php echo htmlspecialchars($s['email']); ?><?php endif; ?>
        </p>
        <div style="margin-top:12px;">
          <a href="staffs.php?delete=<?php echo $s['staff_id']; ?>"
             class="action-btn btn-delete"
             onclick="return confirm('Delete <?php echo htmlspecialchars($s['staff_name']); ?>?')"
             style="font-size:.72rem;padding:4px 10px;">Delete</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Weekly Schedule -->
    <div class="schedule-section">
      <div class="schedule-title">WEEKLY SCHEDULE</div>
      <div style="overflow-x:auto;">
        <table class="schedule-table">
          <thead>
            <tr>
              <?php foreach($days as $d): ?>
              <th><?php echo $d; ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php
            // Find max rows needed
            $maxRows = 0;
            foreach($days as $d){
                $cnt = isset($schedule[$d]) ? count($schedule[$d]) : 0;
                if($cnt > $maxRows) $maxRows = $cnt;
            }
            if($maxRows === 0) $maxRows = count($staffList) ?: 3;

            for($r = 0; $r < $maxRows; $r++):
            ?>
            <tr>
              <?php foreach($days as $d):
                if($d === 'Monday'){
                  echo '<td class="closed">CLOSED</td>';
                } else {
                  $name = isset($schedule[$d][$r]) ? htmlspecialchars($schedule[$d][$r]) : '';
                  // Fallback: show staff names from list if no schedule data
                  if(!$name && !empty($staffList[$r])){
                    $name = htmlspecialchars($staffList[$r]['staff_name']);
                  }
                  echo '<td>' . $name . '</td>';
                }
              endforeach; ?>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
      <p style="color:var(--text-muted);font-size:.78rem;margin-top:10px;">
        Weekly schedule is generated from this week's confirmed/pending appointments.
      </p>
    </div>

  </div>
</div>

<!-- Add Staff Modal -->
<div class="modal-overlay" id="addStaffModal">
  <div class="modal-box">
    <button class="modal-close" onclick="document.getElementById('addStaffModal').classList.remove('open')">&times;</button>
    <h2>Add New Staff</h2>
    <form method="POST">
      <label>Staff Name *</label>
      <input type="text" name="staff_name" required>

      <label>Age</label>
      <input type="number" name="age">

      <label>Address</label>
      <input type="text" name="address">

      <label>Contact Number</label>
      <input type="text" name="phone">

      <label>Email</label>
      <input type="email" name="email">

      <div class="modal-actions">
        <button type="button" class="btn-cancel-modal"
                onclick="document.getElementById('addStaffModal').classList.remove('open')">Cancel</button>
        <button type="submit" name="add_staff" class="btn-save">Add Staff</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
