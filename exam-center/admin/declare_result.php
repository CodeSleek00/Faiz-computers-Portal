<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];
$show = $_GET['show'] === '1' ? 1 : 0;

$conn->query("UPDATE exams SET result_declared = $show WHERE exam_id = $exam_id");

header("Location: exam_dashboard.php");
exit;
?>
