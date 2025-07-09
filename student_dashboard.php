<?php
include 'database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];

// Assignments (latest 3)
$assignments = $conn->query("
    SELECT a.*, s.submission_id
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON s.assignment_id = a.assignment_id AND s.student_id = $student_id
    WHERE t.student_id = $student_id OR t.batch_id IN (
        SELECT batch_id FROM student_batches WHERE student_id = $student_id
    )
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
    LIMIT 3
");

// Check if exams exist
$exams = $conn->query("
    SELECT e.*
    FROM exams e
    JOIN exam_assignments ea ON e.exam_id = ea.exam_id
    WHERE ea.student_id = $student_id OR ea.batch_id IN (
        SELECT batch_id FROM student_batches WHERE student_id = $student_id
    )
");
$has_exams = $exams->num_rows > 0;

// Check if study materials assigned
$materials = $conn->query("
    SELECT *
    FROM study_material
    WHERE (student_id = $student_id OR batch_id IN (
        SELECT batch_id FROM student_batches WHERE student_id = $student_id
    )) OR (student_id IS NULL AND batch_id IS NULL)
");

$has_notes = $materials->num_rows > 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f8fd;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 230px;
            background: #0043a4;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar h2 { font-size: 24px; margin-bottom: 30px; }
        .sidebar a {
            color: white;
            text-decoration: none;
            margin: 15px 0;
            display: block;
            font-weight: 500;
        }
        .main {
            flex: 1;
            padding: 30px;
        }

        .welcome {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.06);
        }

        .text h2 { margin-bottom: 5px; font-size: 24px; color: #333; }
        .text p { color: #777; }

        .card-section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            flex: 1 1 250px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.06);
        }

        .card h4 { font-size: 16px; margin-bottom: 10px; color: #333; }
        .card p { font-size: 14px; color: #555; }

        .card .status {
            margin-top: 10px;
            padding: 6px 12px;
            display: inline-block;
            border-radius: 8px;
            font-size: 13px;
        }

        .submitted { background: #d1ffe4; color: #0f9d58; }
        .not-submitted { background: #ffe1e1; color: #c62828; }

        .smart-info {
            display: flex;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .info-box {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }

        .info-box h4 { color: #333; margin-bottom: 10px; }
        .info-box span {
            font-size: 30px;
            display: block;
            margin-top: 10px;
        }

        .info-box .yes { color: #0f9d58; }
        .info-box .no { color: #dc3545; }

        @media(max-width: 1024px) {
            body { flex-direction: column; }
            .sidebar, .main { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>📚 E-School</h2>
            <a href="#">🏠 Dashboard</a>
            <a href="#">📘 Assignments</a>
            <a href="#">📖 Study Center</a>
            <a href="#">📝 Exams</a>
            <a href="#">📊 Results</a>
        </div>
        <div>
            <p>Semester 2 of 3</p>
        </div>
    </div>

    <div class="main">
        <div class="welcome">
            <div class="text">
                <h2>Welcome, <?= htmlspecialchars($student['name']) ?></h2>
                <p>Enrollment: <?= $student['enrollment_id'] ?> | Course: <?= $student['course'] ?></p>
            </div>
            <div class="profile">
                <img src="uploads/<?= $student['photo'] ?>" width="60" style="border-radius: 50%;">
            </div>
        </div>

        <!-- Smart Info Cards -->
        <div class="smart-info">
            <div class="info-box">
                <h4>📘 Assignments</h4>
                <span class="<?= $assignments->num_rows > 0 ? 'yes' : 'no' ?>">
                    <?= $assignments->num_rows > 0 ? '✅ Available' : '❌ None' ?>
                </span>
            </div>
            <div class="info-box">
                <h4>📝 Exams</h4>
                <span class="<?= $has_exams ? 'yes' : 'no' ?>">
                    <?= $has_exams ? '✅ Assigned' : '❌ Not Assigned' ?>
                </span>
            </div>
            <div class="info-box">
                <h4>📚 Notes</h4>
                <span class="<?= $has_notes ? 'yes' : 'no' ?>">
                    <?= $has_notes ? '✅ Available' : '❌ None' ?>
                </span>
            </div>
        </div>

        <!-- Assignment Cards -->
        <div class="card-section">
            <h3>Your Recent Assignments</h3>
            <div class="cards">
                <?php while($a = $assignments->fetch_assoc()) { ?>
                    <div class="card">
                        <h4><?= htmlspecialchars($a['title']) ?></h4>
                        <p><?= htmlspecialchars(substr($a['question_text'], 0, 50)) ?>...</p>
                        <?php if ($a['submission_id']) { ?>
                            <div class="status submitted">✅ Submitted</div>
                        <?php } else { ?>
                            <div class="status not-submitted">❌ Not Submitted</div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

</body>
</html>
