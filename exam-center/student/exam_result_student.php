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

// Get exam_id
$exam_id = intval($_GET['exam_id'] ?? 0);
if (!$exam_id) die("Invalid exam.");

// Fetch exam
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
if (!$exam) die("Exam not found.");

// Fetch student's submission
$submission = $conn->query("SELECT * FROM exam_submissions WHERE exam_id = $exam_id AND student_id = $student_id")->fetch_assoc();
if (!$submission) die("Submission not found.");

// Total questions & marks
$total_questions = $exam['total_questions'];
$marks_per_question = $exam['marks_per_question'];
$total_marks = $total_questions * $marks_per_question;
$obtained_marks = $submission['score'] * $marks_per_question;
$percentage = ($obtained_marks / $total_marks) * 100;
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($exam['exam_name']) ?> - Result</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        h2 {
            color: #4f46e5;
            text-align: center;
            margin-bottom: 25px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 8px 0;
            font-size: 15px;
        }
        .score-box {
            background: #e0e7ff;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
        }
        .score-box h3 {
            margin: 10px 0;
            font-size: 20px;
            color: #1e40af;
        }
        .back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background: #4f46e5;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: #4338ca;
        }
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 Exam Result</h2>

    <div class="info">
        <p><strong>Student Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
        <p><strong>Enrollment ID:</strong> <?= htmlspecialchars($student['enrollment_id']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
        <p><strong>Exam:</strong> <?= htmlspecialchars($exam['exam_name']) ?></p>
        <p><strong>Total Questions:</strong> <?= $total_questions ?></p>
        <p><strong>Marks per Question:</strong> <?= $marks_per_question ?></p>
    </div>

    <div class="score-box">
        <h3>✅ Obtained Marks: <?= $obtained_marks ?> / <?= $total_marks ?></h3>
        <h3>📈 Percentage: <?= round($percentage, 2) ?>%</h3>
    </div>

    <a href="student_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>

</body>
</html>
