<?php
$servername = "localhost";
$username = "expobusi_franshu";
$password = "Fran!@#123";
$database = "expobusi_franchise";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

