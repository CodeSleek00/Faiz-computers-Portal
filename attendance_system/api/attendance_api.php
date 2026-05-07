<?php

include '../db_connect.php';

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
error_reporting(E_ALL);

header("Content-Type: application/json");

if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo json_encode([
            "ok" => false,
            "error" => "Fatal error",
            "detail" => $err['message'],
            "file" => basename($err['file']),
            "line" => $err['line']
        ]);
    }
});

if(isset($_POST['student_id'])){

    $student_id = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($_POST['student_id'] ?? ''));
    $table_name = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($_POST['table_name'] ?? ''));

    if ($student_id === '') {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "Invalid student_id"]);
        exit;
    }

    // Safety: only allow known tables.
    $allowed_tables = ["students", "students26"];
    if (!in_array($table_name, $allowed_tables, true)) {
        http_response_code(400);
        echo json_encode(["ok" => false, "error" => "Invalid table_name"]);
        exit;
    }

    $date = date("Y-m-d");
    $time = date("H:i:s");

    $id_field = ($table_name === "students") ? "student_id" : "id";
    $student_id_sql = "'" . mysqli_real_escape_string($conn, $student_id) . "'";
    $student_query = mysqli_query($conn, "SELECT * FROM `$table_name` WHERE `$id_field`=$student_id_sql");
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
