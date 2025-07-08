<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'];
$student_id = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc()['student_id'];

$exam_id = $_GET['exam_id'];
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
$result = $conn->query("SELECT * FROM exam_submissions WHERE exam_id = $exam_id AND student_id = $student_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Exam Result</title>
    <style>
        body { font-family: Arial; background: #eef2f5; padding: 50px; text-align: center; }
        .box {
            background: white; padding: 40px; border-radius: 12px;
            max-width: 500px; margin: auto;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="box">
        <h2><?= $exam['exam_name'] ?></h2>
        <?php if ($exam['result_declared']) { ?>
            <p><strong>Your Score:</strong> <?= $result['score'] ?> / <?= $exam['total_questions'] ?></p>
        <?php } else { ?>
            <p><strong>Result not declared yet!</strong></p>
        <?php } ?>
    </div>
</body>
</html>
