<?php
$host = "localhost";
$user = "u298112699_FAIZ2912";
$pass = "Faiz2912";
$db = "u298112699_FAIZ_COMPUTER";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
