<?php

include '../db_connect.php';

header("Content-Type: application/json");

if(isset($_POST['student_id'])){

    $student_id = (int)($_POST['student_id'] ?? 0);
    $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($_POST['table_name'] ?? ''));

    if ($student_id <= 0) {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "Invalid student_id"]);
        exit;
    }

    // Safety: only allow known tables.
    $allowed_tables = ["students"];
    if (!in_array($table_name, $allowed_tables, true)) {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "Invalid table_name"]);
        exit;
    }

    $date = date("Y-m-d");
    $time = date("H:i:s");

    $student_query = mysqli_query($conn, "SELECT * FROM `$table_name` WHERE id=$student_id");
    if (!$student_query) {
        http_response_code(500);
        echo json_encode(["ok" => false, "error" => "Student query failed"]);
        exit;
    }

    $student = mysqli_fetch_assoc($student_query);
    if (!$student) {
        http_response_code(404);
        echo json_encode(["ok" => false, "error" => "Student not found"]);
        exit;
    }

    $student_name = $student['name'];
    $enrollment_id = $student['enrollment_id'];

    $check = mysqli_query($conn,
    "SELECT * FROM attendance
    WHERE student_id=$student_id
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
            status,
            attendance_method
        ) VALUES(
            $student_id,
            '$table_name',
            '$enrollment_id',
            '$student_name',
            '$date',
            '$time',
            'Present',
            'face'
        )");

        echo json_encode(["ok" => true, "marked" => true]);
        exit;
    }

    echo json_encode(["ok" => true, "marked" => false]);
    exit;
}

http_response_code(400);
echo json_encode(["ok" => false, "error" => "Missing student_id"]);
?>
