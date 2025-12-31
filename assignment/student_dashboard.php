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
   2. FETCH STUDENT DATA
===================================================== */
$stmt = $conn->prepare("
    SELECT name, enrollment_id, photo
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
   3. FETCH ASSIGNMENTS
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

// Count statistics
$total_assignments = $assignments->num_rows;
$submitted_count = 0;
$graded_count = 0;
$assignments->data_seek(0);
while($a = $assignments->fetch_assoc()) {
    if (!empty($a['submission_id'])) {
        $submitted_count++;
        if (!is_null($a['marks_awarded'])) {
            $graded_count++;
        }
    }
}
$assignments->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="image.png">
    <title>Assignments | Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --border: #e5e7eb;
            --white: #ffffff;
            --radius: 8px;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.5;
            padding: 16px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 24px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--white);
            color: var(--primary);
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            border: 1px solid var(--border);
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: var(--light-gray);
            border-color: var(--primary);
        }

        .welcome-section h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .welcome-section p {
            color: var(--gray);
            font-size: 13px;
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--white);
            padding: 16px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 14px;
            color: var(--white);
        }

        .stat-icon.total { background: var(--primary); }
        .stat-icon.submitted { background: var(--success); }
        .stat-icon.graded { background: var(--warning); }
        .stat-icon.pending { background: var(--danger); }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Assignments */
        .assignments {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .assignments-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .assignments-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .assignments-header h2 i {
            color: var(--primary);
        }

        .assignments-count {
            font-size: 12px;
            color: var(--gray);
            background: var(--light-gray);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .assignment-list {
            padding: 20px;
        }

        .assignment-item {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .assignment-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .assignment-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            flex: 1;
        }

        .assignment-marks {
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            background: #eff6ff;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
            margin-left: 12px;
        }

        .assignment-body {
            margin-bottom: 16px;
        }

        .assignment-question {
            color: var(--gray);
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
            max-height: 60px;
            overflow: hidden;
            position: relative;
        }

        .assignment-question::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(to bottom, transparent, var(--white));
        }

        .assignment-image {
            width: 100%;
            max-width: 300px;
            border-radius: 6px;
            border: 1px solid var(--border);
            margin: 8px 0;
        }

        .assignment-status {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-submitted {
            background: #d1fae5;
            color: var(--success);
        }

        .badge-not-submitted {
            background: #fee2e2;
            color: var(--danger);
        }

        .badge-graded {
            background: #fef3c7;
            color: #92400e;
        }

        .submission-date {
            font-size: 11px;
            color: var(--gray);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* No Assignments */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 40px;
            color: var(--border);
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 13px;
            margin-bottom: 20px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .assignment-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
            
            .assignment-marks {
                margin-left: 0;
            }
            
            .header-top {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .assignment-question {
                font-size: 12px;
            }
            
            .empty-state {
                padding: 30px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <div class="welcome-section">
                <h1>Hi, <?= htmlspecialchars($student['name']) ?></h1>
                <p>Enrollment ID: <?= $student['enrollment_id'] ?> • Manage your assignments</p>
            </div>
        </div>

       
        <!-- Assignments Section -->
        <div class="assignments">
            <div class="assignments-header">
                <h2><i class="fas fa-file-alt"></i> Your Assignments</h2>
                <div class="assignments-count"><?= $total_assignments ?> assignments</div>
            </div>

            <div class="assignment-list">
                <?php if ($assignments->num_rows > 0): ?>
                    <?php while ($a = $assignments->fetch_assoc()): ?>
                        <div class="assignment-item">
                            <div class="assignment-header">
                                <div class="assignment-title"><?= htmlspecialchars($a['title']) ?></div>
                                <div class="assignment-marks"><?= $a['marks'] ?> marks</div>
                            </div>

                            <div class="assignment-body">
                                <?php if (!empty($a['question_text'])): ?>
                                    <div class="assignment-question">
                                        <?= nl2br(htmlspecialchars(substr($a['question_text'], 0, 150))) ?><?= strlen($a['question_text']) > 150 ? '...' : '' ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($a['question_image'])): ?>
                                    <img src="../uploads/assignments/<?= $a['question_image'] ?>" 
                                         class="assignment-image" 
                                         alt="Assignment">
                                <?php endif; ?>

                                <div class="assignment-status">
                                    <?php if (!empty($a['submission_id'])): ?>
                                        <span class="status-badge badge-submitted">
                                            <i class="fas fa-check"></i>
                                            Submitted
                                        </span>
                                        
                                        <?php if (!is_null($a['marks_awarded'])): ?>
                                            <span class="status-badge badge-graded">
                                                <i class="fas fa-star"></i>
                                                <?= $a['marks_awarded'] ?>/<?= $a['marks'] ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="submission-date">
                                            <?= date('M d, Y', strtotime($a['submitted_at'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge badge-not-submitted">
                                            <i class="fas fa-exclamation-circle"></i>
                                            Not Submitted
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (empty($a['submission_id'])): ?>
                                    <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="btn">
                                        <i class="fas fa-paper-plane"></i>
                                        Submit Assignment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <h3>No Assignments</h3>
                        <p>You don't have any assignments at this time.</p>
                        <a href="../test.php" class="btn" style="width: auto; max-width: 200px;">
                            <i class="fas fa-home"></i>
                            Return to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>