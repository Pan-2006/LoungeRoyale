<?php
session_start();
include "database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$customerStmt = mysqli_prepare($conn, "
    SELECT c.customer_id, COALESCE(NULLIF(c.name, ''), u.name, 'Customer') AS customer_name, u.email
    FROM users u
    LEFT JOIN customers c ON c.user_id = u.user_id
    WHERE u.user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($customerStmt, "i", $user_id);
mysqli_stmt_execute($customerStmt);
$customerResult = mysqli_stmt_get_result($customerStmt);
$customer = mysqli_fetch_assoc($customerResult);

if (!$customer) {
    header("Location: login.html");
    exit();
}

$appointments = [];

if (!empty($customer["customer_id"])) {
    $customer_id = (int) $customer["customer_id"];
    $apptStmt = mysqli_prepare($conn, "
        SELECT
            a.appointment_date,
            a.appointment_time,
            a.status,
            s.service_name,
            st.staff_name
        FROM appointments a
        LEFT JOIN services s ON s.service_id = a.service_id
        LEFT JOIN staff st ON st.staff_id = a.staff_id
        WHERE a.customer_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    mysqli_stmt_bind_param($apptStmt, "i", $customer_id);
    mysqli_stmt_execute($apptStmt);
    $apptResult = mysqli_stmt_get_result($apptStmt);

    while ($row = mysqli_fetch_assoc($apptResult)) {
        $appointments[] = $row;
    }
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function niceDate($value) {
    if (!$value) return "-";
    return date("F j, Y", strtotime($value));
}

function niceTime($value) {
    if (!$value) return "-";
    return date("g:i A", strtotime($value));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - The Lounge Royale</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="site-shell">
        <header class="site-header">
            <a class="brand-link" href="index.html"><img class="brand-logo" src="assets/main logo.png" alt="The Lounge Royale"></a>
            <nav class="main-nav" aria-label="Main navigation">
                <a href="index.html">Home</a>
                <a href="about.html">About</a>
                <a href="services.html">Services</a>
                <a href="appointment.html">Appointment</a>
                <a href="profile.php" aria-current="page">Profile</a>
                <a class="nav-button" href="logout.php">Logout</a>
            </nav>
        </header>
        <main class="profile-page">
            <section class="profile-card">
                <p class="eyebrow">Hello,</p>
                <h1><?php echo h(strtoupper($customer["customer_name"])); ?></h1>
                <p><?php echo h($customer["email"] ?? ""); ?></p>
                <div class="appointments-table">
                    <h2>Confirmed Appointments</h2>
                    <div class="table-row">
                        <strong>Date</strong>
                        <strong>Time</strong>
                        <strong>Service</strong>
                        <strong>Technician</strong>
                        <strong>Status</strong>
                    </div>
                    <?php if (!$appointments): ?>
                        <div class="table-row">
                            <span>No appointment yet</span>
                            <span>-</span>
                            <span>-</span>
                            <span>-</span>
                            <span>-</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="table-row">
                                <span><?php echo h(niceDate($appointment["appointment_date"])); ?></span>
                                <span><?php echo h(niceTime($appointment["appointment_time"])); ?></span>
                                <span><?php echo h($appointment["service_name"] ?? "Service"); ?></span>
                                <span><?php echo h($appointment["staff_name"] ?? "To be assigned"); ?></span>
                                <span><?php echo h($appointment["status"] ?? "Pending"); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
