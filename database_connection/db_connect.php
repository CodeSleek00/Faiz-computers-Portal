<?php
$host = "localhost";
$user = "u298112699_FAIZ_MOHD";
$pass = "Faiz291205";
$db = "u298112699_FAIZ_COMPUTER_";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
