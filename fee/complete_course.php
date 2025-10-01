<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// Mark course complete
$stmt = $conn->prepare("UPDATE student_fees SET course_complete=1 WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->close();

// Redirect
header("Location: admin_fee_dashboard.php?msg=Course marked complete for student ".$student_id);
exit;
?>
