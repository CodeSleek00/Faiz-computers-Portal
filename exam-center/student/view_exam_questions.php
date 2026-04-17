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

$exam_id = intval($_GET['exam_id'] ?? 0);
if (!$exam_id) die("Invalid exam ID.");

// Check if student has submitted this exam
$submitted = $conn->query("SELECT 1 FROM exam_submissions WHERE exam_id = $exam_id AND student_id = $student_id AND student_table = '$student_table'")->num_rows;
if (!$submitted) die("You have not submitted this exam.");

// Fetch exam details
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
if (!$exam) die("Exam not found.");

// Fetch questions
$questions = $conn->query("SELECT * FROM exam_questions WHERE exam_id = $exam_id ORDER BY question_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['exam_name']); ?> - Questions</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .question { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .question p { font-weight: bold; margin-bottom: 10px; }
        .options { margin-left: 20px; }
        .option { margin: 5px 0; }
        .correct { color: green; font-weight: bold; }
        .back-btn { display: block; text-align: center; margin-top: 20px; }
        .back-btn a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($exam['exam_name']); ?> - Questions & Answers</h1>
        <?php while ($q = $questions->fetch_assoc()): ?>
            <div class="question">
                <p><?php echo htmlspecialchars($q['question']); ?></p>
                <div class="options">
                    <div class="option <?php if ($q['correct_option'] == 'A') echo 'correct'; ?>">A) <?php echo htmlspecialchars($q['option_a']); ?> <?php if ($q['correct_option'] == 'A') echo '<i class="fas fa-check"></i>'; ?></div>
                    <div class="option <?php if ($q['correct_option'] == 'B') echo 'correct'; ?>">B) <?php echo htmlspecialchars($q['option_b']); ?> <?php if ($q['correct_option'] == 'B') echo '<i class="fas fa-check"></i>'; ?></div>
                    <div class="option <?php if ($q['correct_option'] == 'C') echo 'correct'; ?>">C) <?php echo htmlspecialchars($q['option_c']); ?> <?php if ($q['correct_option'] == 'C') echo '<i class="fas fa-check"></i>'; ?></div>
                    <div class="option <?php if ($q['correct_option'] == 'D') echo 'correct'; ?>">D) <?php echo htmlspecialchars($q['option_d']); ?> <?php if ($q['correct_option'] == 'D') echo '<i class="fas fa-check"></i>'; ?></div>
                </div>
                <p><strong>Correct Answer: <?php echo $q['correct_option']; ?></strong></p>
            </div>
        <?php endwhile; ?>
        <div class="back-btn">
            <a href="student_exam_history.php"><i class="fas fa-arrow-left"></i> Back to Exam History</a>
        </div>
    </div>
</body>
</html>