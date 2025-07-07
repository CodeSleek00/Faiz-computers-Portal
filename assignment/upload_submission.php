<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Please login to submit.");

$assignment_id = $_POST['assignment_id'] ?? null;
if (!$assignment_id || !is_numeric($assignment_id)) die("Invalid assignment ID.");

// Get student ID
$student_query = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment_id'");
if ($student_query->num_rows == 0) die("Student not found.");
$student_id = $student_query->fetch_assoc()['student_id'];

// Check if already submitted
$check = $conn->query("SELECT * FROM assignment_submissions WHERE student_id = $student_id AND assignment_id = $assignment_id");
if ($check->num_rows > 0) die("Assignment already submitted.");

// Handle file upload
$file_name = null;
if (!empty($_FILES['submitted_file']['name'])) {
    $upload_dir = "../uploads/submissions/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_name = time() . "_" . basename($_FILES['submitted_file']['name']);
    $file_path = $upload_dir . $file_name;
    move_uploaded_file($_FILES['submitted_file']['tmp_name'], $file_path);
}

// Handle written answer
$submitted_text = trim($_POST['submitted_text'] ?? '');

// At least one must be submitted
if (empty($submitted_text) && empty($file_name)) {
    die("Please upload a file or write an answer.");
}

// Insert into submissions
$stmt = $conn->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, submitted_text, submitted_file, submitted_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiss", $assignment_id, $student_id, $submitted_text, $file_name);
$stmt->execute();

header("Location: student_dashboard.php?submitted=1");
exit;
