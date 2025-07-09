<?php
include 'database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    header("Location: login-system/login.php");
    exit;
}

// Fetch student info
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch assignments assigned to this student or their batch
$batch_ids = $conn->query("SELECT batch_id FROM batch_students WHERE student_id = $student_id");
$batch_ids_array = [];
while ($row = $batch_ids->fetch_assoc()) {
    $batch_ids_array[] = $row['batch_id'];
}
$batch_ids_list = implode(',', $batch_ids_array);

$assignments = $conn->query("
    SELECT a.*, 
           (SELECT status FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = $student_id LIMIT 1) AS submission_status,
           (SELECT marks_obtained FROM assignment_submissions WHERE assignment_id = a.assignment_id AND student_id = $student_id LIMIT 1) AS marks
    FROM assignments a
    WHERE 
        a.assigned_to = 'all' 
        OR a.assignment_id IN (SELECT assignment_id FROM assignment_targets WHERE student_id = $student_id)
        OR a.assignment_id IN (SELECT assignment_id FROM assignment_targets WHERE batch_id IN ($batch_ids_list))
    ORDER BY a.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Assignments</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            color: #333;
        }
        header {
            background-color: #2e86de;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            padding: 30px;
        }
        .assignment-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.06);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .assignment-title {
            font-size: 18px;
            font-weight: 600;
        }
        .assignment-desc {
            margin: 10px 0;
            color: #555;
        }
        .assignment-meta {
            font-size: 14px;
            color: #777;
        }
        .status {
            margin-top: 10px;
            font-weight: 500;
        }
        .status.pending {
            color: #e67e22;
        }
        .status.submitted {
            color: #27ae60;
        }
        .status.graded {
            color: #2c3e50;
        }
        .actions a {
            background: #2e86de;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }
        .actions a:hover {
            background: #1b4f91;
        }
        .profile {
            font-size: 16px;
        }
    </style>
</head>
<body>

<header>
    <div><strong>Welcome,</strong> <?= htmlspecialchars($student['name']) ?></div>
    <div class="profile">
        <i class="fas fa-id-badge"></i> <?= $student['enrollment_id'] ?> | 
        <a href="login-system/logout.php" style="color: #fff; text-decoration: underline;">Logout</a>
    </div>
</header>

<div class="container">
    <h2>Your Assignments</h2>

    <?php if ($assignments->num_rows > 0): ?>
        <?php while($row = $assignments->fetch_assoc()): ?>
            <div class="assignment-card">
                <div class="assignment-header">
                    <div class="assignment-title"><?= htmlspecialchars($row['title']) ?></div>
                    <div class="actions">
                        <a href="submit_assignment.php?assignment_id=<?= $row['assignment_id'] ?>">
                            <?= $row['submission_status'] ? 'View/Resubmit' : 'Submit Now' ?>
                        </a>
                    </div>
                </div>
                <div class="assignment-desc"><?= htmlspecialchars($row['description']) ?></div>
                <div class="assignment-meta">
                    <i class="fas fa-calendar"></i> Posted on: <?= date('d M Y', strtotime($row['created_at'])) ?>
                    | <i class="fas fa-pen"></i> Marks: <?= $row['total_marks'] ?>
                </div>
                <div class="status <?= $row['submission_status'] ? ($row['marks'] !== null ? 'graded' : 'submitted') : 'pending' ?>">
                    Status: 
                    <?= !$row['submission_status'] ? 'Pending' : ($row['marks'] !== null ? 'Graded (' . $row['marks'] . '/' . $row['total_marks'] . ')' : 'Submitted') ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No assignments assigned to you yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
