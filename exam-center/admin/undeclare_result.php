<?php
include '../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id'] ?? 0);
if ($exam_id > 0) {
    $conn->query("UPDATE exams SET result_declared = 0 WHERE exam_id = $exam_id");
}

header("Location: exam_dashboard.php");
exit;
