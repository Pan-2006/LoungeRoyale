<?php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

if(isset($_POST['update'])){
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        echo "<script>alert('Name is required.'); window.history.back();</script>";
        exit();
    }

    $customerStmt = mysqli_prepare($conn, "UPDATE customers SET name = ?, phone = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($customerStmt, "ssi", $name, $phone, $user_id);
    mysqli_stmt_execute($customerStmt);

    $userStmt = mysqli_prepare($conn, "UPDATE users SET name = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($userStmt, "si", $name, $user_id);
    mysqli_stmt_execute($userStmt);

    header("Location: profile.php");
    exit();
}

$checkStmt = mysqli_prepare($conn, "
    SELECT customers.name, customers.phone, users.email
    FROM users
    LEFT JOIN customers ON users.user_id = customers.user_id
    WHERE users.user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($checkStmt, "i", $user_id);
mysqli_stmt_execute($checkStmt);
$check = mysqli_stmt_get_result($checkStmt);
$row = mysqli_fetch_assoc($check);

if(!$row){
    echo "Profile not found.";
    exit();
}

if($row['name'] === null){
    $blank = "";
    $insertStmt = mysqli_prepare($conn, "INSERT INTO customers (user_id, name, phone) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($insertStmt, "iss", $user_id, $blank, $blank);
    mysqli_stmt_execute($insertStmt);

    $row['name'] = '';
    $row['phone'] = '';
}
?>

<h1>My Profile</h1>

<form method="POST">

Name:<br>
<input type="text" name="name" value="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>" required>

<br><br>

Email:<br>
<input type="email" value="<?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?>" disabled>

<br><br>

Phone:<br>
<input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

<br><br>

<button type="submit" name="update">Update Profile</button>

</form>

<br>
<a href="booking.html">Book Appointment</a>
<br>
<a href="my_appointments.php">My Appointments</a>
<br>
<a href="dashboard_customer.php">Back to Dashboard</a>
