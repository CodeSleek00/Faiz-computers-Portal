<?php
include '../database_connection/db_connect.php';

$exam_id = $_SESSION['exam_id'] ?? 0;
$student_id = $_SESSION['student_id'] ?? 0;

if(!$exam_id || !$student_id) {
    die("Invalid request");
}

// Calculate time taken
$start_time = $_SESSION['exam_start_time'];
$end_time = date('Y-m-d H:i:s');
$time_taken = strtotime($end_time) - strtotime($start_time);

// Collect answers
$answers = [];
$questions = $conn->query("SELECT question_id FROM exam_questions WHERE exam_id=$exam_id");
while($q = $questions->fetch_assoc()) {
    $qid = $q['question_id'];
    $answers[$qid] = $_POST["q$qid"] ?? null;
}

// Calculate score
$score = 0;
$questions = $conn->query("SELECT * FROM exam_questions WHERE exam_id=$exam_id");
while($q = $questions->fetch_assoc()) {
    if($answers[$q['question_id']] == $q['correct_option']) {
        $score++;
    }
}

// Store submission
$answers_json = json_encode($answers);
$sql = "INSERT INTO exam_submissions 
        (exam_id, student_id, answers, score, start_time, end_time, time_taken) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisisii", $exam_id, $student_id, $answers_json, $score, $start_time, $end_time, $time_taken);
$stmt->execute();

// Clear session
unset($_SESSION['exam_start_time']);
unset($_SESSION['exam_id']);

// Redirect to result page if available
if($conn->query("SELECT is_result_declared FROM exams WHERE exam_id=$exam_id")->fetch_assoc()['is_result_declared']) {
    header("Location: exam_result.php?exam_id=$exam_id");
} else {
    header("Location: student_dashboard.php?message=Exam submitted successfully");
}
exit();