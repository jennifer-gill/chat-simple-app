<?php
$servername = "localhost";
$username = "root";
$password = "root";
$db = "my_data_base";


// Create connection
$conn = mysqli_connect($servername, $username, $password,$db);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>