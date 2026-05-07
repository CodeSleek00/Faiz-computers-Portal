<?php

include '../db_connect.php';

if(isset($_POST['student_id'])){

    $student_id = $_POST['student_id'];
    $table_name = $_POST['table_name'];

    $date = date("Y-m-d");
    $time = date("H:i:s");

    $student_query = mysqli_query($conn,
    "SELECT * FROM $table_name WHERE id='$student_id'");

    $student = mysqli_fetch_assoc($student_query);

    $student_name = $student['name'];
    $enrollment_id = $student['enrollment_id'];

    $check = mysqli_query($conn,
    "SELECT * FROM attendance
    WHERE student_id='$student_id'
    AND table_name='$table_name'
    AND attendance_date='$date'");

    if(mysqli_num_rows($check) == 0){

        mysqli_query($conn,
        "INSERT INTO attendance(
            student_id,
            table_name,
            enrollment_id,
            student_name,
            attendance_date,
            attendance_time,
            status
        ) VALUES(
            '$student_id',
            '$table_name',
            '$enrollment_id',
            '$student_name',
            '$date',
            '$time',
            'Present'
        )");

        echo "Attendance Marked";
    }

}

?>