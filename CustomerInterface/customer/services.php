<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

include "../database.php";

if (!isset($_GET['category']) || trim($_GET['category']) === '') {
    echo "Please select a service category first.";
    echo "<br><br>";
    echo "<a href='booking.html'>Go back to booking</a>";
    exit();
}

$category = trim($_GET['category']);

$stmt = mysqli_prepare($conn, "SELECT service_id, service_name, price FROM services WHERE category = ? ORDER BY service_name");
mysqli_stmt_bind_param($stmt, "s", $category);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Service</title>
<style>
select{
    width: 700px;
    padding: 10px;
    font-size: 16px;
}

button{
    padding: 8px 18px;
    font-size: 15px;
}
</style>
</head>

<body>

<h2>Select Service</h2>

<?php if (mysqli_num_rows($result) === 0) { ?>
    <p>No services are available for this category yet.</p>
    <a href="booking.html">Go back to booking</a>
<?php } else { ?>
<form action="appointment.php" method="POST">

<label>Choose Service:</label>
<br><br>

<select name="service_id" required>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
    <option value="<?php echo (int) $row['service_id']; ?>">
        <?php echo htmlspecialchars($row['service_name'], ENT_QUOTES, 'UTF-8'); ?> - PHP <?php echo htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8'); ?>
    </option>
<?php } ?>
</select>

<br><br><br>

<label>Preferred Technician:</label>
<br><br>

<select name="staff_id">
    <option value="0">No preference (Recommend one)</option>
    <option value="1">Nicole</option>
    <option value="2">Justine</option>
    <option value="3">Rica</option>
</select>

<br><br>

<button type="submit">Continue</button>

</form>
<?php } ?>

</body>
</html>
