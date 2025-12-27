<?php
include '../../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

// Fetch student from either table
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
if (!$student) {
    $student = $conn->query("SELECT * FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
}
if (!$student) die("Student not found.");

$student_id = $student['student_id'] ?? $student['id']; // students26 may have `id`

// Get exam and answers
$exam_id = intval($_POST['exam_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

if (!$exam_id || empty($answers)) {
    die("Invalid submission.");
}

// Fetch correct answers
$correct_q = $conn->query("SELECT question_id, correct_option FROM exam_questions WHERE exam_id = $exam_id");
$correct_map = [];
while ($row = $correct_q->fetch_assoc()) {
    $correct_map[$row['question_id']] = $row['correct_option'];
}

// Calculate score
$score = 0;
foreach ($answers as $qid => $ans) {
    if (isset($correct_map[$qid]) && $correct_map[$qid] == $ans) {
        $score++;
    }
}

// Insert submission
$stmt = $conn->prepare("INSERT INTO exam_submissions (exam_id, student_id, score, submitted_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iii", $exam_id, $student_id, $score);
$stmt->execute();

header("Location: exam_result_student.php?exam_id=$exam_id");
exit;
?>
