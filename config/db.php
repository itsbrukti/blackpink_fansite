<?php
$conn = new mysqli("localhost","root","","blackpink_fansite");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>