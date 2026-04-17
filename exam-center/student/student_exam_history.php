<?php
include '../../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    header("Location: ../../login-system/login.php");
    exit;
}

// Fetch student
$student = $conn->query("SELECT 'students' as student_table, student_id, name FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
if (!$student) {
    $student = $conn->query("SELECT 'students26' as student_table, id as student_id, name FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
}
if (!$student) die("Student not found.");

$student_id = $student['student_id'];
$student_table = $student['student_table'];

// Fetch submitted exams
$submitted_exams = $conn->query("
    SELECT e.exam_id, e.exam_name, e.total_questions, s.score, s.submitted_at
    FROM exam_submissions s
    JOIN exams e ON s.exam_id = e.exam_id
    WHERE s.student_id = $student_id AND s.student_table = '$student_table'
    ORDER BY s.submitted_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Exam History</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .exam-list { list-style: none; padding: 0; }
        .exam-item { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .exam-info h3 { margin: 0; color: #007bff; }
        .exam-info p { margin: 5px 0; color: #666; }
        .view-btn { background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .view-btn:hover { background: #218838; }
        .back-btn { display: block; text-align: center; margin-top: 20px; }
        .back-btn a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Exam History</h1>
        <?php if ($submitted_exams->num_rows > 0): ?>
            <ul class="exam-list">
                <?php while ($exam = $submitted_exams->fetch_assoc()): ?>
                    <li class="exam-item">
                        <div class="exam-info">
                            <h3><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                            <p>Questions: <?php echo $exam['total_questions']; ?> | Score: <?php echo $exam['score']; ?>/<?php echo $exam['total_questions']; ?></p>
                            <p>Submitted: <?php echo date('d M Y, H:i', strtotime($exam['submitted_at'])); ?></p>
                        </div>
                        <a href="view_exam_questions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="view-btn">View Questions</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You have not submitted any exams yet.</p>
        <?php endif; ?>
        <div class="back-btn">
            <a href="../test.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</body>
</html>