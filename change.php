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
    SELECT payment_status, fee_amount
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
    $fee = $result->fetch_assoc();
    $feeStatus = $fee['payment_status'];
    $feeAmount = $fee['fee_amount'] ?? 0;
} else {
    $feeStatus = 'Pending';
    $feeAmount = 0;
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
    $labels[] = substr($r['exam_name'], 0, 12) . (strlen($r['exam_name']) > 12 ? '...' : '');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <title>Dashboard | Faiz Computer Institute</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@300;400;500;600;700&family=SF+Pro+Text:wght@400;500;600&display=swap" rel="stylesheet">
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
            --sidebar-bg: #FFFFFF;
            --sidebar-width: 240px;
            --card-radius: 12px;
            --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            --card-shadow-hover: 0 6px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f8f9ff 0%, #f2f4ff 100%);
            color: var(--dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Navigation - More Compact */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-right: 1px solid var(--glass-border);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            overflow-y: auto;
            padding: 16px 0;
            box-shadow: 3px 0 25px rgba(0, 0, 0, 0.06);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header {
            padding: 0 16px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            margin-bottom: 12px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--white);
            box-shadow: 0 3px 10px rgba(0, 122, 255, 0.15);
        }

        .profile-info h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
            color: var(--dark);
        }

        .profile-info p {
            color: var(--dark-gray);
            font-size: 11px;
            font-weight: 400;
        }

        .sidebar-section {
            padding: 6px 0;
            margin-bottom: 10px;
        }

        .section-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--dark-gray);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 0 16px;
            margin-bottom: 6px;
            display: block;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: var(--dark-gray);
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            border-radius: 10px;
            margin: 0 10px 4px;
            transition: var(--transition);
            position: relative;
            background: transparent;
        }

        .nav-item:hover {
            background: rgba(0, 122, 255, 0.07);
            color: var(--primary-blue);
            transform: translateX(3px);
        }

        .nav-item.active {
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary-blue);
            font-weight: 600;
            box-shadow: inset 3px 0 0 var(--primary-blue);
        }

        .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 13px;
            color: inherit;
        }

        .nav-badge {
            margin-left: auto;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 8px;
            background: rgba(0, 122, 255, 0.1);
            color: var(--primary-blue);
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px;
            margin-top: auto;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 10px 14px;
            background: rgba(255, 59, 48, 0.07);
            color: var(--danger);
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: rgba(255, 59, 48, 0.12);
            transform: translateY(-1px);
        }

        /* Main Content - More Compact */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dashboard Header - More Compact */
        .dashboard-header {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: var(--card-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--glass-border);
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-blue));
        }

        .welcome-section h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--dark);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .institute-name {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .welcome-section p {
            color: var(--dark-gray);
            font-size: 13px;
            font-weight: 400;
            max-width: 450px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Live Clock - Compact */
        .live-clock {
            background: rgba(0, 122, 255, 0.08);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(0, 122, 255, 0.15);
        }

        .clock-icon {
            color: var(--primary-blue);
            font-size: 12px;
        }

        .clock-time {
            font-family: 'SF Pro Display', monospace;
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }

        .date-display {
            font-size: 13px;
            color: var(--dark-gray);
            background: rgba(0, 0, 0, 0.02);
            padding: 6px 12px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Stats Grid - More Compact */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: var(--card-radius);
            padding: 16px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--glass-border);
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
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-blue), var(--accent-blue));
            border-radius: 3px 0 0 3px;
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-text h3 {
            font-size: 11px;
            font-weight: 500;
            color: var(--dark-gray);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .stat-number {
            font-size: 26px;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
        }

        .stat-icon {
            font-size: 26px;
            color: var(--primary-blue);
            opacity: 0.85;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        /* Cards - More Compact */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: var(--primary-blue);
            font-size: 14px;
        }

        .view-all {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            transition: var(--transition);
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(0, 122, 255, 0.07);
        }

        .view-all:hover {
            background: rgba(0, 122, 255, 0.12);
            transform: translateX(2px);
        }

        /* Assignment List - More Compact */
        .assignment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px;
            margin-bottom: 10px;
            background: rgba(0, 0, 0, 0.015);
            border-radius: 10px;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .assignment-item:hover {
            background: rgba(0, 122, 255, 0.03);
            border-color: rgba(0, 122, 255, 0.08);
            transform: translateX(3px);
        }

        .assignment-info h4 {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 3px;
            color: var(--dark);
        }

        .assignment-info p {
            color: var(--dark-gray);
            font-size: 11px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .assignment-status {
            padding: 5px 10px;
            border-radius: 18px;
            font-size: 11px;
            font-weight: 600;
            min-width: 70px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-submitted {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            border: 1px solid rgba(52, 199, 89, 0.2);
        }

        .status-pending {
            background: rgba(255, 59, 48, 0.1);
            color: var(--danger);
            border: 1px solid rgba(255, 59, 48, 0.2);
        }

        /* Materials List */
        .material-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: var(--transition);
        }

        .material-item:hover {
            transform: translateX(3px);
        }

        .material-item:last-child {
            border-bottom: none;
        }

        .material-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, rgba(0, 122, 255, 0.08) 0%, rgba(90, 200, 250, 0.08) 100%);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-size: 13px;
            border: 1px solid rgba(0, 122, 255, 0.08);
        }

        .material-info h4 {
            font-weight: 500;
            font-size: 12px;
            margin-bottom: 2px;
            color: var(--dark);
        }

        /* Exam List */
        .exam-item {
            background: rgba(0, 0, 0, 0.015);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .exam-item:hover {
            background: rgba(0, 122, 255, 0.03);
            border-color: rgba(0, 122, 255, 0.08);
            transform: translateY(-1px);
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .exam-name {
            font-weight: 600;
            font-size: 13px;
            color: var(--dark);
        }

        .exam-duration {
            color: var(--dark-gray);
            font-size: 11px;
            background: rgba(0, 0, 0, 0.03);
            padding: 3px 7px;
            border-radius: 7px;
            font-weight: 500;
        }

        .exam-action {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
            color: white;
            text-decoration: none;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .exam-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 122, 255, 0.25);
        }

        /* Charts */
        .chart-container {
            height: 160px;
            margin-top: 14px;
        }

        /* Fee Status */
        .fee-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            padding: 14px;
            background: rgba(0, 0, 0, 0.015);
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .fee-amount {
            font-size: 22px;
            font-weight: 800;
            color: var(--dark);
        }

        .fee-badge {
            padding: 6px 14px;
            border-radius: 18px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .badge-paid {
            background: rgba(52, 199, 89, 0.12);
            color: var(--success);
            border: 2px solid rgba(52, 199, 89, 0.25);
        }

        .badge-pending {
            background: rgba(255, 59, 48, 0.12);
            color: var(--danger);
            border: 2px solid rgba(255, 59, 48, 0.25);
        }

        /* Attendance */
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .attendance-stat {
            text-align: center;
            padding: 16px;
            background: rgba(0, 0, 0, 0.015);
            border-radius: 10px;
            border: 1px solid transparent;
            transition: var(--transition);
        }

        .attendance-stat:hover {
            border-color: rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }

        .attendance-number {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .attendance-label {
            color: var(--dark-gray);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 12px;
            background: rgba(0, 0, 0, 0.015);
            border-radius: 12px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .action-btn:hover {
            background: rgba(0, 122, 255, 0.07);
            border-color: rgba(0, 122, 255, 0.15);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 20px rgba(0, 122, 255, 0.12);
        }

        .action-btn i {
            font-size: 20px;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }

        .action-btn span {
            font-size: 12px;
            font-weight: 600;
        }

        /* Mobile Navigation - More Compact */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 10px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            border-top: 1px solid var(--glass-border);
            border-radius: 20px 20px 0 0;
        }

        .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .nav-item-mobile {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--dark-gray);
            font-size: 10px;
            transition: var(--transition);
            padding: 8px 10px;
            border-radius: 14px;
            min-width: 60px;
            background: transparent;
        }

        .nav-item-mobile.active {
            color: var(--primary-blue);
            background: rgba(0, 122, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.12);
        }

        .nav-item-mobile i {
            font-size: 16px;
            margin-bottom: 3px;
        }

        .nav-item-mobile span {
            font-weight: 500;
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            color: var(--dark);
            font-size: 18px;
            cursor: pointer;
            padding: 10px;
            border-radius: 10px;
            transition: var(--transition);
            z-index: 1001;
        }

        .menu-toggle:hover {
            background: rgba(0, 122, 255, 0.08);
            transform: rotate(90deg);
        }

        /* Mobile Institute Logo */
        .mobile-institute-header {
            display: none;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
            color: white;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 1002;
            box-shadow: 0 2px 10px rgba(0, 122, 255, 0.2);
        }

        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding-bottom: 80px;
                padding-top: 16px;
            }
            
            .mobile-nav {
                display: block;
            }
            
            .mobile-institute-header {
                display: block;
            }
            
            .menu-toggle {
                display: block;
                position: fixed;
                top: 16px;
                left: 16px;
                z-index: 1003;
            }
            
            .dashboard-header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
                padding: 16px;
                margin-top: 56px;
            }
            
            .institute-name {
                display: none;
            }
            
            .header-right {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .live-clock {
                order: -1;
                width: 100%;
                justify-content: center;
                background: rgba(255, 255, 255, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .clock-time {
                color: var(--primary-blue);
            }
            
            .date-display {
                width: 100%;
                justify-content: center;
                background: rgba(0, 0, 0, 0.02);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 12px;
                padding-bottom: 80px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 20px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .card {
                padding: 16px;
                margin-bottom: 16px;
            }
            
            .welcome-section h1 {
                font-size: 20px;
            }
            
            .welcome-section p {
                font-size: 12px;
            }
            
            .stat-number {
                font-size: 24px;
            }
            
            .attendance-stats {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .assignment-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .assignment-status {
                align-self: flex-end;
            }
            
            .exam-item {
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .mobile-nav {
                padding: 8px 12px;
                border-radius: 16px 16px 0 0;
            }
            
            .nav-item-mobile {
                min-width: 52px;
                padding: 6px 8px;
                font-size: 9px;
            }
            
            .nav-item-mobile i {
                font-size: 14px;
            }
            
            .dashboard-header {
                padding: 14px;
            }
            
            .card {
                padding: 14px;
            }
            
            .stat-card {
                padding: 14px;
            }
            
            .stat-number {
                font-size: 22px;
            }
            
            .attendance-stat {
                padding: 14px;
            }
            
            .attendance-number {
                font-size: 22px;
            }
            
            .action-btn {
                padding: 16px 10px;
            }
            
            .action-btn i {
                font-size: 18px;
            }
            
            .chart-container {
                height: 140px;
            }
            
            .welcome-section h1 {
                font-size: 18px;
            }
            
            .mobile-institute-header {
                font-size: 12px;
                padding: 12px;
            }
        }

        @media (max-width: 360px) {
            .mobile-nav {
                border-radius: 14px 14px 0 0;
            }
            
            .nav-items {
                gap: 2px;
            }
            
            .nav-item-mobile {
                min-width: 46px;
                padding: 5px 6px;
                font-size: 8px;
            }
            
            .nav-item-mobile i {
                font-size: 13px;
            }
            
            .welcome-section h1 {
                font-size: 17px;
            }
            
            .stat-card::before {
                width: 2px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-6px);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }

        .slide-in {
            animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-blue);
            border-radius: 8px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-blue-dark);
        }
    </style>
</head>
<body>
    <!-- Mobile Institute Header -->
    <div class="mobile-institute-header">
        <i class="fas fa-graduation-cap mr-2"></i>
        Faiz Computer Institute
    </div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="user-profile">
                <img src="uploads/<?= $student['photo'] ?>" alt="Profile" class="profile-avatar float-animation">
                <div class="profile-info">
                    <h3><?= htmlspecialchars($student['name']) ?></h3>
                    <p><?= $student['enrollment_id'] ?></p>
                </div>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div class="sidebar-section">
            <span class="section-label">Dashboard</span>
            <a href="test.php" class="nav-item active">
                <i class="fas fa-tachometer-alt nav-icon"></i>
                <span>Overview</span>
                <span class="nav-badge pulse-animation">Live</span>
            </a>
        </div>

        <!-- Academics Section -->
        <div class="sidebar-section">
            <span class="section-label">Academics</span>
            <a href="assignment/student_dashboard.php" class="nav-item">
                <i class="fas fa-tasks nav-icon"></i>
                <span>Assignments</span>
                <span class="nav-badge"><?= $assignment_stats['total_assignments'] - $assignment_stats['submitted_assignments'] ?></span>
            </a>
            <a href="study-center/view_materials_student.php" class="nav-item">
                <i class="fas fa-book-open nav-icon"></i>
                <span>Study Center</span>
                <span class="nav-badge"><?= $total_materials ?></span>
            </a>
            <a href="attendence/student_attendance.php" class="nav-item">
                <i class="fas fa-calendar-check nav-icon"></i>
                <span>Attendance</span>
                <span class="nav-badge"><?= $present ?>%</span>
            </a>
        </div>

        <!-- Examination Section -->
        <div class="sidebar-section">
            <span class="section-label">Examination</span>
            <a href="exam-center/student/student_dashboard.php" class="nav-item">
                <i class="fas fa-pencil-alt nav-icon"></i>
                <span>Exam Center</span>
                <span class="nav-badge"><?= $exams->num_rows ?></span>
            </a>
            <a href="exam-center/student/exam_result_student.php" class="nav-item">
                <i class="fas fa-chart-line nav-icon"></i>
                <span>Results</span>
                <span class="nav-badge"><?= count($labels) ?></span>
            </a>
        </div>

        <!-- Resources Section -->
        <div class="sidebar-section">
            <span class="section-label">Resources</span>
            <a href="video-portal/student/student_videos.php" class="nav-item">
                <i class="fas fa-video nav-icon"></i>
                <span>Online Classes</span>
            </a>
            <a href="library/student_library.php" class="nav-item">
                <i class="fas fa-book-reader nav-icon"></i>
                <span>Digital Library</span>
            </a>
        </div>

        <!-- Finance Section -->
        <div class="sidebar-section">
            <span class="section-label">Finance</span>
            <a href="fee/students/my_fee_receipts.php" class="nav-item">
                <i class="fas fa-file-invoice-dollar nav-icon"></i>
                <span>Fee Center</span>
                <span class="nav-badge"><?= $feeStatus ?></span>
            </a>
        </div>

        <!-- Profile Section -->
        <div class="sidebar-section">
            <span class="section-label">Profile</span>
            <a href="login-system/dashboard-user.php" class="nav-item">
                <i class="fas fa-user nav-icon"></i>
                <span>My Profile</span>
            </a>
            <a href="settings/student_settings.php" class="nav-item">
                <i class="fas fa-cog nav-icon"></i>
                <span>Settings</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <a href="login-system/logout.php" style="text-decoration: none;">
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </a>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Mobile Menu Toggle -->
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Dashboard Header -->
        <div class="dashboard-header animate-in" style="animation-delay: 0.1s">
            <div class="welcome-section">
                <div class="institute-name">
                    <i class="fas fa-graduation-cap mr-1"></i>
                    Faiz Computer Institute
                </div>
                <h1>Welcome back, <?= htmlspecialchars($student['name']) ?> 👋</h1>
                <p>Track your progress and manage your learning journey</p>
            </div>
            <div class="header-right">
                <div class="live-clock">
                    <i class="fas fa-clock clock-icon"></i>
                    <div class="clock-time" id="liveClock">--:--:--</div>
                </div>
                <div class="date-display">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="currentDate"><?= date('l, F j, Y') ?></span>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card animate-in" style="animation-delay: 0.2s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Enrolled Courses</h3>
                        <div class="stat-number"><?= $course_stats['total_courses'] ?></div>
                    </div>
                    <i class="fas fa-graduation-cap stat-icon float-animation"></i>
                </div>
            </div>

            <div class="stat-card animate-in" style="animation-delay: 0.3s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Total Assignments</h3>
                        <div class="stat-number"><?= $assignment_stats['total_assignments'] ?></div>
                    </div>
                    <i class="fas fa-tasks stat-icon float-animation"></i>
                </div>
            </div>

            <div class="stat-card animate-in" style="animation-delay: 0.4s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Submitted</h3>
                        <div class="stat-number"><?= $assignment_stats['submitted_assignments'] ?></div>
                    </div>
                    <i class="fas fa-check-circle stat-icon float-animation"></i>
                </div>
            </div>

            <div class="stat-card animate-in" style="animation-delay: 0.5s">
                <div class="stat-content">
                    <div class="stat-text">
                        <h3>Study Materials</h3>
                        <div class="stat-number"><?= $total_materials ?></div>
                    </div>
                    <i class="fas fa-book stat-icon float-animation"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Recent Assignments -->
                <div class="card animate-in" style="animation-delay: 0.2s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-tasks"></i> Recent Assignments</h2>
                        <a href="assignment/student_dashboard.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-content">
                        <?php while($assignment = $assignments->fetch_assoc()): ?>
                        <div class="assignment-item slide-in">
                            <div class="assignment-info">
                                <h4><?= htmlspecialchars($assignment['title']) ?></h4>
                                <p><?= htmlspecialchars(substr($assignment['question_text'], 0, 70)) ?>...</p>
                            </div>
                            <span class="assignment-status <?= $assignment['submission_id'] ? 'status-submitted' : 'status-pending' ?>">
                                <?= $assignment['submission_id'] ? 'Submitted' : 'Pending' ?>
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="card animate-in" style="animation-delay: 0.3s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-chart-line"></i> Performance Trend</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Assigned Exams -->
                <div class="card animate-in" style="animation-delay: 0.4s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-pencil-alt"></i> Assigned Exams</h2>
                        <a href="exam-center/student/student_dashboard.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-content">
                        <?php while ($exam = $exams->fetch_assoc()): ?>
                        <div class="exam-item slide-in">
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
                <div class="card animate-in" style="animation-delay: 0.2s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-calendar-check"></i> Attendance</h2>
                        <a href="attendence/student_attendance.php" class="view-all">Details <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="attendance-stats">
                        <div class="attendance-stat" style="border-color: rgba(52, 199, 89, 0.15);">
                            <div class="attendance-number" style="color: #34C759;"><?= $present ?></div>
                            <div class="attendance-label">Present</div>
                        </div>
                        <div class="attendance-stat" style="border-color: rgba(255, 59, 48, 0.15);">
                            <div class="attendance-number" style="color: #FF3B30;"><?= $absent ?></div>
                            <div class="attendance-label">Absent</div>
                        </div>
                        <div class="attendance-stat" style="border-color: rgba(255, 149, 0, 0.15);">
                            <div class="attendance-number" style="color: #FF9500;"><?= $leave ?></div>
                            <div class="attendance-label">Leave</div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>

                <!-- Fee Status -->
                <div class="card animate-in" style="animation-delay: 0.3s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Fee Status</h2>
                    </div>
                    <div class="fee-status">
                        <div>
                            <div style="color: var(--dark-gray); font-size: 12px; font-weight: 500;"><?= $currentMonthName ?> Fee</div>
                            <div class="fee-amount">₹<?= $feeAmount ?></div>
                        </div>
                        <div class="fee-badge <?= $feeStatus === 'Paid' ? 'badge-paid' : 'badge-pending' ?> float-animation">
                            <?= $feeStatus ?>
                        </div>
                    </div>
                </div>

                <!-- Study Materials -->
                <div class="card animate-in" style="animation-delay: 0.4s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-book-open"></i> Recent Materials</h2>
                        <a href="study-center/view_materials_student.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card-content">
                        <?php if ($last_materials->num_rows > 0): ?>
                            <?php while($row = $last_materials->fetch_assoc()): ?>
                            <div class="material-item slide-in">
                                <div class="material-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="material-info">
                                    <h4><?= htmlspecialchars($row['title']) ?></h4>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--dark-gray); text-align: center; padding: 16px; font-size: 12px; background: rgba(0, 0, 0, 0.015); border-radius: 10px;">
                                <i class="fas fa-inbox mr-2"></i> No materials assigned
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card animate-in" style="animation-delay: 0.5s">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="quick-actions">
                        <a href="assignment/student_dashboard.php" class="action-btn">
                            <i class="fas fa-tasks float-animation"></i>
                            <span>Assignments</span>
                        </a>
                        <a href="study-center/view_materials_student.php" class="action-btn">
                            <i class="fas fa-book float-animation"></i>
                            <span>Study Center</span>
                        </a>
                        <a href="exam-center/student/student_dashboard.php" class="action-btn">
                            <i class="fas fa-pencil-alt float-animation"></i>
                            <span>Exams</span>
                        </a>
                        <a href="video-portal/student/student_videos.php" class="action-btn">
                            <i class="fas fa-video float-animation"></i>
                            <span>Classes</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <div class="nav-items">
            <a href="test.php" class="nav-item-mobile active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="assignment/student_dashboard.php" class="nav-item-mobile">
                <i class="fas fa-tasks"></i>
                <span>Assignments</span>
            </a>
            <a href="study-center/view_materials_student.php" class="nav-item-mobile">
                <i class="fas fa-book"></i>
                <span>Study</span>
            </a>
            <a href="exam-center/student/student_dashboard.php" class="nav-item-mobile">
                <i class="fas fa-pencil-alt"></i>
                <span>Exams</span>
            </a>
            <a href="login-system/dashboard-user.php" class="nav-item-mobile">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <script>
        // Live Clock Functionality
        function updateLiveClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const clockElement = document.getElementById('liveClock');
            if (clockElement) {
                clockElement.textContent = timeString;
                clockElement.classList.add('pulse-animation');
                setTimeout(() => clockElement.classList.remove('pulse-animation'), 300);
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            // Start live clock
            updateLiveClock();
            setInterval(updateLiveClock, 1000);

            // Mobile menu functionality
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('overlay');
            
            function toggleMobileMenu() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
            
            if (menuToggle) {
                menuToggle.addEventListener('click', toggleMobileMenu);
                overlay.addEventListener('click', toggleMobileMenu);
            }
            
            // Close menu when clicking nav items
            document.querySelectorAll('.nav-item, .nav-item-mobile').forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
            
            // Responsive checks
            function checkResponsive() {
                if (window.innerWidth <= 1024) {
                    menuToggle.style.display = 'block';
                } else {
                    menuToggle.style.display = 'none';
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
            
            checkResponsive();
            window.addEventListener('resize', checkResponsive);
            
            // Initialize Charts
            // Attendance Chart
            const attendanceCtx = document.getElementById('attendanceChart')?.getContext('2d');
            if (attendanceCtx) {
                new Chart(attendanceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent', 'Leave'],
                        datasets: [{
                            data: [<?= $present ?>, <?= $absent ?>, <?= $leave ?>],
                            backgroundColor: [
                                'rgba(52, 199, 89, 0.8)',
                                'rgba(255, 59, 48, 0.8)',
                                'rgba(255, 149, 0, 0.8)'
                            ],
                            borderWidth: 1,
                            borderRadius: 6,
                            spacing: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 12,
                                    usePointStyle: true,
                                    font: { size: 10 }
                                }
                            }
                        },
                        cutout: '72%'
                    }
                });
            }
            
            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart')?.getContext('2d');
            if (performanceCtx) {
                new Chart(performanceCtx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($labels) ?>,
                        datasets: [{
                            label: 'Score %',
                            data: <?= json_encode($scores) ?>,
                            borderColor: '#007AFF',
                            backgroundColor: 'rgba(0, 122, 255, 0.08)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#007AFF',
                            pointBorderColor: '#FFFFFF',
                            pointBorderWidth: 1.5,
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { drawBorder: false, color: 'rgba(0, 0, 0, 0.03)' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: value => value + '%'
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 10 },
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        }
                    }
                });
            }
            
            // Enhanced hover effects
            const interactiveElements = document.querySelectorAll('.stat-card, .assignment-item, .exam-item, .attendance-stat, .action-btn, .material-item');
            interactiveElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    this.style.transform = this.classList.contains('stat-card') ? 
                        'translateY(-6px)' : 'translateY(-2px)';
                });
                element.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Add touch feedback for mobile
            interactiveElements.forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                element.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
            
            // Stagger animations
            document.querySelectorAll('.slide-in').forEach((el, index) => {
                el.style.animationDelay = `${0.1 + (index * 0.1)}s`;
            });
            
            // Intersection Observer for animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.animate-in').forEach(el => {
                el.style.opacity = 0;
                el.style.transform = 'translateY(15px)';
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(el);
            });
        });
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateLiveClock();
            }
        });
    </script>
</body>
</html>