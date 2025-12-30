<?php
include '../database_connection/db_connect.php';

$batch_id = $_POST['batch_id'];
$date     = $_POST['date'];
$status   = $_POST['status'];
foreach ($_POST['status'] as $table => $students) {
    foreach ($students as $student_id => $status) {

        $conn->query("
            INSERT INTO attendance 
            (batch_id, student_id, student_table, attendance_date, status)
            VALUES 
            ($batch_id, $student_id, '$table', '$date', '$status')
        ");
    }
}



header("Location: mark_attendance.php?success=1");
exit;
