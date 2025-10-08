<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) exit;

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];
$exam_id = $_POST['exam_id'] ?? 0;

if ($exam_id) {
    $conn->query("UPDATE exam_submissions 
        SET status='submitted', submitted_at=NOW() 
        WHERE student_id='$student_id' AND exam_id='$exam_id'");
}
?>
