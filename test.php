<?php
include 'database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) {
    header("Location: login-system/login.php");
    exit;
}

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch assignments
$assignments = $conn->query("
    SELECT a.*, s.submission_id, s.marks_awarded
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON s.assignment_id = a.assignment_id AND s.student_id = $student_id
    WHERE t.student_id = $student_id
       OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    GROUP BY a.assignment_id
    ORDER BY a.created_at DESC
    LIMIT 3
");

// Fetch course stats
$course_stats = $conn->query("
    SELECT COUNT(*) as total_courses 
    FROM student_batches 
    WHERE student_id = $student_id
")->fetch_assoc();

// Fetch assignment stats
$assignment_stats = $conn->query("
    SELECT 
        COUNT(DISTINCT a.assignment_id) as total_assignments,
        COUNT(s.submission_id) as submitted_assignments
    FROM assignments a
    LEFT JOIN assignment_targets t ON a.assignment_id = t.assignment_id
    LEFT JOIN assignment_submissions s ON s.assignment_id = a.assignment_id AND s.student_id = $student_id
    WHERE t.student_id = $student_id
       OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Faiz Computer Institute</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4cc9f0;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
            --dark: #1a1a2e;
            --dark-gray: #495057;
            --medium-gray: #6c757d;
            --light-gray: #e9ecef;
            --light: #f8f9fa;
            --white: #ffffff;
            --sidebar-width: 280px;
            --navbar-height: 70px;
            --footer-height: 70px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --box-shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f9fbfd;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
            padding-bottom: var(--footer-height);
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 0.75rem;
        }

        h1 { font-size: 2.5rem; }
        h2 { font-size: 2rem; }
        h3 { font-size: 1.75rem; }
        h4 { font-size: 1.5rem; }
        p { margin-bottom: 1rem; }

        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background-color: var(--white);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--primary);
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-toggler {
            background: none;
            border: none;
            color: var(--medium-gray);
            font-size: 1.4rem;
            cursor: pointer;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .navbar-toggler:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--medium-gray);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
            transition: var(--transition);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--navbar-height));
            background-color: var(--white);
            transition: var(--transition);
            z-index: 999;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--light-gray);
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.05);
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(76, 201, 240, 0.1));
        }

        .sidebar-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-user-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }

        .sidebar-user-info p {
            font-size: 0.85rem;
            color: var(--medium-gray);
            margin-bottom: 0;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(67, 97, 238, 0.05);
            color: var(--primary);
        }

        .nav-link.active {
            border-left: 4px solid var(--primary);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            color: inherit;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--light-gray);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: none;
            border: none;
            color: var(--medium-gray);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            width: 100%;
        }

        .logout-btn:hover {
            color: var(--danger);
            background-color: rgba(239, 35, 60, 0.05);
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            backdrop-filter: blur(3px);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Main Content */
        .main-content {
            padding: calc(var(--navbar-height) + 2rem) 2rem 2rem;
            transition: var(--transition);
            width: 100%;
            min-height: calc(100vh - var(--navbar-height) - var(--footer-height));
            margin-left: 0;
        }

        .sidebar.active + .main-content {
            margin-left: var(--sidebar-width);
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--box-shadow);
        }

        .welcome-text h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .user-profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }

        .stat-card.courses {
            border-left-color: #7209b7;
        }

        .stat-card.assignments {
            border-left-color: #f8961e;
        }

        .stat-card.submitted {
            border-left-color: #4cc9f0;
        }

        .stat-card.pending {
            border-left-color: #f72585;
        }

        .stat-title {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card.courses .stat-value {
            color: #7209b7;
        }

        .stat-card.assignments .stat-value {
            color: #f8961e;
        }

        .stat-card.submitted .stat-value {
            color: #4cc9f0;
        }

        .stat-card.pending .stat-value {
            color: #f72585;
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-left: auto;
            opacity: 0.8;
        }

        /* Assignments Section */
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--primary);
        }

        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .assignment-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
            display: flex;
            flex-direction: column;
        }

        .assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }

        .assignment-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark);
        }

        .assignment-details {
            font-size: 0.9rem;
            color: var(--medium-gray);
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .assignment-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--medium-gray);
            margin-bottom: 1rem;
        }

        .assignment-status {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-submitted {
            background-color: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
        }

        .status-pending {
            background-color: rgba(239, 35, 60, 0.1);
            color: #ef233c;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        /* Calendar Widget */
        .calendar-widget {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .calendar-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }

        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }

        .calendar-nav-btn {
            background: none;
            border: none;
            color: var(--medium-gray);
            cursor: pointer;
            font-size: 1rem;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .calendar-nav-btn:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }

        .calendar-day-header {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--medium-gray);
            text-align: center;
            padding: 0.5rem 0;
        }

        .calendar-day {
            text-align: center;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .calendar-day:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        .calendar-day.today {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }

        .calendar-day.other-month {
            color: var(--light-gray);
        }

        /* Mobile Footer */
        .mobile-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--footer-height);
            background-color: var(--white);
            box-shadow: 0 -2px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 990;
        }

        .footer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--medium-gray);
            font-size: 0.8rem;
            padding: 0.5rem;
            flex: 1;
            transition: var(--transition);
            position: relative;
        }

        .footer-item.active {
            color: var(--primary);
        }

        .footer-item i {
            font-size: 1.4rem;
            margin-bottom: 0.25rem;
            transition: var(--transition);
        }

        .footer-item.active i {
            transform: translateY(-5px);
        }

        .footer-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            transition: var(--transition);
        }

        .footer-item.active::after {
            width: 60%;
        }

        /* Responsive Adjustments */
        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: var(--sidebar-width);
            }
            
            .mobile-footer {
                display: none;
            }
            
            .navbar-toggler {
                display: none;
            }
        }

        @media (max-width: 991px) {
            .main-content {
                padding: calc(var(--navbar-height) + 1rem) 1rem 1rem;
            }
            
            .welcome-section {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .user-profile-img {
                margin-top: 1rem;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .assignments-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-text h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                padding: 0 1rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-left">
            <button class="navbar-toggler" id="navbarToggler">
                <i class="fas fa-bars"></i>
            </button>
            <a href="#" class="navbar-brand">
                <span>Faiz Computer Institute</span>
            </a>
        </div>
        
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="uploads/<?= $student['photo'] ?>" alt="User" class="sidebar-user-avatar">
            <div class="sidebar-user-info">
                <h4><?= htmlspecialchars($student['name']) ?></h4>
                <p><?= $student['enrollment_id'] ?></p>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="nav-item">
                <a href="test.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="assignment/student_dashboard.php" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
                </a>    
            </div>
            
            <div class="nav-item">
                <a href="study-center/view_materials_student.php" class="nav-link">
                    <i class="fas fa-book-open"></i>
                    <span>Study Center</span>
                </a>    
            </div>
            
            <div class="nav-item">
                <a href="exam-center/student/exam_result_student.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Results</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="exam-center/student/student_dashboard.php" class="nav-link">
                    <i class="fa-solid fa-pencil"></i>
                    <span>Exam Center</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="login-system/dashboard-user.php" class="nav-link">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </div>
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

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Welcome Section -->
        <section class="welcome-section animate-in">
            <div class="welcome-text">
                <h2>Welcome back, <?= htmlspecialchars($student['name']) ?>!</h2>
                <p>Ready to continue your learning journey?</p>
            </div>
            <img src="uploads/<?= $student['photo'] ?>" alt="Profile" class="user-profile-img">
        </section>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card courses animate-in delay-1">
                <div class="stat-title">
                    <i class="fas fa-book"></i>
                    <span>Enrolled Courses</span>
                </div>
                <div class="stat-value"><?= $course_stats['total_courses'] ?></div>
                <i class="fas fa-graduation-cap stat-icon"></i>
            </div>
            
            <div class="stat-card assignments animate-in delay-2">
                <div class="stat-title">
                    <i class="fas fa-tasks"></i>
                    <span>Total Assignments</span>
                </div>
                <div class="stat-value"><?= $assignment_stats['total_assignments'] ?></div>
                <i class="fas fa-clipboard-list stat-icon"></i>
            </div>
            
            <div class="stat-card submitted animate-in delay-3">
                <div class="stat-title">
                    <i class="fas fa-check-circle"></i>
                    <span>Submitted</span>
                </div>
                <div class="stat-value"><?= $assignment_stats['submitted_assignments'] ?></div>
                <i class="fas fa-check-double stat-icon"></i>
            </div>
            
            <div class="stat-card pending animate-in delay-4">
                <div class="stat-title">
                    <i class="fas fa-clock"></i>
                    <span>Pending</span>
                </div>
                <div class="stat-value"><?= $assignment_stats['total_assignments'] - $assignment_stats['submitted_assignments'] ?></div>
                <i class="fas fa-exclamation-circle stat-icon"></i>
            </div>
        </div>

        <!-- Recent Assignments -->
        <section>
            <h3 class="section-title">
                <i class="fas fa-tasks"></i>
                Recent Assignments
            </h3>
            
            <div class="assignments-grid">
                <?php while($assignment = $assignments->fetch_assoc()): ?>
                    <div class="assignment-card animate-in">
                        <h4 class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></h4>
                        <p class="assignment-details"><?= htmlspecialchars(substr($assignment['question_text'], 0, 100)) ?>...</p>
                        
                        <div class="assignment-meta">
                           
                            <?php if ($assignment['submission_id']): ?>
                                <span class="assignment-status status-submitted">Submitted</span>
                            <?php else: ?>
                                <span class="assignment-status status-pending">Pending</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="assignment/student_dashboard.php" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Calendar Widget -->
        <section class="calendar-widget animate-in">
            <div class="calendar-header">
                <h3 class="calendar-title"><?= date('F Y') ?></h3>
                <div class="calendar-nav">
                    <button class="calendar-nav-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="calendar-nav-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            
            <div class="calendar-grid">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
                
                <!-- Calendar days would be dynamically generated in a real app -->
                <?php
                    $firstDay = date('w', strtotime(date('Y-m-01')));
                    $daysInMonth = date('t');
                    $currentDay = date('j');
                    
                    // Previous month days
                    for ($i = 0; $i < $firstDay; $i++) {
                        echo '<div class="calendar-day other-month"></div>';
                    }
                    
                    // Current month days
                    for ($i = 1; $i <= $daysInMonth; $i++) {
                        $class = $i == $currentDay ? 'calendar-day today' : 'calendar-day';
                        echo "<div class='$class'>$i</div>";
                    }
                ?>
            </div>
        </section>
    </main>

    <!-- Mobile Footer -->
    <footer class="mobile-footer">
        <a href="test.php" class="footer-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="assignment/student_dashboard.php" class="footer-item">
            <i class="fas fa-tasks"></i>
            <span>Assignments</span>
        </a>
        <a href="study-center/view_materials_student.php" class="footer-item">
            <i class="fas fa-book"></i>
            <span>Study</span>
        </a>
        <a href="login-system/dashboard-user.php" class="footer-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const navbarToggler = document.getElementById('navbarToggler');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const mainContent = document.getElementById('mainContent');
            
            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
            
            // Close sidebar when clicking on overlay
            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
            
            // Event listeners
            navbarToggler.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', closeSidebar);
            
            // Add scroll effect to navbar
            window.addEventListener('scroll', function() {
                if (window.scrollY > 10) {
                    document.getElementById('navbar').classList.add('scrolled');
                } else {
                    document.getElementById('navbar').classList.remove('scrolled');
                }
            });
            
            // Initialize calendar navigation (placeholder functionality)
            document.querySelectorAll('.calendar-nav-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // In a real app, this would update the calendar view
                    alert('Calendar navigation would be implemented here');
                });
            });
            
            // Add click effect to calendar days
            document.querySelectorAll('.calendar-day:not(.other-month)').forEach(day => {
                day.addEventListener('click', function() {
                    // In a real app, this would show events for the selected day
                    alert('Day selected: ' + this.textContent);
                });
            });
            
            // Responsive adjustments
            function handleResize() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.add('active');
                    overlay.classList.remove('active');
                } else {
                    sidebar.classList.remove('active');
                }
            }
            
            // Initialize on load
            handleResize();
            window.addEventListener('resize', handleResize);
            
            // Add animation to elements as they come into view
            const animateElements = document.querySelectorAll('.animate-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            animateElements.forEach(el => {
                el.style.opacity = 0;
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>