<?php
include '../database_connection/db_connect.php';

$date = $_POST['date'];
$ids = $_POST['student_id'];
$tables = $_POST['table_name'];
$statuses = $_POST['status'];

for($i=0; $i<count($ids); $i++){

    $id = $ids[$i];
    $table = $tables[$i];
    $status = $statuses[$i];

    $conn->query("
    INSERT INTO attendance (student_id, table_name, date, status)
    VALUES ('$id','$table','$date','$status')
    ");
}

header("Location: mark_attendance.php?success=1");
?>