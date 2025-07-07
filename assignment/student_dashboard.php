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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #4cc9f0;
            --success: #4bb543;
            --warning: #ffc107;
            --danger: #f44336;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Section */
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
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary);
        }

        .profile-info h2 {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .profile-info p {
            color: var(--gray);
            font-size: 15px;
        }

        .stats {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .stat-item {
            background: var(--light);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Assignment Cards */
        .assignment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 18px 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-weight: 600;
            font-size: 18px;
        }

        .card-marks {
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 14px;
        }

        .card-body {
            padding: 20px;
        }

        .question-text {
            color: var(--dark);
            margin-bottom: 15px;
            font-size: 15px;
        }

        .question-image {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .submitted {
            background: rgba(75, 181, 67, 0.1);
            color: var(--success);
        }

        .not-submitted {
            background: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .graded {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .due-date {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            width: 100%;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        .no-data {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .no-data i {
            font-size: 50px;
            color: var(--primary);
            margin-bottom: 15px;
            opacity: 0.7;
        }

        .no-data h3 {
            margin-bottom: 10px;
            color: var(--primary);
        }

        .no-data p {
            color: var(--gray);
            max-width: 400px;
            margin: 0 auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .stats {
                justify-content: center;
                flex-wrap: wrap;
            }

            .assignment-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .profile-img {
                width: 70px;
                height: 70px;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .card-marks {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Section -->
    <div class="header">
        <img src="../uploads/<?= $student['photo'] ?>" alt="Student Photo" class="profile-img">
        <div class="profile-info">
            <h2>Hello, <?= htmlspecialchars($student['name']) ?> 👋</h2>
            <p><?= $student['enrollment_id'] ?> </p>
            <div class="stats">
                <div class="stat-item">
                    <i class="fas fa-book"></i> Assignments
                </div>
                <div class="stat-item">
                    <i class="fas fa-graduation-cap"></i> Student
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Section -->
    <?php if ($assignments->num_rows > 0) { ?>
        <div class="assignment-grid">
            <?php while ($a = $assignments->fetch_assoc()) { ?>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= htmlspecialchars($a['title']) ?></div>
                        <div class="card-marks"><?= $a['marks'] ?> marks</div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($a['due_date'])) { ?>
                            <div class="due-date">
                                <i class="far fa-calendar-alt"></i>
                                Due: <?= date('d M Y', strtotime($a['due_date'])) ?>
                            </div>
                        <?php } ?>

                        <?php if (!empty($a['question_text'])) { ?>
                            <div class="question-text"><?= nl2br(htmlspecialchars($a['question_text'])) ?></div>
                        <?php } ?>

                        <?php if (!empty($a['question_image'])) { ?>
                            <img src="../uploads/assignments/<?= $a['question_image'] ?>" class="question-image">
                        <?php } ?>

                        <?php if (!empty($a['submission_id'])) { ?>
                            <div class="status-badge submitted">
                                <i class="fas fa-check-circle"></i>
                                Submitted on <?= date('d M Y', strtotime($a['submitted_at'])) ?>
                            </div>
                            
                            <?php if (!is_null($a['marks_awarded'])) { ?>
                                <div class="status-badge graded">
                                    <i class="fas fa-star"></i>
                                    Scored: <?= $a['marks_awarded'] ?> / <?= $a['marks'] ?>
                                </div>
                            <?php } else { ?>
                                <div class="status-badge" style="background: rgba(255, 193, 7, 0.1); color: var(--warning);">
                                    <i class="fas fa-hourglass-half"></i>
                                    Waiting for grading
                                </div>
                            <?php } ?>
                            
                            <a href="view_submission.php?submission_id=<?= $a['submission_id'] ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i> View Submission
                            </a>
                        <?php } else { ?>
                            <div class="status-badge not-submitted">
                                <i class="fas fa-exclamation-circle"></i>
                                Not Submitted
                            </div>
                            <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Now
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="no-data">
            <i class="fas fa-book-open"></i>
            <h3>No Assignments Yet</h3>
            <p>You currently don't have any assignments. Check back later or contact your instructor if you believe this is an error.</p>
        </div>
    <?php } ?>
</div>

</body>
</html>