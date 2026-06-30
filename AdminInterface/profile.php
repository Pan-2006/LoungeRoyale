<?php
// File: admin/profile.php
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

$uid = (int)$_SESSION['user_id'];

// Fetch admin info — try users table first, fallback to customers
$userRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE user_id='$uid'"));

$msg = '';

// Handle profile update
if(isset($_POST['update_profile'])){
    $name  = mysqli_real_escape_string($conn, trim($_POST['name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

    // Check if users table has a name/phone column
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'name'");
    if(mysqli_num_rows($cols) > 0){
        mysqli_query($conn, "UPDATE users SET name='$name' WHERE user_id='$uid'");
    }
    $colsPhone = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'phone'");
    if(mysqli_num_rows($colsPhone) > 0){
        mysqli_query($conn, "UPDATE users SET phone='$phone' WHERE user_id='$uid'");
    }

    // Handle password change
    if(!empty($_POST['new_password'])){
        $np = $_POST['new_password'];
        $hashed = password_hash($np, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE user_id='$uid'");
    }

    $_SESSION['name'] = $name;
    $msg = 'Profile updated successfully.';
    $userRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE user_id='$uid'"));
}

$displayName  = $userRow['name']  ?? ($_SESSION['name'] ?? 'Admin');
$displayEmail = $userRow['email'] ?? '';
$displayPhone = $userRow['phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile — The Lounge Royale</title>
<link rel="stylesheet" href="admin_style.css">
<link rel="stylesheet" href="admin_overrides.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="admin-page">

  <?php if($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <div class="profile-card">
    <p class="greeting">Hello!,</p>
    <h1 class="admin-name"><?php echo htmlspecialchars(strtoupper($displayName)); ?></h1>
    <p class="admin-email"><?php echo htmlspecialchars($displayEmail); ?></p>
    <?php if($displayPhone): ?>
    <p class="admin-phone"><?php echo htmlspecialchars($displayPhone); ?></p>
    <?php endif; ?>

    <hr style="border-color:#333;margin:28px 0;">

    <h3 style="color:var(--gold-light);font-family:'Cinzel',serif;font-size:1rem;margin-bottom:4px;">
      Update Profile
    </h3>

    <form method="POST" class="profile-edit-form">

      <label>Name</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($displayName); ?>">

      <label>Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($displayPhone); ?>">

      <label>New Password <span style="font-weight:400;color:var(--text-muted);">(leave blank to keep current)</span></label>
      <input type="password" name="new_password" placeholder="••••••••">

      <div style="margin-top:22px;">
        <button type="submit" name="update_profile" class="action-btn btn-gold"
                style="padding:10px 28px;font-size:.88rem;">
          Save Changes
        </button>
        <a href="../logout.php" class="action-btn btn-cancel"
           style="padding:10px 28px;font-size:.88rem;margin-left:10px;">
          Logout
        </a>
      </div>

    </form>
  </div>

</div>

</body>
</html>
