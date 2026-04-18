<?php
include '../database_connection/db_connect.php';

$ids = $_POST['student_id'];
$tables = $_POST['table_name'];
$statuses = $_POST['status'];

for($i=0; $i<count($ids); $i++){

    $id = $ids[$i];
    $table = $tables[$i];
    $status = $statuses[$i];

    // check exist
    $check = $conn->query("SELECT id FROM student_status WHERE student_id='$id' AND table_name='$table'");

    if($check->num_rows > 0){
        $conn->query("UPDATE student_status SET status='$status' WHERE student_id='$id' AND table_name='$table'");
    } else {
        $conn->query("INSERT INTO student_status (student_id, table_name, status) VALUES ('$id','$table','$status')");
    }
}

header("Location: bulk_status_update.php?success=1");
exit;
?>