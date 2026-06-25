<?php

session_start();

include "database.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){

    $user = mysqli_fetch_assoc($result);

    if(password_verify($password, $user['password'])){

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == "admin"){
            header("Location: admin/dashboard.php");
            exit();
        } else {
            header("Location: customer/dashboard_customer.php");
            exit();
        }

    } else {
        echo "Wrong password";
    }

} else {
    echo "Account not found";
}

?>