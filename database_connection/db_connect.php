<?php
$host = "178.16.136.97";
$user = "u298112699_FAIZ2912";
$pass = "FAIZ2912";
$db = "u298112699_FAIZ_COMPUTER";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
