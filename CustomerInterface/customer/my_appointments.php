<?php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "
    SELECT
        appointments.appointment_id,
        services.service_name,
        staff.staff_name,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.status
    FROM appointments
    JOIN customers ON appointments.customer_id = customers.customer_id
    JOIN services ON appointments.service_id = services.service_id
    JOIN staff ON appointments.staff_id = staff.staff_id
    WHERE customers.user_id = ?
    ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<h1>My Appointments</h1>

<table border="1" cellpadding="10">
<tr>
    <th>Service</th>
    <th>Technician</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
    <td><?php echo htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['staff_name'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['appointment_date'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['appointment_time'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td>
    <?php if($row['status'] === "Pending"){ ?>
        <a href="cancel_appointment.php?id=<?php echo (int) $row['appointment_id']; ?>">Cancel</a>
    <?php } else { ?>
        No action
    <?php } ?>
    </td>
</tr>
<?php } ?>

</table>

<br>
<a href="booking.html">Book Another Appointment</a>
<br>
<a href="dashboard_customer.php">Back to Dashboard</a>
