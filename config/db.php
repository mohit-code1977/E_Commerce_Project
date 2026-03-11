<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "ecommerce_task";

$conn = new mysqli($servername, $username, $password, $database);

if($conn->connect_error){
    die("Connection Failed".$conn->connect_error);
}

// echo "<script>console.log('Database Connected Successfully');</script>";


?>