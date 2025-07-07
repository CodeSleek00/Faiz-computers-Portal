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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .profile-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--primary-light);
        }

        .profile-info h2 {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .profile-info p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Assignments */
        .assignments {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .assignment-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light);
        }

        .assignment-title {
            font-weight: 600;
            color: var(--primary);
        }

        .assignment-marks {
            color: var(--gray);
            font-size: 14px;
        }

        .assignment-body {
            margin-bottom: 15px;
        }

        .assignment-question {
            color: var(--dark);
            margin-bottom: 15px;
        }

        .assignment-image {
            max-width: 100%;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .submitted {
            background: #e8f5e9;
            color: var(--success);
        }

        .not-submitted {
            background: #ffebee;
            color: var(--danger);
        }

        .graded {
            background: #e3f2fd;
            color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #3a56d4;
        }

        .btn-outline {
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
        }

        .no-assignments {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .no-assignments i {
            font-size: 40px;
            color: var(--gray);
            margin-bottom: 15px;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .assignment-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <img src="../uploads/<?= $student['photo'] ?>" alt="Student Photo" class="profile-img">
        <div class="profile-info">
            <h2><?= htmlspecialchars($student['name']) ?></h2>
            <p><?= $student['enrollment_id'] ?> </p>
        </div>
    </div>

    <!-- Assignments -->
    <?php if ($assignments->num_rows > 0) { ?>
        <div class="assignments">
            <?php while ($a = $assignments->fetch_assoc()) { ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <div class="assignment-title"><?= htmlspecialchars($a['title']) ?></div>
                        <div class="assignment-marks"><?= $a['marks'] ?> marks</div>
                    </div>
                    
                    <div class="assignment-body">
                        <?php if (!empty($a['question_text'])) { ?>
                            <div class="assignment-question"><?= nl2br(htmlspecialchars($a['question_text'])) ?></div>
                        <?php } ?>

                        <?php if (!empty($a['question_image'])) { ?>
                            <img src="../uploads/assignments/<?= $a['question_image'] ?>" class="assignment-image">
                        <?php } ?>

                        <?php if (!empty($a['submission_id'])) { ?>
                            <div class="status submitted">
                                <i class="fas fa-check-circle"></i>
                                Submitted on <?= date('M d, Y', strtotime($a['submitted_at'])) ?>
                            </div>
                            
                            <?php if (!is_null($a['marks_awarded'])) { ?>
                                <div class="status graded">
                                    <i class="fas fa-star"></i>
                                    Grade: <?= $a['marks_awarded'] ?>/<?= $a['marks'] ?>
                                </div>
                            <?php } ?>
                            
                        <?php } else { ?>
                            <div class="status not-submitted">
                                <i class="fas fa-exclamation-circle"></i>
                                Not Submitted
                            </div>
                            <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Assignment
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="no-assignments">
            <i class="fas fa-book-open"></i>
            <h3>No Assignments Found</h3>
            <p>You don't have any assignments at this time.</p>
        </div>
    <?php } ?>
</div>

</body>
</html>