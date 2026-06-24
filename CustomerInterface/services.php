<?php

include "../database.php";

if(!isset($_GET['category']) || empty($_GET['category'])){
    echo "Please select a service category first.";
    echo "<br><br>";
    echo "<a href='booking.html'>Go back to booking</a>";
    exit();
}

$category = $_GET['category'];

$sql = "SELECT * FROM services WHERE category='$category'";
$result = mysqli_query($conn, $sql);

$pngMap = [
    'Hand Services'   => '../assets/references/appointment_HandServices_customer.png',
    'Foot Services'   => '../assets/references/appointment_FootSpa_customer.png',
    'Royale Deluxe'   => '../assets/references/appointment_DeluxePackage_customer.png',
    'Nail Extension'  => '../assets/references/appointment_HandServices_customer.png',
    'Kiddie Services' => '../assets/references/appointment_KiddieAndOthers_customer.png',
    'Wax Services'    => '../assets/references/appointment_WaxServices_customer.png',
];
$png = $pngMap[$category] ?? '../assets/references/appointment_HandServices_customer.png';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Select Service – The Lounge Royale</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="design-canvas">
    <img class="page-design" src="<?php echo $png; ?>" alt="Select Service">

    <form class="booking-panel" action="appointment.php" method="POST">

        <div class="bp-group">
            <div class="bp-label">Choose Service</div>
            <select name="service_id" class="bp-select" required>
                <option value="">Select a service...</option>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <option value="<?php echo $row['service_id']; ?>">
                    <?php echo $row['service_name']; ?> - \u20b1<?php echo $row['price']; ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="bp-group">
            <div class="bp-label">Preferred Technician</div>
            <select name="staff_id" class="bp-select">
                <option value="0">No preference (Recommend one)</option>
                <option value="1">Nicole</option>
                <option value="2">Justine</option>
                <option value="3">Rica</option>
            </select>
        </div>

        <button type="submit" class="bp-submit">Continue \u2192</button>
    </form>
</div>

</body>
</html>
