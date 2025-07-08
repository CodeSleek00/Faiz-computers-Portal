<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch all assigned exams
$assigned = $conn->query("
    SELECT DISTINCT e.*
    FROM exams e
    JOIN exam_assignments ea ON e.exam_id = ea.exam_id
    WHERE ea.student_id = $student_id
       OR ea.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    ORDER BY e.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Exams</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f4f7f9; }
        .container {
            max-width: 950px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h2 { text-align: center; }
        .exam {
            padding: 20px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 10px;
            background: #fafafa;
        }
        .start-btn {
            background: #007bff; color: white; padding: 10px 20px;
            border-radius: 8px; text-decoration: none;
        }
        .taken {
            color: green; font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍🎓 Welcome, <?= htmlspecialchars($student['name']) ?>!</h2>
    <h3>Your Assigned Exams</h3>

    <?php while ($exam = $assigned->fetch_assoc()) {
        // Check if student already submitted
        $check = $conn->query("SELECT 1 FROM exam_submissions WHERE exam_id = {$exam['exam_id']} AND student_id = $student_id");
        $already_submitted = $check->num_rows > 0;
        ?>
        <div class="exam">
            <strong><?= htmlspecialchars($exam['exam_name']) ?></strong><br>
            Questions: <?= $exam['total_questions'] ?> |
            Duration: <?= $exam['duration'] ?> mins
            <br><br>
            <?php if ($already_submitted): ?>
                <span class="taken">✅ You have already submitted this exam.</span>
            <?php else: ?>
                <a href="take_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="start-btn">Start Exam</a>
            <?php endif; ?>
        </div>
    <?php } ?>
</div>
</body>
</html>
