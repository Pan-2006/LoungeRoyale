<?php

$options = array(
    "location" => "http://localhost/LoungeRoyale/soap/server.php",
    "uri" => "http://localhost/LoungeRoyale/soap/server.php"
);

$client = new SoapClient(null, $options);

$result = $client->checkAvailability(1, "2026-06-20", "11:00");

echo "Slot Status: " . $result;

?>