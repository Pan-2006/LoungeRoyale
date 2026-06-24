<?php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id'])){
    echo "Please login first.";
    exit();
}

$user_id = $_SESSION['user_id'];

$check = mysqli_query($conn, "
    SELECT customers.name, customers.phone, users.email
    FROM users
    LEFT JOIN customers ON users.user_id = customers.user_id
    WHERE users.user_id='$user_id'
");
$row = mysqli_fetch_assoc($check);

if($row['name'] == null){
    mysqli_query($conn, "INSERT INTO customers (user_id, name, phone) VALUES ('$user_id', '', '')");
    $row['name']  = '';
    $row['phone'] = '';
}

if(isset($_POST['update'])){
    $name  = $_POST['name'];
    $phone = $_POST['phone'];
    mysqli_query($conn, "UPDATE customers SET name='$name', phone='$phone' WHERE user_id='$user_id'");
    mysqli_query($conn, "UPDATE users SET name='$name' WHERE user_id='$user_id'");
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – The Lounge Royale</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .profile-edit-form {
            position: absolute;
            z-index: 25;
            left: 4%;
            top: 8%;
            width: 67%;
            background: #181210;
            padding: 2.5% 3%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .profile-edit-form label {
            display: flex;
            flex-direction: column;
            gap: 3px;
            font-size: clamp(7px, 0.72vw, 11px);
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gold-light);
        }
        .profile-edit-form input {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(242,217,141,0.3);
            color: #fff8e7;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: clamp(9px, 0.88vw, 13px);
        }
        .profile-edit-form input:disabled { opacity: 0.45; cursor: not-allowed; }
        .profile-update-btn {
            align-self: flex-start;
            margin-top: 4px;
            padding: 6px 22px;
            background: linear-gradient(135deg, #f5c842, #e2991f);
            border: none;
            border-radius: 20px;
            font-size: clamp(8px, 0.78vw, 12px);
            font-weight: 800;
            text-transform: uppercase;
            color: #1a0f00;
            cursor: pointer;
        }
        .profile-update-btn:hover { background: linear-gradient(135deg, #ffd454, #e8a82e); }
    </style>
</head>
<body>

<div class="design-canvas">
    <img class="page-design" src="../assets/references/profile_customer.png" alt="My Profile">

    <!-- Original form — name="name", name="phone", name="update" untouched -->
    <form class="profile-edit-form" method="POST">
        <label>Name
            <input type="text" name="name"
                   value="<?php echo htmlspecialchars($row['name']); ?>" required>
        </label>
        <label>Email (cannot be changed)
            <input type="email" value="<?php echo htmlspecialchars($row['email']); ?>" disabled>
        </label>
        <label>Phone
            <input type="text" name="phone"
                   value="<?php echo htmlspecialchars($row['phone']); ?>">
        </label>
        <button type="submit" name="update" class="profile-update-btn">Update Profile</button>
    </form>

    <!-- Gold area -->
    <div class="appointments-cover">
        <div class="appt-empty">
            <a href="my_appointments.php"
               style="color:#1a0f00;font-weight:800;text-decoration:underline">
               View My Appointments →
            </a>
        </div>
    </div>
</div>

</body>
</html>
