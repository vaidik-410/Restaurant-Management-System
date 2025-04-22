<?php

    $server = "localhost";
    $user = "root";
    $password = "";
    $name = "resturant";

    $conn = new mysqli("localhost", "root", "", $name);  // database name = user
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

?>