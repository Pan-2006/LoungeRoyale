<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$service_id = (int) ($_POST['service_id'] ?? 0);
$staff_id = (int) ($_POST['staff_id'] ?? 0);

if ($_SERVER["REQUEST_METHOD"] !== "POST" || $service_id <= 0) {
    header("Location: booking.html");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Schedule</title>
</head>

<body>

<h2>Select Date and Time</h2>

<form action="booking.php" method="POST">

<input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
<input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">

<label>Date:</label>
<br>
<input
type="date"
name="date"
id="date"
min="<?php echo date('Y-m-d'); ?>"
required>

<small>Closed every Monday</small>

<br><br>

<label>Time:</label>
<br>
<input
type="time"
name="time"
min="11:00"
max="20:00"
step="1800"
required>

<small>Business Hours: 11:00 AM - 8:00 PM</small>

<br><br>

<button type="submit">Book Appointment</button>

</form>

<script>
document.getElementById("date").addEventListener("change", function () {
    var selectedDate = new Date(this.value);
    var day = selectedDate.getDay();

    if(day === 1){
        alert("The Lounge Royale is closed every Monday. Please choose another date.");
        this.value = "";
    }
});
</script>
</body>
</html>
