<?php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id'])){
    echo "Please login first.";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT
appointments.appointment_id,
services.service_name,
staff.staff_name,
appointments.appointment_date,
appointments.appointment_time,
appointments.status
FROM appointments
JOIN customers ON appointments.customer_id = customers.customer_id
JOIN services  ON appointments.service_id  = services.service_id
JOIN staff     ON appointments.staff_id    = staff.staff_id
WHERE customers.user_id = '$user_id'
ORDER BY appointments.appointment_date DESC
";
$result = mysqli_query($conn, $sql);

$nameRow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT customers.name, users.email FROM users
     LEFT JOIN customers ON users.user_id = customers.user_id
     WHERE users.user_id='$user_id'"));
$customerName  = $nameRow['name']  ?? '';
$customerEmail = $nameRow['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments – The Lounge Royale</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="design-canvas">
    <img class="page-design" src="../assets/references/profile_customer.png" alt="My Appointments">

    <!-- Dark header cover -->
    <div class="profile-cover-block">
        <div class="profile-hello">Hello!,</div>
        <div class="profile-name"><?php echo strtoupper(htmlspecialchars($customerName)); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($customerEmail); ?></div>
    </div>

    <!-- Gold table cover — original PHP loop untouched -->
    <div class="appointments-cover">
        <div class="appt-table-head">
            <span>Service</span>
            <span>Technician</span>
            <span>Date</span>
            <span>Time</span>
            <span>Status</span>
            <span>Action</span>
        </div>

        <?php if(mysqli_num_rows($result) === 0): ?>
        <div class="appt-empty">No appointments yet.</div>
        <?php else: ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="appt-row" style="grid-template-columns:20% 15% 15% 12% 13% 13%">
            <span><?php echo htmlspecialchars($row['service_name']); ?></span>
            <span><?php echo htmlspecialchars($row['staff_name']); ?></span>
            <span><?php echo htmlspecialchars($row['appointment_date']); ?></span>
            <span><?php echo htmlspecialchars($row['appointment_time']); ?></span>
            <span><?php echo htmlspecialchars($row['status']); ?></span>
            <span>
                <?php if($row['status'] == "Pending"): ?>
                <a class="appt-cancel-btn"
                   href="cancel_appointment.php?id=<?php echo $row['appointment_id']; ?>"
                   onclick="return confirm('Cancel this appointment?')">Cancel</a>
                <?php else: ?>
                —
                <?php endif; ?>
            </span>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
