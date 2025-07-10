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
    <title>My Exams</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --light: #f3f4f6;
            --white: #ffffff;
            --gray: #6b7280;
            --success: #16a34a;
            --radius: 12px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            background: var(--light);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .container {
            max-width: 950px;
            margin: auto;
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .back-btn {
            text-decoration: none;
            color: var(--primary);
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .back-btn i {
            font-size: 14px;
        }

        .exam-card {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.2s;
        }

        .exam-card:hover {
            background: #eef2ff;
        }

        .exam-title {
            font-weight: 600;
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .exam-info {
            color: var(--gray);
            margin-bottom: 15px;
            font-size: 14px;
        }

        .start-btn {
            display: inline-block;
            background: var(--primary);
            color: var(--white);
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }

        .start-btn:hover {
            background: #4338ca;
        }

        .taken-msg {
            color: var(--success);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 15px;
        }

        .taken-msg i {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .exam-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h2>Hi, <?= htmlspecialchars($student['name']) ?> 👋</h2>
        <a class="back-btn" href="../../test.php"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <h3>Your Assigned Exams</h3>

    <?php if ($assigned->num_rows > 0): ?>
        <?php while ($exam = $assigned->fetch_assoc()):
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
