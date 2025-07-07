<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    die("❌ Please login to view your dashboard.");
}

// Get student ID using prepared statement
$student_query = $conn->prepare("SELECT student_id FROM students WHERE enrollment_id = ?");
$student_query->bind_param("s", $enrollment_id);
$student_query->execute();
$student_result = $student_query->get_result();

if ($student_result->num_rows == 0) {
    die("❌ Student not found.");
}
$student_id = $student_result->fetch_assoc()['student_id'];

// Get all assignments for this student (either directly assigned or through batches)
$assignments_query = $conn->query("
    SELECT DISTINCT a.*, 
           s.submission_id, 
           s.marks_awarded, 
           s.submitted_at,
           s.submitted_text,
           s.submitted_file
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $student_id
    WHERE (
        t.student_id = $student_id 
        OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    )
    ORDER BY a.due_date ASC, a.created_at DESC
");

$assignments = [];
while ($row = $assignments_query->fetch_assoc()) {
    $assignments[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #eef2f5;
            padding: 40px;
        }
        .container {
            max-width: 950px;
            margin: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .card {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #007bff;
        }
        .card h3 {
            margin-top: 0;
            color: #007bff;
        }
        .meta {
            color: #777;
            font-size: 14px;
            margin: 8px 0;
        }
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
        .submitted {
            background: #e0f8e9;
            color: #198754;
        }
        .not-submitted {
            background: #ffeaea;
            color: #c82333;
        }
        .overdue {
            background: #fff3cd;
            color: #856404;
        }
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
            transition: background 0.2s;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .no-data {
            text-align: center;
            color: #999;
            margin-top: 40px;
            padding: 20px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }
        .submission-details {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Assignments</h2>

    <?php if (!empty($assignments)) { ?>
        <?php foreach ($assignments as $a) { 
            $is_overdue = strtotime($a['due_date']) < time();
            ?>
            <div class="card">
                <h3><?= htmlspecialchars($a['title']) ?> (<?= $a['marks'] ?> marks)</h3>
                <div class="meta">
                    Due: <?= date('M d, Y', strtotime($a['due_date'])) ?>
                    <?php if ($is_overdue) { ?>
                        <span class="status overdue">⚠️ Overdue</span>
                    <?php } ?>
                </div>

                <?php if (!empty($a['question_text'])) { ?>
                    <p><?= nl2br(htmlspecialchars($a['question_text'])) ?></p>
                <?php } ?>

                <?php if (!empty($a['question_image'])) { ?>
                    <img src="../uploads/assignments/<?= htmlspecialchars($a['question_image']) ?>" class="img-preview">
                <?php } ?>

                <?php if (!empty($a['submission_id'])) { ?>
                    <div class="status submitted">✅ Submitted on <?= date('d M Y', strtotime($a['submitted_at'])) ?></div>
                    
                    <?php if (!empty($a['submitted_text']) || !empty($a['submitted_file'])) { ?>
                        <div class="submission-details">
                            <?php if (!empty($a['submitted_text'])) { ?>
                                <p><strong>Your answer:</strong> <?= nl2br(htmlspecialchars($a['submitted_text'])) ?></p>
                            <?php } ?>
                            <?php if (!empty($a['submitted_file'])) { ?>
                                <p><a href="../uploads/submissions/<?= htmlspecialchars($a['submitted_file']) ?>" target="_blank">📎 View submitted file</a></p>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    
                    <?php if (!is_null($a['marks_awarded'])) { ?>
                        <div class="marks">Scored: <?= $a['marks_awarded'] ?> / <?= $a['marks'] ?></div>
                    <?php } else { ?>
                        <div class="marks" style="color:#999;">(Waiting for grading...)</div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="status not-submitted">❌ Not Submitted <?= $is_overdue ? '(Late)' : '' ?></div>
                    <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="submit-btn">📤 Submit Now</a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="no-data">
            <p>🎉 No assignments assigned to you yet.</p>
            <p>Check back later or contact your instructor if you believe this is incorrect.</p>
        </div>
    <?php } ?>
</div>

</body>
</html>