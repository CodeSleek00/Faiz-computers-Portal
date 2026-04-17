<?php
include '../../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

// Fetch student from either table
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_table = 'students';

if (!$student) {
    $student = $conn->query("SELECT * FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
    $student_table = 'students26';
}

if (!$student) die("Student not found.");

// Get student_id correctly
$student_id = $student['student_id'] ?? $student['id']; // students26 may have `id`

// Get exam and answers
$exam_id = intval($_POST['exam_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

// Better error handling
if (!$exam_id) {
    die("Error: Exam ID not received. Please try again.");
}

if (empty($answers)) {
    die("Error: You haven't answered any questions. Please answer at least one question before submitting.");
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

// Create student_answers table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS student_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    student_table VARCHAR(20) NOT NULL,
    question_id INT NOT NULL,
    selected_option VARCHAR(1) NOT NULL,
    is_correct TINYINT(1) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_answer (exam_id, student_id, student_table, question_id)
)");

// Insert submission with student_table
$stmt = $conn->prepare("
    INSERT INTO exam_submissions (exam_id, student_id, student_table, score, submitted_at) 
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iisi", $exam_id, $student_id, $student_table, $score);
$stmt->execute();

// Insert individual answers
$stmt_answer = $conn->prepare("
    INSERT INTO student_answers (exam_id, student_id, student_table, question_id, selected_option, is_correct)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE selected_option = VALUES(selected_option), is_correct = VALUES(is_correct)
");
foreach ($answers as $qid => $ans) {
    $is_correct = (isset($correct_map[$qid]) && $correct_map[$qid] == $ans) ? 1 : 0;
    $stmt_answer->bind_param("iisisi", $exam_id, $student_id, $student_table, $qid, $ans, $is_correct);
    $stmt_answer->execute();
}

header("Location: exam_result_student.php?exam_id=$exam_id");
exit;
?>
