<?php
$host = "srv1559.hstgr.io"; 
$username = "u252859821_admin";  
$password = "Admin@908";      
$database = "u252859821_spinawheel"; 

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
