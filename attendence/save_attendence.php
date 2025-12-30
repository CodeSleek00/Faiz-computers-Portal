<?php
include '../database_connection/db_connect.php';

$batch_id = $_POST['batch_id'];
$date     = $_POST['date'];
$status   = $_POST['status'];

foreach ($status as $student_id => $att) {

    // prevent duplicate
    $check = $conn->query("
        SELECT id FROM attendance 
        WHERE student_id=$student_id 
        AND attendance_date='$date'
        AND batch_id=$batch_id
    ");

    if($check->num_rows == 0){
        $conn->query("
            INSERT INTO attendance (batch_id, student_id, attendance_date, status)
            VALUES ($batch_id, $student_id, '$date', '$att')
        ");
    }
}

header("Location: mark_attendance.php?success=1");
exit;
