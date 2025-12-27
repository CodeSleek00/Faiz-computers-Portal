<?php
include '../../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

// Fetch student from students first
$student = $conn->query("SELECT 'students' as student_table, student_id, name FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();

// If not found, try students26
if (!$student) {
    $student = $conn->query("SELECT 'students26' as student_table, id as student_id, name FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
}

if (!$student) die("Student not found.");

$student_id = $student['student_id'];
$student_table = $student['student_table'];

// Fetch all assigned exams
$assigned = $conn->query("
    SELECT DISTINCT e.*
    FROM exams e
    JOIN exam_assignments ea ON e.exam_id = ea.exam_id
    LEFT JOIN student_batches sb ON ea.batch_id = sb.batch_id
    WHERE (ea.student_id = $student_id AND ea.student_table = '$student_table')
       OR (sb.student_id = $student_id AND sb.student_table = '$student_table')
    ORDER BY e.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Exams</title>
    <!-- head content omitted for brevity -->
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Hi, <?= htmlspecialchars($student['name']) ?> 👋</h2>
        <a class="back-btn" href="../../test.php"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <h3>Your Assigned Exams</h3>

    <?php if ($assigned->num_rows > 0): ?>
        <?php while ($exam = $assigned->fetch_assoc()):
            // Check if already submitted
            $check = $conn->query("SELECT 1 FROM exam_submissions WHERE exam_id = {$exam['exam_id']} AND student_id = $student_id");
            $already_submitted = $check->num_rows > 0;
        ?>
            <div class="exam-card">
                <div class="exam-title"><?= htmlspecialchars($exam['exam_name']) ?></div>
                <div class="exam-info">
                    Questions: <?= $exam['total_questions'] ?> | Duration: <?= $exam['duration'] ?> mins
                </div>

                <?php if ($already_submitted): ?>
                    <div class="taken-msg">
                        <i class="fas fa-check-circle"></i> You have already submitted this exam.
                    </div>
                <?php else: ?>
                    <a href="take_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="start-btn">
                        <i class="fas fa-pen"></i> Start Exam
                    </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No exams have been assigned yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
