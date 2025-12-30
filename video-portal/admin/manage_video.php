<?php
include "../includes/db_connect.php";

if($_GET['action']=='delete' && isset($_GET['id'])){
    $id = $_GET['id'];
    $file = $conn->query("SELECT filename FROM videos WHERE video_id=$id")->fetch_assoc()['filename'];
    unlink("../videos/$file");
    $conn->query("DELETE FROM videos WHERE video_id=$id");
    $conn->query("DELETE FROM video_assignments WHERE video_id=$id");
    header("Location: admin_dashboard.php");
    exit;
}
?>
