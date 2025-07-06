<?php
include '../database_connection/db_connect.php';
$id = $_GET['id'];

// Delete record
$conn->query("DELETE FROM students WHERE student_id = $id");

// Redirect
header("Location: manage_student.php");
exit;
?>
