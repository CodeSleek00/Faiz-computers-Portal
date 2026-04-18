<?php
include '../database_connection/db_connect.php';

$id = $_POST['id'];
$status = $_POST['status'];
$table = $_POST['table_name'];

// check if already exists
$check = $conn->query("SELECT id FROM student_status WHERE student_id='$id' AND table_name='$table'");

if($check->num_rows > 0){
    $conn->query("UPDATE student_status SET status='$status' WHERE student_id='$id' AND table_name='$table'");
} else {
    $conn->query("INSERT INTO student_status (student_id, table_name, status) VALUES ('$id','$table','$status')");
}

// redirect back
header("Location: student_status.php");
exit;
?>