<?php
session_start();
require_once 'database_connection/db_connect.php';

/* =====================================================
   1. LOGIN CHECK
===================================================== */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: login-system/login.php");
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
    header("Location: login-system/login.php?error=student_not_found");
    exit;
}

$student = $res->fetch_assoc();

/* =====================================================
   3. COURSE / BATCH COUNT
===================================================== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_courses
    FROM student_batches
    WHERE student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$course_stats = $stmt->get_result()->fetch_assoc();

/* =====================================================
   4. ASSIGNMENT STATS
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT a.assignment_id) AS total_assignments,
        COUNT(DISTINCT s.submission_id) AS submitted_assignments
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
");
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$assignment_stats = $stmt->get_result()->fetch_assoc();

/* =====================================================
   5. RECENT ASSIGNMENTS (LAST 3)
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        a.assignment_id,
        a.title,
        a.question_text,
        a.created_at,
        s.submission_id,
        s.marks_awarded
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
    LIMIT 3
");
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$assignments = $stmt->get_result();

/* =====================================================
   6. ATTENDANCE STATS
===================================================== */
$stmt = $conn->prepare("
    SELECT 
        SUM(status = 'Present') AS present_days,
        SUM(status = 'Absent') AS absent_days,
        SUM(status = 'Leave') AS leave_days
    FROM attendance
    WHERE student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attendance = $stmt->get_result()->fetch_assoc();

$present = $attendance['present_days'] ?? 0;
$absent  = $attendance['absent_days'] ?? 0;
$leave   = $attendance['leave_days'] ?? 0;

/* =====================================================
   7. STUDY MATERIALS (LAST 5 ASSIGNED TO STUDENT)
===================================================== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_materials
    FROM study_material_targets
    WHERE student_id = ?
      AND student_table = ?
");
$stmt->bind_param("is", $student_id, $table);
$stmt->execute();
$total_materials = $stmt->get_result()->fetch_assoc()['total_materials'] ?? 0;

$stmt = $conn->prepare("
    SELECT sm.title
    FROM study_material_targets smt
    JOIN study_materials sm ON sm.id = smt.material_id
    WHERE smt.student_id = ?
      AND smt.student_table = ?
    ORDER BY smt.id DESC
    LIMIT 5
");
$stmt->bind_param("is", $student_id, $table);
$stmt->execute();
$last_materials = $stmt->get_result();

/* =====================================================
   8. FEE STATUS
===================================================== */
$currentMonthNo   = (int) date('n');
$currentMonthName = date('F');

$stmt = $conn->prepare("
    SELECT payment_status
    FROM student_monthly_fee
    WHERE enrollment_id = ?
      AND fee_type = 'Monthly'
      AND month_no = ?
    LIMIT 1
");
$stmt->bind_param("si", $enrollment_id, $currentMonthNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $feeStatus = $row['payment_status'];
} else {
    $feeStatus = 'Pending';
}

/* =====================================================
   9. EXAM PERFORMANCE
===================================================== */
$result = $conn->query("
    SELECT 
        e.exam_name,
        s.score,
        (e.total_questions * e.marks_per_question) AS total_marks
    FROM exam_submissions s
    JOIN exams e ON e.exam_id = s.exam_id
    WHERE s.student_id = $student_id
      AND s.student_table = '$table'
      AND e.result_declared = 1
    ORDER BY s.submitted_at ASC
    LIMIT 6
");

$labels = [];
$scores = [];

while ($r = $result->fetch_assoc()) {
    $labels[] = substr($r['exam_name'], 0, 15) . (strlen($r['exam_name']) > 15 ? '...' : '');
    $scores[] = round(($r['score'] / $r['total_marks']) * 100);
}

/* =====================================================
   10. ASSIGNED EXAMS
===================================================== */
$stmt = $conn->prepare("
    SELECT e.exam_id, e.exam_name, e.duration
    FROM exam_assignments ea
    JOIN exams e ON e.exam_id = ea.exam_id
    WHERE ea.student_id = ? AND ea.student_table = ?
    ORDER BY e.exam_id DESC
    LIMIT 3
");
$stmt->bind_param("is", $student_id, $table);
$stmt->execute();
$exams = $stmt->get_result();

/* =====================================================
   FALLBACK SAFETY (ZERO VALUES)
===================================================== */
$course_stats['total_courses']          = $course_stats['total_courses'] ?? 0;
$assignment_stats['total_assignments']  = $assignment_stats['total_assignments'] ?? 0;
$assignment_stats['submitted_assignments'] = $assignment_stats['submitted_assignments'] ?? 0;
$total_materials = $total_materials ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <title>Dashboard | Faiz Computer Institute</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007AFF;
            --primary-blue-light: #409CFF;
            --primary-blue-dark: #0056CC;
            --accent-blue: #5AC8FA;
            --success: #34C759;
            --warning: #FF9500;
            --danger: #FF3B30;
            --dark: #1D1D1F;
            --dark-gray: #8E8E93;
            --medium-gray: #C7C7CC;
            --light-gray: #F2F2F7;
            --card-gray: #F5F5F7;
            --white: #FFFFFF;
            --card-radius: 16px;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding: 24px 32px;
            background: var(--white);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }

        .welcome-section h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-section p {
            color: var(--dark-gray);
            font-size: 16px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .profile-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--white);
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.15);
        }

        .profile-info h3 {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .profile-info p {
            color: var(--dark-gray);
            font-size: 14px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--card-radius);
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-blue);
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-text h3 {
            font-size: 14px;
            font-weight: 500;
            color: var(--dark-gray);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        .stat-icon {
            font-size: 40px;
            color: var(--primary-blue);
            opacity: 0.8;
        }

        /* Main Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .card {
            background: var(--white);
            border-radius: var(--card-radius);
            padding: 24px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--light-gray);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: var(--primary-blue);
        }

        .view-all {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .view-all:hover {
            color: var(--primary-blue-dark);
        }

        /* Assignment List */
        .assignment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            margin-bottom: 12px;
            background: var(--card-gray);
            border-radius: 12px;
            transition: var(--transition);
        }

        .assignment-item:hover {
            background: var(--light-gray);
            transform: translateX(4px);
        }

        .assignment-info h4 {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .assignment-info p {
            color: var(--dark-gray);
            font-size: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .assignment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            min-width: 80px;
            text-align: center;
        }

        .status-submitted {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
        }

        .status-pending {
            background: rgba(255, 59, 48, 0.1);
            color: var(--danger);
        }

        /* Materials List */
        .material-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .material-item:last-child {
            border-bottom: none;
        }

        .material-icon {
            width: 40px;
            height: 40px;
            background: rgba(0, 122, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
        }

        .material-info h4 {
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 2px;
        }

        /* Exam List */
        .exam-item {
            background: var(--card-gray);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            transition: var(--transition);
        }

        .exam-item:hover {
            background: var(--light-gray);
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .exam-name {
            font-weight: 600;
        }

        .exam-duration {
            color: var(--dark-gray);
            font-size: 14px;
        }

        .exam-action {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary-blue);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .exam-action:hover {
            background: var(--primary-blue-dark);
        }

        /* Charts */
        .chart-container {
            height: 200px;
            margin-top: 16px;
        }

        /* Fee Status */
        .fee-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 16px;
        }

        .fee-amount {
            font-size: 24px;
            font-weight: 700;
        }

        .fee-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .badge-paid {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
        }

        .badge-pending {
            background: rgba(255, 59, 48, 0.1);
            color: var(--danger);
        }

        /* Attendance */
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 20px;
        }

        .attendance-stat {
            text-align: center;
            padding: 16px;
            background: var(--card-gray);
            border-radius: 12px;
        }

        .attendance-number {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .attendance-label {
            color: var(--dark-gray);
            font-size: 14px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 20px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--card-gray);
            border-radius: 12px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
        }

        .action-btn:hover {
            background: var(--light-gray);
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 24px;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }

        .action-btn span {
            font-size: 14px;
            font-weight: 500;
        }

        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            padding: 12px 20px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
        }

        .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--dark-gray);
            font-size: 12px;
            transition: var(--transition);
            padding: 8px;
            border-radius: 12px;
        }

        .nav-item.active {
            color: var(--primary-blue);
            background: rgba(0, 122, 255, 0.1);
        }

        .nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 16px;
            }
            
            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                gap: 20px;
            }
            
            .welcome-section h1 {
                font-size: 24px;
            }
            
            .mobile-nav {
                display: block;
            }
            
            .card {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .attendance-stats {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header animate-in">
            <div class="welcome-section">
                <h1>Welcome back, <?= htmlspecialchars($student['name']) ?></h1>
                <p>Track your progress and manage your learning journey</p>
            </div>
            <div class="user-profile">
                <img src="uploads/<?= $student['photo'] ?>" alt="Profile" class="profile-avatar">
                <div class="profile-info">
                    <h3><?= htmlspecialchars($student['name']) ?></h3>
                    <p><?= $student['enrollment_id'] ?></p>
                </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card animate-in">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Enrolled Courses</h3>
                        <div class="stat-number"><?= $course_stats['total_courses'] ?></div>
                    </div>
                    <i class="fas fa-graduation-cap stat-icon"></i>
                </div>
            </div>

            <div class="stat-card animate-in" style="animation-delay: 0.1s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Total Assignments</h3>
                        <div class="stat-number"><?= $assignment_stats['total_assignments'] ?></div>
                    </div>
                    <i class="fas fa-tasks stat-icon"></i>
                </div>
            </div>

            <div class="stat-card animate-in" style="animation-delay: 0.2s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Submitted</h3>
                        <div class="stat-number"><?= $assignment_stats['submitted_assignments'] ?></div>
                    </div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
            </div>

            <div class="stat-card animate-in" style="animation-delay: 0.3s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Study Materials</h3>
                        <div class="stat-number"><?= $total_materials ?></div>
                    </div>
                    <i class="fas fa-book stat-icon"></i>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Recent Assignments -->
                <div class="card animate-in">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-tasks"></i> Recent Assignments</h2>
                        <a href="assignment/student_dashboard.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php while($assignment = $assignments->fetch_assoc()): ?>
                        <div class="assignment-item">
                            <div class="assignment-info">
                                <h4><?= htmlspecialchars($assignment['title']) ?></h4>
                                <p><?= htmlspecialchars(substr($assignment['question_text'], 0, 80)) ?>...</p>
                            </div>
                            <span class="assignment-status <?= $assignment['submission_id'] ? 'status-submitted' : 'status-pending' ?>">
                                <?= $assignment['submission_id'] ? 'Submitted' : 'Pending' ?>
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="card animate-in" style="animation-delay: 0.1s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-chart-line"></i> Performance Trend</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Assigned Exams -->
                <div class="card animate-in" style="animation-delay: 0.2s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-pencil-alt"></i> Assigned Exams</h2>
                        <a href="exam-center/student/student_dashboard.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php while ($exam = $exams->fetch_assoc()): ?>
                        <div class="exam-item">
                            <div class="exam-header">
                                <div class="exam-name"><?= htmlspecialchars($exam['exam_name']) ?></div>
                                <div class="exam-duration"><?= $exam['duration'] ?> mins</div>
                            </div>
                            <a href="exam-center/student/take_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="exam-action">
                                Start Exam
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Attendance -->
                <div class="card animate-in">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-calendar-check"></i> Attendance</h2>
                        <a href="attendence/student_attendance.php" class="view-all">Details</a>
                    </div>
                    <div class="attendance-stats">
                        <div class="attendance-stat">
                            <div class="attendance-number" style="color: #34C759;"><?= $present ?></div>
                            <div class="attendance-label">Present</div>
                        </div>
                        <div class="attendance-stat">
                            <div class="attendance-number" style="color: #FF3B30;"><?= $absent ?></div>
                            <div class="attendance-label">Absent</div>
                        </div>
                        <div class="attendance-stat">
                            <div class="attendance-number" style="color: #FF9500;"><?= $leave ?></div>
                            <div class="attendance-label">Leave</div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <!-- Fee Status -->
                <div class="card animate-in" style="animation-delay: 0.1s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Fee Status</h2>
                    </div>
                    <div class="fee-status">
                        <div>
                            <div style="color: var(--dark-gray); font-size: 14px;"><?= $currentMonthName ?> Fee</div>
                            <div class="fee-amount">₹<?= $fee['fee_amount'] ?? '0' ?></div>
                        </div>
                        <div class="fee-badge <?= $feeStatus === 'Paid' ? 'badge-paid' : 'badge-pending' ?>">
                            <?= $feeStatus ?>
                        </div>
                    </div>
                </div>

                <!-- Study Materials -->
                <div class="card animate-in" style="animation-delay: 0.2s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-book-open"></i> Recent Materials</h2>
                        <a href="study-center/view_materials_student.php" class="view-all">View All</a>
                    </div>
                    <div class="card-content">
                        <?php if ($last_materials->num_rows > 0): ?>
                            <?php while($row = $last_materials->fetch_assoc()): ?>
                            <div class="material-item">
                                <div class="material-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="material-info">
                                    <h4><?= htmlspecialchars($row['title']) ?></h4>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--dark-gray); text-align: center; padding: 20px;">No materials assigned</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card animate-in" style="animation-delay: 0.3s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="quick-actions">
                        <a href="assignment/student_dashboard.php" class="action-btn">
                            <i class="fas fa-tasks"></i>
                            <span>Assignments</span>
                        </a>
                        <a href="study-center/view_materials_student.php" class="action-btn">
                            <i class="fas fa-book"></i>
                            <span>Study Center</span>
                        </a>
                        <a href="exam-center/student/student_dashboard.php" class="action-btn">
                            <i class="fas fa-pencil-alt"></i>
                            <span>Exams</span>
                        </a>
                        <a href="login-system/dashboard-user.php" class="action-btn">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <div class="nav-items">
            <a href="test.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="assignment/student_dashboard.php" class="nav-item">
                <i class="fas fa-tasks"></i>
                <span>Assignments</span>
            </a>
            <a href="study-center/view_materials_student.php" class="nav-item">
                <i class="fas fa-book"></i>
                <span>Study</span>
            </a>
            <a href="exam-center/student/student_dashboard.php" class="nav-item">
                <i class="fas fa-pencil-alt"></i>
                <span>Exams</span>
            </a>
            <a href="login-system/dashboard-user.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Attendance Chart (Pie)
            const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(attendanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Leave'],
                    datasets: [{
                        data: [<?= $present ?>, <?= $absent ?>, <?= $leave ?>],
                        backgroundColor: ['#34C759', '#FF3B30', '#FF9500'],
                        borderWidth: 0,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '65%'
                }
            });

            // Performance Chart (Line)
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Score %',
                        data: <?= json_encode($scores) ?>,
                        borderColor: '#007AFF',
                        backgroundColor: 'rgba(0, 122, 255, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#007AFF',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Add hover effects to cards
            const cards = document.querySelectorAll('.stat-card, .assignment-item, .exam-item');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Mobile navigation active state
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    navItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-in').forEach(el => {
            el.style.opacity = 0;
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>