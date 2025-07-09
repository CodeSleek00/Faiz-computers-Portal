<?php
include 'database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch assignments
$assignments = $conn->query("
    SELECT a.*, s.submission_id, s.marks_awarded
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON s.assignment_id = a.assignment_id AND s.student_id = $student_id
    WHERE t.student_id = $student_id
       OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
    LIMIT 3
");
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
            background: #f7f9fc;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: #0043a4;
            color: white;
            padding: 40px 20px;
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
        .sidebar a:hover { text-decoration: underline; }

        /* Main */
        .main {
            flex: 1;
            padding: 40px;
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

        .welcome .text h2 {
            margin-bottom: 5px;
            font-size: 24px;
            color: #333;
        }

        .welcome .text p { color: #777; }

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

        .card h4 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            font-size: 14px;
            color: #555;
        }

        .card .status {
            margin-top: 10px;
            padding: 6px 12px;
            display: inline-block;
            border-radius: 8px;
            font-size: 12px;
        }

        .submitted { background: #d1ffe4; color: #0f9d58; }
        .not-submitted { background: #ffe1e1; color: #c62828; }

        .right-panel {
            width: 280px;
            padding: 40px 30px;
            background: #f2f6fc;
        }

        .calendar, .task-list {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .calendar h4, .task-list h4 { margin-bottom: 10px; }

        .task-list ul { padding-left: 20px; }
        .task-list li {
            font-size: 14px;
            margin: 6px 0;
        }

        @media(max-width: 1024px) {
            body { flex-direction: column; }
            .sidebar, .right-panel { width: 100%; }
            .main { padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <h2>E-School</h2>
            <a href="#">🏠 Dashboard</a>
            <a href="#">📘 Assignments</a>
            <a href="#">📚 Study Material</a>
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
                <h2>Hello, <?= htmlspecialchars($student['name']) ?></h2>
                <p>Enrollment: <?= $student['enrollment_id'] ?> | Course: <?= $student['course'] ?></p>
            </div>
            <div class="profile-pic">
                <img src="../../uploads/<?= $student['photo'] ?>" width="60" style="border-radius: 50%;">
            </div>
        </div>

        <div class="card-section">
            <h3>Your Assignments</h3>
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

    <div class="right-panel">
        <div class="calendar">
            <h4>📅 Calendar</h4>
            <p><?= date('F j, Y') ?></p>
        </div>
        <div class="task-list">
            <h4>📝 Your Tasks</h4>
            <ul>
                <li>Upload Assignment</li>
                <li>Study for Quiz</li>
                <li>Check Study Notes</li>
                <li>Complete Practice Exam</li>
            </ul>
        </div>
    </div>

</body>
</html>
