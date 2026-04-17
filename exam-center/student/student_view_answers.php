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

// Fetch questions and student's answers
$questions = $conn->query("
    SELECT q.*, sa.selected_option, sa.is_correct
    FROM exam_questions q
    LEFT JOIN student_answers sa ON q.question_id = sa.question_id 
        AND sa.exam_id = $exam_id 
        AND sa.student_id = $student_id 
        AND sa.student_table = '$student_table'
    WHERE q.exam_id = $exam_id
    ORDER BY q.question_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exam['exam_name']); ?> - My Answers</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --error-color: #ff3333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-weight: 600;
        }
        
        .question {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid rgba(67, 97, 238, 0.1);
            border-radius: 10px;
            background: rgba(67, 97, 238, 0.02);
        }
        
        .question p {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        
        .options {
            margin-left: 1rem;
        }
        
        .option {
            margin: 0.5rem 0;
            padding: 0.5rem;
            border-radius: 6px;
            transition: background 0.3s ease;
        }
        
        .selected {
            border-left: 4px solid var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
        }
        
        .correct {
            background: rgba(75, 181, 67, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
            font-weight: 500;
        }
        
        .wrong {
            background: rgba(255, 51, 51, 0.1);
            border-left: 4px solid var(--error-color);
            color: var(--error-color);
            font-weight: 500;
        }
        
        .status {
            margin-top: 1rem;
            font-weight: 600;
        }
        
        .status.correct {
            color: var(--success-color);
        }
        
        .status.wrong {
            color: var(--error-color);
        }
        
        .back-btn {
            display: block;
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-btn a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-btn a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($exam['exam_name']); ?> - My Answers</h1>
        <?php while ($q = $questions->fetch_assoc()): ?>
            <div class="question">
                <p><?php echo htmlspecialchars($q['question']); ?></p>
                <div class="options">
                    <div class="option <?php if ($q['selected_option'] == 'A') echo 'selected'; ?> <?php if ($q['correct_option'] == 'A') echo 'correct'; else if ($q['selected_option'] == 'A' && $q['is_correct'] == 0) echo 'wrong'; ?>">
                        A) <?php echo htmlspecialchars($q['option_a']); ?> 
                        <?php if ($q['selected_option'] == 'A') echo '<i class="fas fa-hand-point-right"></i>'; ?>
                        <?php if ($q['correct_option'] == 'A') echo '<i class="fas fa-check"></i>'; ?>
                    </div>
                    <div class="option <?php if ($q['selected_option'] == 'B') echo 'selected'; ?> <?php if ($q['correct_option'] == 'B') echo 'correct'; else if ($q['selected_option'] == 'B' && $q['is_correct'] == 0) echo 'wrong'; ?>">
                        B) <?php echo htmlspecialchars($q['option_b']); ?> 
                        <?php if ($q['selected_option'] == 'B') echo '<i class="fas fa-hand-point-right"></i>'; ?>
                        <?php if ($q['correct_option'] == 'B') echo '<i class="fas fa-check"></i>'; ?>
                    </div>
                    <div class="option <?php if ($q['selected_option'] == 'C') echo 'selected'; ?> <?php if ($q['correct_option'] == 'C') echo 'correct'; else if ($q['selected_option'] == 'C' && $q['is_correct'] == 0) echo 'wrong'; ?>">
                        C) <?php echo htmlspecialchars($q['option_c']); ?> 
                        <?php if ($q['selected_option'] == 'C') echo '<i class="fas fa-hand-point-right"></i>'; ?>
                        <?php if ($q['correct_option'] == 'C') echo '<i class="fas fa-check"></i>'; ?>
                    </div>
                    <div class="option <?php if ($q['selected_option'] == 'D') echo 'selected'; ?> <?php if ($q['selected_option'] == 'D') echo 'correct'; else if ($q['selected_option'] == 'D' && $q['is_correct'] == 0) echo 'wrong'; ?>">
                        D) <?php echo htmlspecialchars($q['option_d']); ?> 
                        <?php if ($q['selected_option'] == 'D') echo '<i class="fas fa-hand-point-right"></i>'; ?>
                        <?php if ($q['correct_option'] == 'D') echo '<i class="fas fa-check"></i>'; ?>
                    </div>
                </div>
                <div class="status <?php echo $q['is_correct'] ? 'correct' : 'wrong'; ?>">
                    <?php if ($q['is_correct']): ?>
                        <i class="fas fa-check-circle"></i> Correct Answer
                    <?php else: ?>
                        <i class="fas fa-times-circle"></i> Wrong Answer (Correct: <?php echo $q['correct_option']; ?>)
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        <div class="back-btn">
            <a href="student_exam_history.php"><i class="fas fa-arrow-left"></i> Back to Exam History</a>
        </div>
    </div>
</body>
</html>