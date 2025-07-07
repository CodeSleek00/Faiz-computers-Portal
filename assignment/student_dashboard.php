<?php
include '../database_connection/db_connect.php';
session_start();

// Ensure student is logged in
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    die("⚠️ Please login to view your dashboard.");
}

// Get student ID
$student_result = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment_id'");
if ($student_result->num_rows === 0) {
    die("Student not found.");
}
$student = $student_result->fetch_assoc();
$student_id = $student['student_id'];

// ✅ FIXED QUERY: Get relevant assignments
$assignment_sql = "
    SELECT a.*, s.submission_id, s.marks_awarded, s.submitted_at
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $student_id
    WHERE t.student_id = $student_id 
       OR t.batch_id IN (
            SELECT batch_id FROM student_batches WHERE student_id = $student_id
       )
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
";
$assignments = $conn->query($assignment_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Assignments</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            padding: 40px;
        }
        .container {
            max-width: 960px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .assignment-card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.06);
        }
        .assignment-card h3 {
            margin: 0;
            font-size: 20px;
            color: #007bff;
        }
        .meta {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        .question-text {
            margin-top: 12px;
        }
        .img-preview {
            margin-top: 10px;
            max-width: 280px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .status {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 14px;
            font-size: 14px;
            border-radius: 8px;
        }
        .submitted {
            background: #d4edda;
            color: #155724;
        }
        .not-submitted {
            background: #f8d7da;
            color: #721c24;
        }
        .marks {
            font-weight: bold;
            color: #343a40;
            margin-top: 10px;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .no-assignments {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Assignments</h2>

    <?php if ($assignments->num_rows > 0) { ?>
        <?php while ($a = $assignments->fetch_assoc()) { ?>
            <div class="assignment-card">
                <h3><?= htmlspecialchars($a['title']) ?> (<?= $a['marks'] ?> marks)</h3>
                
                <?php if (!empty($a['question_text'])) { ?>
                    <p class="question-text"><?= nl2br(htmlspecialchars($a['question_text'])) ?></p>
                <?php } ?>
                
                <?php if (!empty($a['question_image'])) { ?>
                    <img src="../uploads/assignments/<?= htmlspecialchars($a['question_image']) ?>" class="img-preview">
                <?php } ?>

                <?php if (!empty($a['submission_id'])) { ?>
                    <div class="status submitted">✅ Submitted on <?= date('d M Y', strtotime($a['submitted_at'])) ?></div>
                    <?php if (!is_null($a['marks_awarded'])) { ?>
                        <div class="marks">🎯 Scored: <?= $a['marks_awarded'] ?> / <?= $a['marks'] ?></div>
                    <?php } else { ?>
                        <div class="marks" style="color: #888;">🕒 Waiting for grading...</div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="status not-submitted">❌ Not Submitted</div>
                    <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="submit-btn">📤 Submit Now</a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="no-assignments">🎉 No assignments assigned to you yet.</div>
    <?php } ?>
</div>

</body>
</html>
