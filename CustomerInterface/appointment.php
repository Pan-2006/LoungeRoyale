<?php
$service_id = $_POST['service_id'];
$staff_id   = $_POST['staff_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Schedule – The Lounge Royale</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="design-canvas">
    <img class="page-design" src="../assets/references/appointment_HandServices_customer.png" alt="Select Schedule">

    <form class="booking-panel" action="booking.php" method="POST">

        <!-- Original hidden fields — untouched -->
        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
        <input type="hidden" name="staff_id"   value="<?php echo $staff_id; ?>">

        <div class="bp-group">
            <div class="bp-label">Select Date <small style="font-weight:400;text-transform:none;font-size:0.85em">Closed Mondays</small></div>
            <!-- Original date input — name="date" untouched -->
            <input type="date" name="date" id="date"
                   min="<?php echo date('Y-m-d'); ?>"
                   class="bp-select" required>
        </div>

        <div class="bp-group">
            <div class="bp-label">Select Time <small style="font-weight:400;text-transform:none;font-size:0.85em">11 AM – 8 PM</small></div>
            <!-- Original time input — name="time" untouched -->
            <input type="time" name="time"
                   min="11:00" max="20:00" step="1800"
                   class="bp-select" required>
        </div>

        <div class="form-message" id="schedMsg" role="alert"></div>
        <button type="submit" class="bp-submit">Book Appointment</button>
    </form>
</div>

<!-- Original Monday-block script — untouched -->
<script>
document.getElementById("date").addEventListener("change", function () {
    let selectedDate = new Date(this.value);
    let day = selectedDate.getDay();
    if(day === 1){
        alert("The Lounge Royale is closed every Monday. Please choose another date.");
        this.value = "";
    }
});
</script>

</body>
</html>
