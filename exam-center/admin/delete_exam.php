<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];

$conn->query("DELETE FROM exam_questions WHERE exam_id = $exam_id");
$conn->query("DELETE FROM exam_assignments WHERE exam_id = $exam_id");
$conn->query("DELETE FROM exam_submissions WHERE exam_id = $exam_id");
$conn->query("DELETE FROM exams WHERE exam_id = $exam_id");

header("Location: exam_dashboard.php");
exit;
?>
