<?php

include "database.php";

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = "customer";

if($password != $confirm_password){

    echo "
    <script>
        alert('Passwords do not match!');
        window.history.back();
    </script>
    ";

    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password, role)
        VALUES ('$name', '$email', '$hashedPassword', '$role')";

if(mysqli_query($conn, $sql)){

    $user_id = mysqli_insert_id($conn);

    $sql2 = "INSERT INTO customers (user_id, name, phone)
             VALUES ('$user_id', '$name', '')";

    if(mysqli_query($conn, $sql2)){

        echo "
        <script>
            alert('Registration successful!');
            window.location.href='login.html';
        </script>
        ";

    } else {
        echo "Customer error: " . mysqli_error($conn);
    }

} else {
    echo "User error: " . mysqli_error($conn);
}

?>