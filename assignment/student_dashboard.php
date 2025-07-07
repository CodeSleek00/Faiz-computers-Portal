<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    header("Location: login.php");
    exit();
}

// Get student ID and batch info
$student_query = $conn->prepare("SELECT student_id FROM students WHERE enrollment_id = ?");
$student_query->bind_param("s", $enrollment_id);
$student_query->execute();
$student_result = $student_query->get_result();

if ($student_result->num_rows == 0) {
    die("Student not found.");
}
$student = $student_result->fetch_assoc();
$student_id = $student['student_id'];

// Get all batches the student belongs to
$batch_query = $conn->query("SELECT batch_id FROM student_batches WHERE student_id = $student_id");
$batch_ids = [];
while ($batch = $batch_query->fetch_assoc()) {
    $batch_ids[] = $batch['batch_id'];
}
$batch_condition = !empty($batch_ids) ? "OR t.batch_id IN (" . implode(',', $batch_ids) . ")" : "";

// Get relevant assignments with proper JOIN logic
$assignment_sql = "
    SELECT DISTINCT a.*, 
           s.submission_id, 
           s.marks_awarded, 
           s.submitted_at,
           s.submitted_text,
           s.submitted_file
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $student_id
    WHERE (t.student_id = $student_id $batch_condition)
    ORDER BY a.due_date ASC, a.created_at DESC
";
$assignments = $conn->query($assignment_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --primary-dark: #3730a3;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --bg: #f8fafc;
            --card-bg: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.5;
            padding: 2rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: 600;
        }

        .assignment-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .assignment-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid var(--primary);
        }

        .assignment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .assignment-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .assignment-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .assignment-meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .assignment-description {
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .assignment-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border);
        }

        .assignment-status {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-submitted {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-not-submitted {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .marks-awarded {
            font-weight: 600;
            color: var(--primary);
            margin-left: auto;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
            background: var(--card-bg);
            border-radius: 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        .submission-details {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--bg);
            border-radius: 0.5rem;
            border: 1px solid var(--border);
        }

        .submission-text {
            margin-bottom: 0.5rem;
        }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
        }

        .file-link:hover {
            text-decoration: underline;
        }

        .due-date-warning {
            color: var(--danger);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .assignment-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .assignment-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">
                <i class="fas fa-tasks"></i> My Assignments
            </h1>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($enrollment_id, 0, 2)) ?></div>
                <span><?= htmlspecialchars($enrollment_id) ?></span>
            </div>
        </div>

        <div class="assignment-grid">
            <?php if ($assignments->num_rows > 0): ?>
                <?php while ($a = $assignments->fetch_assoc()): ?>
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <div>
                                <h3 class="assignment-title"><?= htmlspecialchars($a['title']) ?></h3>
                                <div class="assignment-meta">
                                    <span class="assignment-meta-item">
                                        <i class="fas fa-star"></i> <?= $a['marks'] ?> Marks
                                    </span>
                                    <span class="assignment-meta-item">
                                        <i class="fas fa-calendar-alt"></i> 
                                        Due: <?= date('M d, Y', strtotime($a['due_date'])) ?>
                                        <?php if (strtotime($a['due_date']) < time()): ?>
                                            <span class="due-date-warning">(Overdue)</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($a['question_text'])): ?>
                            <div class="assignment-description">
                                <?= nl2br(htmlspecialchars($a['question_text'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($a['question_image'])): ?>
                            <img src="../uploads/assignments/<?= $a['question_image'] ?>" class="assignment-image">
                        <?php endif; ?>

                        <div class="assignment-status">
                            <?php if (!empty($a['submission_id'])): ?>
                                <span class="status-badge status-submitted">
                                    <i class="fas fa-check-circle"></i> Submitted on <?= date('M d, Y', strtotime($a['submitted_at'])) ?>
                                </span>
                                
                                <?php if (!is_null($a['marks_awarded'])): ?>
                                    <span class="marks-awarded">
                                        <?= $a['marks_awarded'] ?> / <?= $a['marks'] ?> Marks
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> Grading in progress
                                    </span>
                                <?php endif; ?>
                                
                                <div class="submission-details">
                                    <?php if (!empty($a['submitted_text'])): ?>
                                        <div class="submission-text">
                                            <strong>Your submission:</strong> <?= nl2br(htmlspecialchars($a['submitted_text'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($a['submitted_file'])): ?>
                                        <a href="../uploads/submissions/<?= $a['submitted_file'] ?>" target="_blank" class="file-link">
                                            <i class="fas fa-paperclip"></i> View submitted file
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="status-badge status-not-submitted">
                                    <i class="fas fa-exclamation-circle"></i> Not submitted
                                </span>
                                <a href="submit_assignment.php?assignment_id=<?= $a['assignment_id'] ?>" class="action-btn btn-primary">
                                    <i class="fas fa-upload"></i> Submit Assignment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-double"></i>
                    <h3>No assignments found</h3>
                    <p>You currently don't have any assigned work. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>