<?php
include 'db_connect.php';
$id = $_GET['id'];
$conn->query("DELETE FROM videos WHERE id=$id");
header("Location: admin_videos.php");
?>
