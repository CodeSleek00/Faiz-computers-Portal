<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    die("Please login to view your dashboard.");
}

// Get student info
$student_query = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'");
if ($student_query->num_rows == 0) {
    die("Student not found.");
}
$student = $student_query->fetch_assoc();
$student_id = $student['student_id'];

// Fetch relevant assignments
$assignment_sql = "
    SELECT a.*, s.submission_id, s.marks_awarded, s.submitted_at
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $student_id
    WHERE t.student_id = $student_id
       OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
";
$assignments = $conn->query($assignment_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6fa;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 960px;
            margin: auto;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
        }

        .header img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ccc;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            color: #333;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .card h3 {
            margin-top: 0;
            color: #007bff;
        }

        .img-preview {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .status {
            margin-top: 10px;
            padding: 6px 12px;
            display: inline-block;
            border-radius: 6px;
            font-size: 14px;
        }

        .submitted {
            background: #e1f7ea;
            color: #198754;
        }

        .not-submitted {
            background: #ffeaea;
            color: #c82333;
        }

        .marks {
            margin-top: 10px;
            font-weight: bold;
            color: #0056b3;
        }

        .submit-btn {
            margin-top: 12px;
            display: inline-block;
            padding: 8px 14px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #0056b3;
        }

        .no-data {
            text-align: center;
            color: #999;
            font-size: 16px;
            margin-top: 40px;
        }

        @media screen and (max-width: 600px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header img {
                width: 50px;
                height: 50px;
            }

            .card {
                padding: 15px;
            }

            .submit-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <img src="../uploads/<?= $student['photo'] ?>" alt="Student Photo">
        <h2>Hello, <?= htmlspecialchars($student['name']) ?> 👋</h2>
    </div>

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
                        <div class="marks" style="color: #999;">(Waiting for grading...)</div>
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
