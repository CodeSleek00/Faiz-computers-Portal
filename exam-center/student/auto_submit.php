<?php
session_start();
include '../../database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    exit('Login required');
}

/* ================= EXAM ID ================= */
$exam_id = $_POST['exam_id'] ?? 0;
$exam_id = intval($exam_id);

if ($exam_id <= 0) {
    exit('Invalid Exam');
}

/* ================= AUTO SUBMIT ================= */
$stmt = $conn->prepare("
    UPDATE exam_submissions
    SET status = 'submitted',
        submitted_at = NOW()
    WHERE enrollment_id = ?
      AND exam_id = ?
      AND status != 'submitted'
");

$stmt->bind_param("si", $enrollment_id, $exam_id);
$stmt->execute();

/* ================= RESPONSE ================= */
if ($stmt->affected_rows > 0) {
    echo "auto_submitted";
} else {
    echo "already_submitted";
}

$stmt->close();
$conn->close();
?>
