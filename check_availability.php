<?php
include "database.php";

header("Content-Type: application/json");

$staff_id = (int) ($_GET["staff_id"] ?? 0);
$date = trim($_GET["date"] ?? "");

if ($staff_id <= 0 || $date === "") {
    echo json_encode(["booked" => []]);
    exit();
}

$stmt = mysqli_prepare($conn, "
    SELECT appointment_time
    FROM appointments
    WHERE staff_id = ?
      AND appointment_date = ?
      AND status IN ('Pending', 'Confirmed')
");
mysqli_stmt_bind_param($stmt, "is", $staff_id, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$booked = [];
while ($row = mysqli_fetch_assoc($result)) {
    $booked[] = substr((string) $row["appointment_time"], 0, 5);
}

echo json_encode(["booked" => $booked]);
?>
