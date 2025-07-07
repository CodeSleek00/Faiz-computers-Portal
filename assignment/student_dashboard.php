<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    die("Please login to view your dashboard.");
}

// Get student ID
$student_query = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment_id'");
if ($student_query->num_rows == 0) {
    die("Student not found.");
}
$student = $student_query->fetch_assoc();
$student_id = $student['student_id'];

// Get relevant assignments using fixed LEFT JOIN logic
$assignment_sql = "
    SELECT a.*, s.submission_id, s.marks_awarded, s.submitted_at
    FROM assignments a
    LEFT JOIN assignment_targets t 
        ON a.assignment_id = t.assignment_id 
        AND (t.student_id = $student_id 
             OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id))
    LEFT JOIN assignment_submissions s 
        ON a.assignment_id = s.assignment_id AND s.student_id = $student_id
    WHERE t.assignment_id IS NOT NULL
    ORDER BY a.created_at DESC
";
$assignments = $conn->query($assignment_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef2f5; padding: 40px; }
        .container { max-width: 950px; margin: auto; }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .card {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }
        .card h3 { margin-top: 0; color: #007bff; }
        .meta { color: #777; font-size: 14px; }
        .img-preview {
            margin-top: 10px;
            max-width: 300px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .status {
            margin-top: 10px;
            padding: 8px 12px;
            display: inline-block;
            border-radius: 8px;
            font-size: 14px;
        }
        .submitted { background: #e0f8e9; color: #198754; }
        .not-submitted { background: #ffeaea; color: #c82333; }
        .marks {
            font-weight: bold;
            color: #0056b3;
            margin-top: 10px;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .no-data {
            text-align: center;
            color: #999;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Assignments</h2>

    <?php if ($assignments->num_rows > 0) { ?>
        <?php while ($a = $assignments->fetch_assoc()) { ?>
            <div class="card">
                <h3><?= htmlspecialchars($a['title']) ?> (<?= $a['marks'] ?> marks)</h3>

                <?php if (!empty($a['question_text'])) { ?>
                    <p><?= nl2br(htmlspecialchars($a['question_text'])) ?></p>
                <?php } ?>

                <?php if (!empty($a['question_image'])) { ?>
                    <img src="../uploads/assignments/<?= $a['question_image'] ?>" class="img-preview">
                <?php } ?>

                <?php if (!empty($a['submission_id'])) { ?>
                    <div class="status submitted">✅ Submitted on <?= date('d M Y', strtotime($a['submitted_at'])) ?></div>
                    <?php if (!is_null($a['marks_awarded'])) { ?>
                        <div class="marks">Scored: <?= $a['marks_awarded'] ?> / <?= $a['marks'] ?></div>
                    <?php } else { ?>
                        <div class="marks" style="color:#999;">(Waiting for grading...)</div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="status not-submitted">❌ Not Submitted</div>
                    <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="submit-btn">📤 Submit Now</a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="no-data">🎉 No assignments assigned to you yet.</div>
    <?php } ?>
</div>

</body>
</html>
