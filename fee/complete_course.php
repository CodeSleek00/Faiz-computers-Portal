<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// Ensure column exists in student_fees table
// ALTER TABLE student_fees ADD COLUMN course_complete TINYINT(1) NOT NULL DEFAULT 0;

// Update student course status to complete in student_fees
$stmt = $conn->prepare("UPDATE student_fees SET course_complete=1 WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->close();

// Redirect to dashboard
header("Location: admin_fee_dashboard.php?msg=Course marked complete for student ".$student_id);
exit;
?>
