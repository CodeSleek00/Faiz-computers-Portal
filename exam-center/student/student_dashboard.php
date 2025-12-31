<?php
include '../../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    header("Location: ../../login-system/login.php");
    exit;
}

// Fetch student from students first
$student = $conn->query("SELECT 'students' as student_table, student_id, name FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();

// If not found, try students26
if (!$student) {
    $student = $conn->query("SELECT 'students26' as student_table, id as student_id, name FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
}

if (!$student) die("Student not found.");

$student_id = $student['student_id'];
$student_table = $student['student_table'];

// Fetch all assigned exams
$assigned = $conn->query("
    SELECT DISTINCT e.*
    FROM exams e
    JOIN exam_assignments ea ON e.exam_id = ea.exam_id
    LEFT JOIN student_batches sb ON ea.batch_id = sb.batch_id
    WHERE (ea.student_id = $student_id AND ea.student_table = '$student_table')
       OR (sb.student_id = $student_id AND sb.student_table = '$student_table')
    ORDER BY e.created_at DESC
");

// Count exams
$total_exams = $assigned->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams | Student Dashboard</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
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
            grid-template-columns: repeat(3, 1fr);
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
        .stat-icon.pending { background: var(--warning); }
        .stat-icon.completed { background: var(--success); }

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

        /* Exams Section */
        .exams-section {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header h2 i {
            color: var(--primary);
        }

        .exams-count {
            font-size: 12px;
            color: var(--gray);
            background: var(--light-gray);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .exams-list {
            padding: 20px;
        }

        .exam-item {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .exam-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .exam-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            flex: 1;
        }

        .exam-meta {
            font-size: 11px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 12px;
            white-space: nowrap;
        }

        .exam-body {
            margin-bottom: 16px;
        }

        .exam-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--gray);
        }

        .detail-item i {
            color: var(--primary);
            font-size: 10px;
        }

        .exam-status {
            margin-bottom: 16px;
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

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-completed {
            background: #d1fae5;
            color: var(--success);
        }

        .btn {
            display: inline-flex;
            align-items: center;
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
            justify-content: center;
        }

        .btn:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled,
        .btn.disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
        }

        /* No Exams */
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
            
            .exam-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
            
            .exam-meta {
                margin-left: 0;
            }
            
            .exam-details {
                grid-template-columns: 1fr;
                gap: 8px;
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
            
            .empty-state {
                padding: 30px 16px;
            }
            
            .detail-item {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="../../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <div class="welcome-section">
                <h1>Hi, <?= htmlspecialchars($student['name']) ?></h1>
                <p>Enrollment ID: <?= $enrollment_id ?> • Manage your exams</p>
            </div>
        </div>

        <?php
        // Count pending and completed exams
        $pending_count = 0;
        $completed_count = 0;
        
        $assigned->data_seek(0); // Reset pointer
        while ($exam = $assigned->fetch_assoc()) {
            $check = $conn->query("SELECT 1 FROM exam_submissions WHERE exam_id = {$exam['exam_id']} AND student_id = $student_id");
            if ($check->num_rows > 0) {
                $completed_count++;
            } else {
                $pending_count++;
            }
        }
        $assigned->data_seek(0); // Reset pointer again
        ?>

       

        <!-- Exams Section -->
        <div class="exams-section">
            <div class="section-header">
                <h2><i class="fas fa-pencil-alt"></i> Assigned Exams</h2>
                <div class="exams-count"><?= $total_exams ?> exams</div>
            </div>

            <div class="exams-list">
                <?php if ($assigned->num_rows > 0): ?>
                    <?php while ($exam = $assigned->fetch_assoc()):
                        $check = $conn->query("SELECT 1 FROM exam_submissions WHERE exam_id = {$exam['exam_id']} AND student_id = $student_id");
                        $already_submitted = $check->num_rows > 0;
                    ?>
                        <div class="exam-item">
                            <div class="exam-header">
                                <div class="exam-title"><?= htmlspecialchars($exam['exam_name']) ?></div>
                                <div class="exam-meta">
                                    <i class="far fa-calendar"></i>
                                    <?= date('M d, Y', strtotime($exam['created_at'])) ?>
                                </div>
                            </div>

                            <div class="exam-body">
                                <div class="exam-details">
                                    <div class="detail-item">
                                        <i class="fas fa-question-circle"></i>
                                        <span><?= $exam['total_questions'] ?> questions</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?= $exam['duration'] ?> minutes</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-star"></i>
                                        <span><?= $exam['marks_per_question'] ?> marks each</span>
                                    </div>
                                </div>

                                <div class="exam-status">
                                    <?php if ($already_submitted): ?>
                                        <span class="status-badge badge-completed">
                                            <i class="fas fa-check-circle"></i>
                                            Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge badge-pending">
                                            <i class="fas fa-clock"></i>
                                            Pending
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($already_submitted): ?>
                                    <a href="#" class="btn disabled" disabled>
                                        <i class="fas fa-check"></i>
                                        Already Submitted
                                    </a>
                                <?php else: ?>
                                    <a href="take_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="btn">
                                        <i class="fas fa-pen"></i>
                                        Start Exam
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No Exams Assigned</h3>
                        <p>No exams have been assigned to you yet.</p>
                        <a href="../../test.php" class="btn" style="width: auto; max-width: 200px;">
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