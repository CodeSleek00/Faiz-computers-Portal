<?php
include '../../database_connection/db_connect.php';
session_start();

$student_id = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '{$_SESSION['enrollment_id']}'")->fetch_assoc()['student_id'];
$exam_id = $_POST['exam_id'];
$answers = $_POST['answers'];

$correct_q = $conn->query("SELECT question_id, correct_option FROM exam_questions WHERE exam_id = $exam_id");
$correct_map = [];
while ($row = $correct_q->fetch_assoc()) {
    $correct_map[$row['question_id']] = $row['correct_option'];
}

$score = 0;
foreach ($answers as $qid => $ans) {
    if (isset($correct_map[$qid]) && $correct_map[$qid] == $ans) {
        $score++;
    }
}

$conn->query("INSERT INTO exam_submissions (exam_id, student_id, score, submitted_at) VALUES ($exam_id, $student_id, $score, NOW())");

header("Location: exam_result_student.php?exam_id=$exam_id");
exit;
?>
