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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPortal - Modern Learning Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f9fbfd;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
            padding-bottom: var(--footer-height);
            transition: var(--transition);
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
            padding: 0 1rem;
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
        li {
            list-style-type: none;
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
            display: none;
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

        .user-profile:hover .user-avatar {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--white);
            transition: var(--transition);
            z-index: 999;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--light-gray);
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.05);
        }

        .sidebar.active {
            transform: translateX(280px);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 1rem;
            height: var(--navbar-height);
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

        .badge {
            background-color: var(--accent);
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            margin-left: auto;
        }

        .dropdown-toggle::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
            font-size: 0.75rem;
            transition: var(--transition);
            color: var(--medium-gray);
        }

        .dropdown-toggle.active::after {
            transform: rotate(180deg);
            color: var(--primary);
        }

        .dropdown-menu {
            display: none;
            background-color: rgba(67, 97, 238, 0.03);
            list-style: none;
            padding: 0.5rem 0;
            border-left: 3px solid var(--light-gray);
            transition: var(--transition);
        }

        .dropdown-menu.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem 0.75rem 3.5rem;
            color: var(--dark-gray);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
            position: relative;
        }

        .dropdown-item:hover,
        .dropdown-item.active {
            color: var(--primary);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .dropdown-item i {
            font-size: 0.9rem;
            width: 18px;
        }

        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 3rem;
            width: 6px;
            height: 6px;
            background-color: var(--medium-gray);
            border-radius: 50%;
            transition: var(--transition);
        }

        .dropdown-item:hover::before,
        .dropdown-item.active::before {
            background-color: var(--primary);
            transform: scale(1.3);
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

        .logout-btn i {
            font-size: 1.1rem;
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
        }

        /* Cards */
        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .card-title {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }

        .stat-card.courses {
            border-left-color: #7209b7;
        }

        .stat-card.assignments {
            border-left-color: #f8961e;
        }

        .stat-card.grades {
            border-left-color: #4cc9f0;
        }

        .stat-card.messages {
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
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card.courses .stat-value {
            background: linear-gradient(135deg, #7209b7, #b5179e);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .stat-card.assignments .stat-value {
            background: linear-gradient(135deg, #f8961e, #f3722c);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .stat-card.grades .stat-value {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .stat-card.messages .stat-value {
            background: linear-gradient(135deg, #f72585, #b5179e);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .stat-change {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: #2ec4b6;
        }

        .stat-change.negative {
            color: #ef233c;
        }

        /* Recent Activity */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.85rem;
            color: var(--medium-gray);
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
            body {
                padding-left: 0;
            }
            
            .main-content {
                padding-left: 2rem;
            }
            
            .mobile-footer {
                display: none;
            }
            
            .user-info {
                display: block;
            }
            
            .navbar {
                padding: 0 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: calc(var(--navbar-height) + 1rem) 1rem 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
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

    <!-- Sidebar - Initially closed -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-user-info">
              
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="nav-item">
                <a href="test.php" class="nav-link active" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
           <div class="nav-item">
                <a href="study-center/view_materials_student.php" class="nav-link">
                    <i class="fas fa-book-open"></i>
                    <span>Study Center</span>
                </a>    
            </div>
            
            
            <div class="nav-item">
                <a href="study-center/view_materials_student.php" class="nav-link">
                    <i class="fas fa-book-open"></i>
                    <span>Study Center</span>
                </a>    
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link" data-page="grades">
                    <i class="fas fa-chart-line"></i>
                    <span>Results</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="exam-center/student/student_dashboard.php" class="nav-link" data-page="notifications">
                    <i class="fa-solid fa-pencil"></i>
                    <span>Exam Center </span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="login-system/dashboard-user.php" class="nav-link" data-page="messages">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </div>
            
        </div>
        
        <div class="sidebar-footer">
            <a href="login-system/logout.php">
            <button class="logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span></a>
            </button>
        </div>
    </aside>
<div class="main">
        <div class="welcome">
            <div class="text">
                <h2>Hello, <?= htmlspecialchars($student['name']) ?></h2>
                <p>Enrollment: <?= $student['enrollment_id'] ?> | Course: <?= $student['course'] ?></p>
            </div>
            <div class="profile-pic">
                <img src="uploads/<?= $student['photo'] ?>" width="60" style="border-radius: 50%;">
            </div>
        </div>

        <div class="card-section">
            <h3>Your Assignments</h3>
            <div class="cards">
                <?php while($a = $assignments->fetch_assoc()) { ?>
                    <div class="card">
                        <h4><?= htmlspecialchars($a['title']) ?></h4>
                        <p><?= htmlspecialchars(substr($a['question_text'], 0, 50)) ?>...</p>
                        <?php if ($a['submission_id']) { ?>
                            <div class="status submitted">✅ Submitted</div>
                        <?php } else { ?>
                            <div class="status not-submitted">❌ Not Submitted</div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="calendar">
            <h4>📅 Calendar</h4>
            <p><?= date('F j, Y') ?></p>
        </div>
        <div class="task-list">
            <h4>📝 Your Tasks</h4>
            <ul>
                <li>Upload Assignment</li>
                <li>Study for Quiz</li>
                <li>Check Study Notes</li>
                <li>Complete Practice Exam</li>
            </ul>
        </div>
    </div>

    
    <!-- Mobile Footer -->
    <footer class="mobile-footer">
        <a href="test.php" class="footer-item active" data-page="dashboard">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="assignment/student_dashboard.php" class="footer-item" data-page="assignments">
            <i class="fas fa-tasks"></i>
            <span>Assignments</span>
        </a>
        <a href="study-center/view_materials_student.php" class="footer-item" data-page="study">
            <i class="fas fa-book"></i>
            <span>Study</span>
        </a>
        <a href="login-system/dashboard-user.php" class="footer-item" data-page="messages">
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
            const logoutBtn = document.getElementById('logoutBtn');
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle)');
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            const footerItems = document.querySelectorAll('.footer-item');
            const navbar = document.getElementById('navbar');

            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                
                // On desktop, adjust body padding when sidebar opens
                if (window.innerWidth >= 992) {
                    document.body.style.paddingLeft = sidebar.classList.contains('active') ? '280px' : '0';
                }
            }

            // Close sidebar
            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                
                if (window.innerWidth >= 992) {
                    document.body.style.paddingLeft = '0';
                }
            }

            // Toggle dropdown menu
            function toggleDropdown(event) {
                event.preventDefault();
                const targetId = this.getAttribute('data-target');
                const dropdownMenu = document.getElementById(targetId);
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('active');
                        menu.previousElementSibling.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                this.classList.toggle('active');
                dropdownMenu.classList.toggle('active');
            }

            // Set active page
            function setActivePage(event) {
                const page = this.getAttribute('data-page');
                
                // Update active states
                navLinks.forEach(link => link.classList.remove('active'));
                dropdownItems.forEach(item => item.classList.remove('active'));
                footerItems.forEach(item => item.classList.remove('active'));
                
                this.classList.add('active');
                
                // Sync with footer
                const correspondingFooterItem = document.querySelector(`.footer-item[data-page="${page}"]`);
                if (correspondingFooterItem) {
                    correspondingFooterItem.classList.add('active');
                }
                
                // Close sidebar on mobile after selection
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            }

            // Add scroll effect to navbar
            function handleScroll() {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }

            // Event Listeners
            navbarToggler.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', closeSidebar);
            logoutBtn.addEventListener('click', () => {
                // Simulate logout process
                logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
                setTimeout(() => {
                    alert('You have been logged out successfully.');
                    logoutBtn.innerHTML = '<i class="fas fa-sign-out-alt"></i> <span>Logout</span>';
                }, 1500);
            });
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', toggleDropdown);
            });
            
            navLinks.forEach(link => {
                link.addEventListener('click', setActivePage);
            });
            
            dropdownItems.forEach(item => {
                item.addEventListener('click', setActivePage);
            });
            
            footerItems.forEach(item => {
                item.addEventListener('click', setActivePage);
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.nav-item')) {
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        menu.classList.remove('active');
                    });
                    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                        toggle.classList.remove('active');
                    });
                }
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            // Handle window resize
            function handleResize() {
                if (window.innerWidth >= 992 && sidebar.classList.contains('active')) {
                    document.body.style.paddingLeft = '280px';
                } else {
                    document.body.style.paddingLeft = '0';
                }
            }

            // Initialize
            window.addEventListener('scroll', handleScroll);
            window.addEventListener('resize', handleResize);
            
            // Add animation to cards on load
            setTimeout(() => {
                document.querySelectorAll('.card, .stat-card').forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            }, 300);
        });
    </script>
</body>
</html>