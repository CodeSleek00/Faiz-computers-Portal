<?php
session_start();
include '../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'])) {
    header("Location: login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table']; // students OR students26

$stmt = $conn->prepare("SELECT * FROM $table WHERE enrollment_id = ?");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo "Student record not found!";
    exit;
}
?>
