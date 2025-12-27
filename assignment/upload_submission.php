<?php
session_start();
require_once '../database_connection/db_connect.php';

/* =====================================================
   1. LOGIN CHECK
===================================================== */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table']; // students OR students26
$student_id    = (int) $_SESSION['student_id'];

/* =====================================================
   2. VALIDATE ASSIGNMENT ID
===================================================== */
$assignment_id = $_POST['assignment_id'] ?? null;
if (!$assignment_id || !is_numeric($assignment_id)) {
    die("Invalid assignment ID.");
}

$assignment_id = (int) $assignment_id;

/* =====================================================
   3. CHECK IF ALREADY SUBMITTED (SAFE)
===================================================== */
$stmt = $conn->prepare("SELECT submission_id FROM assignment_submissions WHERE student_id = ? AND assignment_id = ? LIMIT 1");
$stmt->bind_param("ii", $student_id, $assignment_id);
$stmt->execute();
$check_result = $stmt->get_result();

if ($check_result->num_rows > 0) {
    die("Assignment already submitted.");
}

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
