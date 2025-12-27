<?php 
session_start();
require_once '../database_connection/db_connect.php';

/* =====================================================
   1. LOGIN CHECK
===================================================== */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table']; // students OR students26
$student_id    = (int) $_SESSION['student_id'];

/* =====================================================
   2. FETCH STUDENT DATA (SAFE & DYNAMIC)
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        name,
        enrollment_id,
        photo
    FROM $table
    WHERE enrollment_id = ?
    LIMIT 1
");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    session_destroy();
    header("Location: ../login-system/login.php?error=student_not_found");
    exit;
}

$student = $res->fetch_assoc();

/* =====================================================
   3. FETCH ASSIGNMENTS (SAFE & DYNAMIC)
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        a.*, 
        s.submission_id, 
        s.marks_awarded, 
        s.submitted_at
    FROM assignments a
    INNER JOIN assignment_targets t 
        ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s 
        ON s.assignment_id = a.assignment_id 
        AND s.student_id = ?
    WHERE 
        t.student_id = ?
        OR t.batch_id IN (
            SELECT batch_id 
            FROM student_batches 
            WHERE student_id = ?
        )
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
");
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$assignments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --light-bg: #f5f7fb;
            --light: #ffffff;
            --gray: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --radius: 10px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: #222;
            margin: 0;
            padding: 15px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .header {
            background: var(--light);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .header .back-btn {
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        .header .greeting {
            font-size: 20px;
            font-weight: 600;
        }

        .header .enroll-id {
            font-size: 14px;
            color: var(--gray);
        }

        .assignments {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .assignment-card {
            background: var(--light);
            border-radius: var(--radius);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            padding: 20px;
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .assignment-title {
            font-weight: 600;
            color: var(--primary);
        }

        .assignment-body {
            margin-top: 10px;
        }

        .assignment-image {
            max-width: 100%;
            border-radius: var(--radius);
            margin: 10px 0;
            border: 1px solid #eee;
        }

        .assignment-question {
            margin-bottom: 10px;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 10px;
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
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--radius);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary);
            color: white;
        }

        .btn:hover {
            background: #3a56d4;
        }

        .no-assignments {
            background: white;
            padding: 40px 20px;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .no-assignments i {
            font-size: 40px;
            color: var(--gray);
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .assignment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .header {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <a href="../test.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="greeting">Hi, <?= htmlspecialchars($student['name']) ?> 👋</div>
        <div class="enroll-id">Enrollment ID: <?= $student['enrollment_id'] ?></div>
    </div>

    <!-- Assignments -->
    <?php if ($assignments->num_rows > 0): ?>
        <div class="assignments">
            <?php while ($a = $assignments->fetch_assoc()): ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <div class="assignment-title"><?= htmlspecialchars($a['title']) ?></div>
                        <div><?= $a['marks'] ?> marks</div>
                    </div>

                    <div class="assignment-body">
                        <?php if (!empty($a['question_text'])): ?>
                            <div class="assignment-question"><?= nl2br(htmlspecialchars($a['question_text'])) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($a['question_image'])): ?>
                            <img src="../uploads/assignments/<?= $a['question_image'] ?>" class="assignment-image">
                        <?php endif; ?>

                        <?php if (!empty($a['submission_id'])): ?>
                            <div class="status submitted">
                                <i class="fas fa-check-circle"></i>
                                Submitted on <?= date('M d, Y', strtotime($a['submitted_at'])) ?>
                            </div>

                            <?php if (!is_null($a['marks_awarded'])): ?>
                                <div class="status graded">
                                    <i class="fas fa-star"></i>
                                    Grade: <?= $a['marks_awarded'] ?>/<?= $a['marks'] ?>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="status not-submitted">
                                <i class="fas fa-exclamation-circle"></i>
                                Not Submitted
                            </div>
                            <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="btn">
                                <i class="fas fa-paper-plane"></i> Submit Assignment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-assignments">
            <i class="fas fa-book-open"></i>
            <h3>No Assignments Found</h3>
            <p>You don't have any assignments at this time.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
