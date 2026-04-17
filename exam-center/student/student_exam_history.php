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
        
        .exam-list {
            list-style: none;
            padding: 0;
        }
        
        .exam-item {
            background: rgba(67, 97, 238, 0.05);
            border: 1px solid rgba(67, 97, 238, 0.1);
            margin: 1rem 0;
            padding: 1.5rem;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .exam-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .exam-info h3 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .exam-info p {
            margin: 0.5rem 0;
            color: var(--dark-color);
        }
        
        .view-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.3s ease;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
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
        <h1>My Exam History</h1>
        <?php if ($submitted_exams->num_rows > 0): ?>
            <ul class="exam-list">
                <?php while ($exam = $submitted_exams->fetch_assoc()): ?>
                    <li class="exam-item">
                        <div class="exam-info">
                            <h3><?php echo htmlspecialchars($exam['exam_name']); ?></h3>
                            <p>Questions: <?php echo $exam['total_questions']; ?> | Score: <?php echo $exam['score']; ?>/<?php echo $exam['total_questions']; ?></p>
                        </div>
                        <div>
                            <a href="view_exam_questions.php?exam_id=<?php echo $exam['exam_id']; ?>" class="view-btn" style="margin-right: 10px;">View Questions</a>
                            <a href="student_view_answers.php?exam_id=<?php echo $exam['exam_id']; ?>" class="view-btn">My Answers</a>
                        </div>
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