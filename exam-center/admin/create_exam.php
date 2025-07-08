<?php
include '../../database_connection/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = $_POST['exam_name'];
    $total_questions = $_POST['total_questions'];
    $duration = $_POST['duration'];
    $marks = $_POST['marks'];

    $stmt = $conn->prepare("INSERT INTO exams (exam_name, total_questions, duration, marks_per_question) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $exam_name, $total_questions, $duration, $marks);
    $stmt->execute();

    $exam_id = $stmt->insert_id;
    header("Location: add_question.php?exam_id=$exam_id&total=$total_questions");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Exam</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f0f0f0; }
        .form-box {
            max-width: 600px; margin: auto; background: white;
            padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        input, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 6px; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Create Exam</h2>
    <form method="POST">
        <input type="text" name="exam_name" placeholder="Exam Name" required>
        <input type="number" name="total_questions" placeholder="Total Questions" required>
        <input type="number" name="marks" placeholder="Marks per Question" required>
        <input type="number" name="duration" placeholder="Duration (in minutes)" required>
        <button type="submit">Next</button>
    </form>
</div>
</body>
</html>
