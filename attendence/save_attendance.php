<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database_connection/db_connect.php';

if(!isset($_POST['student_id'])){
    die("No data received");
}

$date = $_POST['date'];
$ids = $_POST['student_id'];
$tables = $_POST['table_name'];
$statuses = $_POST['status'];

for($i=0; $i<count($ids); $i++){

    $id = $conn->real_escape_string($ids[$i]);
    $table = $conn->real_escape_string($tables[$i]);
    $status = $conn->real_escape_string($statuses[$i]);

    // 🔥 Check if already exists
    $check = $conn->query("
        SELECT id FROM attendance 
        WHERE student_id='$id' AND table_name='$table' AND date='$date'
    ");

    if(!$check){
        die("Check Error: " . $conn->error);
    }

    if($check->num_rows > 0){
        // 🔁 UPDATE
        $update = $conn->query("
            UPDATE attendance 
            SET status='$status' 
            WHERE student_id='$id' AND table_name='$table' AND date='$date'
        ");

        if(!$update){
            die("Update Error: " . $conn->error);
        }

    } else {
        // ➕ INSERT
        $insert = $conn->query("
            INSERT INTO attendance (student_id, table_name, date, status)
            VALUES ('$id','$table','$date','$status')
        ");

        if(!$insert){
            die("Insert Error: " . $conn->error);
        }
    }
}

header("Location: mark_attendance.php?success=1");
exit;
?>