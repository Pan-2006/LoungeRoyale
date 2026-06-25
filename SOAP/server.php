<?php

function checkAvailability($staff_id, $date, $time){

    $conn = mysqli_connect("localhost", "root", "", "lounge_royale", 3307);

    $sql = "
    SELECT * FROM appointments
    WHERE staff_id='$staff_id'
    AND appointment_date='$date'
    AND appointment_time='$time'
    AND status != 'Cancelled'
    ";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        return "Not Available";
    } else {
        return "Available";
    }
}

$options = array("uri" => "http://localhost/LoungeRoyale/soap/server.php");

$server = new SoapServer(null, $options);

$server->addFunction("checkAvailability");

$server->handle();

?>