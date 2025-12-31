<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    header("Location: ../../login-system/login.php");
    exit;
}

/* ===================== FETCH STUDENT (students / students26) ===================== */
$student = $conn->query("
    SELECT 'students' AS student_table, student_id, name
    FROM students 
    WHERE enrollment_id = '$enrollment_id'
")->fetch_assoc();

if (!$student) {
    $student = $conn->query("
        SELECT 'students26' AS student_table, id AS student_id, name
        FROM students26 
        WHERE enrollment_id = '$enrollment_id'
    ")->fetch_assoc();
}

if (!$student) die("Student not found.");

$student_id    = $student['student_id'];
$student_table = $student['student_table'];

/* ===================== FETCH DECLARED RESULTS ===================== */
$sql = "
    SELECT e.exam_name, e.total_questions, s.score, e.marks_per_question,
           e.created_at, s.submitted_at
    FROM exam_submissions s
    JOIN exams e ON s.exam_id = e.exam_id
    WHERE 
        s.student_id = $student_id
        AND s.student_table = '$student_table'
        AND e.result_declared = 1
    ORDER BY e.created_at DESC
";
$results = $conn->query($sql);
$total_results = $results->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results | Student Dashboard</title>
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
        .stat-icon.average { background: var(--success); }
        .stat-icon.score { background: var(--warning); }

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

        /* Results Section */
        .results-section {
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

        .results-count {
            font-size: 12px;
            color: var(--gray);
            background: var(--light-gray);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .results-list {
            padding: 20px;
        }

        .result-item {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .result-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .result-header {
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

        .exam-date {
            font-size: 11px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-left: 12px;
            white-space: nowrap;
        }

        .result-body {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 12px;
        }

        .result-metric {
            text-align: center;
        }

        .metric-label {
            font-size: 11px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--dark);
        }

        .metric-value.score {
            color: var(--primary);
        }

        .metric-value.total {
            color: var(--warning);
        }

        /* Performance Indicator */
        .performance-indicator {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .performance-excellent {
            background: #d1fae5;
            color: var(--success);
        }

        .performance-good {
            background: #fef3c7;
            color: #92400e;
        }

        .performance-average {
            background: #fee2e2;
            color: var(--danger);
        }

        /* No Results */
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
            
            .result-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
            
            .exam-date {
                margin-left: 0;
            }
            
            .result-body {
                grid-template-columns: 1fr;
                gap: 12px;
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
            
            .metric-value {
                font-size: 14px;
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
                <p>Enrollment ID: <?= $enrollment_id ?> • View your exam results</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-number"><?= $total_results ?></div>
                <div class="stat-label">Results Declared</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon average">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-number"><?= $total_results ?></div>
                <div class="stat-label">Exams Taken</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon score">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number"><?= $total_results ?></div>
                <div class="stat-label">Total Results</div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="section-header">
                <h2><i class="fas fa-chart-line"></i> Exam Results</h2>
                <div class="results-count"><?= $total_results ?> results</div>
            </div>

            <div class="results-list">
                <?php if ($results->num_rows > 0): ?>
                    <?php while ($row = $results->fetch_assoc()): 
                        $total_marks = $row['total_questions'] * $row['marks_per_question'];
                    ?>
                        <div class="result-item">
                            <div class="result-header">
                                <div class="exam-title"><?= htmlspecialchars($row['exam_name']) ?></div>
                                <div class="exam-date">
                                    <i class="far fa-calendar"></i>
                                    <?= date('M d, Y', strtotime($row['submitted_at'])) ?>
                                </div>
                            </div>

                            <div class="result-body">
                                <div class="result-metric">
                                    <div class="metric-label">Score</div>
                                    <div class="metric-value score">
                                        <?= $row['score'] ?> marks
                                    </div>
                                </div>
                                
                                <div class="result-metric">
                                    <div class="metric-label">Total Questions</div>
                                    <div class="metric-value total">
                                        <?= $row['total_questions'] ?>
                                    </div>
                                </div>
                                
                                <div class="result-metric">
                                    <div class="metric-label">Marks per Question</div>
                                    <div class="metric-value">
                                        <?= $row['marks_per_question'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h3>No Results Declared</h3>
                        <p>Your exam results will appear here once they are declared.</p>
                        <a href="../../test.php" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius); font-size: 13px; font-weight: 500;">
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