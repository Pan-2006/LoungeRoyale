<?php

include "database.php";

$sql = "INSERT INTO users (email, password, role)
VALUES ('admin@gmail.com','123','admin')";

mysqli_query($conn, $sql);

echo "Saved successfully";

?>