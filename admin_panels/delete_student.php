<?php
include '../database_connection/db_connect.php';
$id = $_GET['id'];

// Optional: delete photo from folder if needed
$conn->query("DELETE FROM my_student WHERE student_id = $id");

header("Location: view_students.php");
exit();
