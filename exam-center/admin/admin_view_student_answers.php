<?php
include '../../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);
$student_table = $_GET['student_table'] ?? '';

if (!$exam_id || !$student_id || !$student_table) die("Invalid parameters.");

// Fetch exam details
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
if (!$exam) die("Exam not found.");

// Fetch student details
$student = $conn->query("SELECT name, enrollment_id FROM $student_table WHERE " . ($student_table == 'students' ? 'student_id' : 'id') . " = $student_id")->fetch_assoc();
if (!$student) die("Student not found.");

// Fetch questions and student's answers
$questions = $conn->query("
    SELECT q.*, 
        (SELECT selected_option FROM student_answers sa WHERE sa.question_id = q.question_id AND sa.exam_id = $exam_id AND sa.student_id = $student_id AND sa.student_table = '$student_table') as selected_option,
        (SELECT is_correct FROM student_answers sa WHERE sa.question_id = q.question_id AND sa.exam_id = $exam_id AND sa.student_id = $student_id AND sa.student_table = '$student_table') as is_correct
    FROM exam_questions q
    WHERE q.exam_id = $exam_id
    ORDER BY q.question_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($student['name']); ?> - Answers for <?php echo htmlspecialchars($exam['exam_name']); ?></title>
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
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .student-info {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(67, 97, 238, 0.05);
            border-radius: 8px;
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
        
        .status.not-attempted {
            color: var(--dark-color);
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
        <h1><?php echo htmlspecialchars($exam['exam_name']); ?> - Student Answers</h1>
        <div class="student-info">
            <strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['enrollment_id']); ?>)
        </div>
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
                    <div class="option <?php if ($q['selected_option'] == 'D') echo 'selected'; ?> <?php if ($q['correct_option'] == 'D') echo 'correct'; else if ($q['selected_option'] == 'D' && $q['is_correct'] == 0) echo 'wrong'; ?>">
                        D) <?php echo htmlspecialchars($q['option_d']); ?> 
                        <?php if ($q['selected_option'] == 'D') echo '<i class="fas fa-hand-point-right"></i>'; ?>
                        <?php if ($q['correct_option'] == 'D') echo '<i class="fas fa-check"></i>'; ?>
                    </div>
                </div>
                <div class="status <?php echo $q['selected_option'] === null ? 'not-attempted' : ($q['is_correct'] ? 'correct' : 'wrong'); ?>">
                    <?php if ($q['selected_option'] === null): ?>
                        <i class="fas fa-question-circle"></i> Not Attempted
                    <?php elseif ($q['is_correct']): ?>
                        <i class="fas fa-check-circle"></i> Correct Answer
                    <?php else: ?>
                        <i class="fas fa-times-circle"></i> Wrong Answer (Correct: <?php echo $q['correct_option']; ?>)
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        <div class="back-btn">
            <a href="view_results_admin.php?exam_id=<?php echo $exam_id; ?>"><i class="fas fa-arrow-left"></i> Back to Results</a>
        </div>
    </div>
</body>
</html>