<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPortal - Student Dashboard</title>
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

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(76, 201, 240, 0.1));
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(67, 97, 238, 0.1);
        }

        .welcome-text h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .welcome-text p {
            color: var(--medium-gray);
            font-size: 0.95rem;
        }

        .welcome-text .highlight {
            color: var(--primary);
            font-weight: 600;
        }

        .user-avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--white);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
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

        /* Assignments Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--dark);
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: var(--transition);
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .assignment-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
        }

        .assignment-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }

        .assignment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .assignment-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .assignment-due {
            font-size: 0.8rem;
            color: var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .assignment-description {
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .assignment-status {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-submitted {
            background-color: rgba(46, 196, 182, 0.1);
            color: #2ec4b6;
        }

        .status-pending {
            background-color: rgba(239, 35, 60, 0.1);
            color: #ef233c;
        }

        .status-graded {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .assignment-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
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

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        /* Calendar Section */
        .calendar-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .calendar-title {
            font-size: 1.25rem;
            color: var(--dark);
        }

        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }

        .calendar-nav-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .calendar-nav-btn:hover {
            background: var(--primary);
            color: white;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--medium-gray);
            padding: 0.5rem;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .calendar-day:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        .calendar-day.today {
            background: var(--primary);
            color: white;
        }

        .calendar-day.event {
            position: relative;
        }

        .calendar-day.event::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--accent);
        }

        .calendar-day.other-month {
            color: var(--light-gray);
        }

        /* Upcoming Events */
        .events-list {
            list-style: none;
        }

        .event-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .event-item:last-child {
            border-bottom: none;
        }

        .event-date {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius);
            background-color: rgba(67, 97, 238, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        .event-day {
            font-weight: 700;
            font-size: 1.1rem;
            line-height: 1;
        }

        .event-month {
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .event-content {
            flex: 1;
        }

        .event-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .event-time {
            font-size: 0.85rem;
            color: var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-time i {
            font-size: 0.8rem;
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
            
            .welcome-card {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
            
            .welcome-text {
                order: 2;
            }
            
            .user-avatar-lg {
                order: 1;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .assignments-grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .assignment-actions {
                flex-direction: column;
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
                <i class="fas fa-graduation-cap"></i>
                <span>EduPortal</span>
            </a>
        </div>
        
        <div class="user-profile">
            <div class="user-info">
                <div class="user-name"><?php echo isset($student['name']) ? htmlspecialchars($student['name']) : 'Student'; ?></div>
                <div class="user-role">Student</div>
            </div>
            <img src="<?php echo isset($student['photo']) ? 'uploads/'.htmlspecialchars($student['photo']) : 'https://ui-avatars.com/api/?name='.urlencode($student['name'] ?? 'Student').'&background=4361ee&color=fff'; ?>" 
                 class="user-avatar" 
                 alt="User Avatar">
        </div>
    </nav>

    <!-- Sidebar - Initially closed -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?php echo isset($student['photo']) ? 'uploads/'.htmlspecialchars($student['photo']) : 'https://ui-avatars.com/api/?name='.urlencode($student['name'] ?? 'Student').'&background=4361ee&color=fff'; ?>" 
                 class="sidebar-user-avatar" 
                 alt="User Avatar">
            <div class="sidebar-user-info">
                <h4><?php echo isset($student['name']) ? htmlspecialchars($student['name']) : 'Student'; ?></h4>
                <p><?php echo isset($student['enrollment_id']) ? htmlspecialchars($student['enrollment_id']) : 'ID: N/A'; ?></p>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="nav-item">
                <a href="#" class="nav-link active" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <div class="nav-item">
                <div class="nav-link dropdown-toggle" data-target="assignments-menu">
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
                </div>
                <ul class="dropdown-menu" id="assignments-menu">
                    <li><a href="#" class="dropdown-item" data-page="my-assignments"><i class="fas fa-list"></i> My Assignments</a></li>
                    <li><a href="#" class="dropdown-item" data-page="due-dates"><i class="fas fa-calendar"></i> Due Dates</a></li>
                    <li><a href="#" class="dropdown-item" data-page="submit-work"><i class="fas fa-upload"></i> Submit Work</a></li>
                </ul>
            </div>
            
            <div class="nav-item">
                <div class="nav-link dropdown-toggle" data-target="study-center-menu">
                    <i class="fas fa-book-open"></i>
                    <span>Study Center</span>
                </div>
                <ul class="dropdown-menu" id="study-center-menu">
                    <li><a href="#" class="dropdown-item" data-page="courses"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="#" class="dropdown-item" data-page="lectures"><i class="fas fa-video"></i> Lectures</a></li>
                    <li><a href="#" class="dropdown-item" data-page="materials"><i class="fas fa-file-pdf"></i> Materials</a></li>
                    <li><a href="#" class="dropdown-item" data-page="tutoring"><i class="fas fa-chalkboard-teacher"></i> Tutoring</a></li>
                </ul>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link" data-page="grades">
                    <i class="fas fa-chart-line"></i>
                    <span>Grades</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link" data-page="notifications">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="badge">3</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link" data-page="messages">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link" data-page="calendar">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendar</span>
                </a>
            </div>
        </div>
        
        <div class="sidebar-footer">
            <button class="logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h2>Welcome back, <?php echo isset($student['name']) ? htmlspecialchars($student['name']) : 'Student'; ?>!</h2>
                <p>You're enrolled in <span class="highlight"><?php echo isset($student['course']) ? htmlspecialchars($student['course']) : 'your course'; ?></span> with ID <span class="highlight"><?php echo isset($student['enrollment_id']) ? htmlspecialchars($student['enrollment_id']) : 'N/A'; ?></span></p>
            </div>
            <img src="<?php echo isset($student['photo']) ? 'uploads/'.htmlspecialchars($student['photo']) : 'https://ui-avatars.com/api/?name='.urlencode($student['name'] ?? 'Student').'&background=4361ee&color=fff'; ?>" 
                 class="user-avatar-lg" 
                 alt="User Avatar">
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card courses">
                <div class="stat-title">
                    <i class="fas fa-book"></i>
                    Active Courses
                </div>
                <div class="stat-value">5</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>2 this semester</span>
                </div>
            </div>
            
            <div class="stat-card assignments">
                <div class="stat-title">
                    <i class="fas fa-tasks"></i>
                    Pending Assignments
                </div>
                <div class="stat-value">3</div>
                <div class="stat-change negative">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>1 overdue</span>
                </div>
            </div>
            
            <div class="stat-card grades">
                <div class="stat-title">
                    <i class="fas fa-chart-line"></i>
                    Average Grade
                </div>
                <div class="stat-value">87%</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>5% from last term</span>
                </div>
            </div>
            
            <div class="stat-card messages">
                <div class="stat-title">
                    <i class="fas fa-envelope"></i>
                    Unread Messages
                </div>
                <div class="stat-value">2</div>
                <div class="stat-change">
                    <i class="fas fa-circle"></i>
                    <span>From instructors</span>
                </div>
            </div>
        </div>
        
        <!-- Assignments Section -->
        <div class="section-header">
            <h3 class="section-title">Recent Assignments</h3>
            <a href="#" class="view-all">
                View all
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        
        <div class="assignments-grid">
            <?php
            if ($assignments && $assignments->num_rows > 0) {
                while($assignment = $assignments->fetch_assoc()) {
                    $status_class = '';
                    $status_text = '';
                    
                    if ($assignment['marks_awarded'] !== null) {
                        $status_class = 'status-graded';
                        $status_text = 'Graded: ' . $assignment['marks_awarded'] . '/100';
                    } elseif ($assignment['submission_id']) {
                        $status_class = 'status-submitted';
                        $status_text = 'Submitted';
                    } else {
                        $status_class = 'status-pending';
                        $status_text = 'Pending';
                    }
                    
                    $due_date = new DateTime($assignment['due_date']);
                    $now = new DateTime();
                    $is_overdue = $now > $due_date && !$assignment['submission_id'];
                    ?>
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <h4 class="assignment-title"><?php echo htmlspecialchars($assignment['title']); ?></h4>
                            <div class="assignment-due">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo $due_date->format('M j, Y'); ?>
                                <?php if ($is_overdue): ?>
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="assignment-description">
                            <?php echo htmlspecialchars(substr($assignment['question_text'], 0, 100)); ?>
                            <?php if (strlen($assignment['question_text']) > 100): ?>...<?php endif; ?>
                        </p>
                        <span class="assignment-status <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                        <div class="assignment-actions">
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                            <?php if (!$assignment['submission_id']): ?>
                                <button class="btn btn-outline btn-sm">
                                    <i class="fas fa-upload"></i>
                                    Submit
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No assignments found.</p>';
            }
            ?>
        </div>
        
        <!-- Calendar and Events Section -->
        <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div class="calendar-card">
                <div class="calendar-header">
                    <h4 class="calendar-title">July 2023</h4>
                    <div class="calendar-nav">
                        <button class="calendar-nav-btn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="calendar-nav-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
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
                    
                    <!-- Calendar days would be generated dynamically in a real app -->
                    <div class="calendar-day other-month">25</div>
                    <div class="calendar-day other-month">26</div>
                    <div class="calendar-day other-month">27</div>
                    <div class="calendar-day other-month">28</div>
                    <div class="calendar-day other-month">29</div>
                    <div class="calendar-day">1</div>
                    <div class="calendar-day">2</div>
                    <div class="calendar-day">3</div>
                    <div class="calendar-day">4</div>
                    <div class="calendar-day">5</div>
                    <div class="calendar-day">6</div>
                    <div class="calendar-day">7</div>
                    <div class="calendar-day">8</div>
                    <div class="calendar-day today">9</div>
                    <div class="calendar-day event">10</div>
                    <div class="calendar-day">11</div>
                    <div class="calendar-day">12</div>
                    <div class="calendar-day event">13</div>
                    <div class="calendar-day">14</div>
                    <div class="calendar-day">15</div>
                    <div class="calendar-day">16</div>
                    <div class="calendar-day">17</div>
                    <div class="calendar-day">18</div>
                    <div class="calendar-day">19</div>
                    <div class="calendar-day">20</div>
                    <div class="calendar-day">21</div>
                    <div class="calendar-day">22</div>
                    <div class="calendar-day">23</div>
                    <div class="calendar-day">24</div>
                    <div class="calendar-day">25</div>
                    <div class="calendar-day">26</div>
                    <div class="calendar-day">27</div>
                    <div class="calendar-day">28</div>
                    <div class="calendar-day">29</div>
                    <div class="calendar-day">30</div>
                    <div class="calendar-day">31</div>
                    <div class="calendar-day other-month">1</div>
                </div>
            </div>
            
            <div class="card">
                <div class="section-header">
                    <h4 class="section-title">Upcoming Events</h4>
                    <a href="#" class="view-all">
                        View all
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <ul class="events-list">
                    <li class="event-item">
                        <div class="event-date">
                            <div class="event-day">10</div>
                            <div class="event-month">Jul</div>
                        </div>
                        <div class="event-content">
                            <h5 class="event-title">Mathematics Quiz</h5>
                            <div class="event-time">
                                <i class="far fa-clock"></i>
                                10:00 AM - 11:30 AM
                            </div>
                        </div>
                    </li>
                    <li class="event-item">
                        <div class="event-date">
                            <div class="event-day">13</div>
                            <div class="event-month">Jul</div>
                        </div>
                        <div class="event-content">
                            <h5 class="event-title">Assignment Due: History Essay</h5>
                            <div class="event-time">
                                <i class="far fa-clock"></i>
                                11:59 PM
                            </div>
                        </div>
                    </li>
                    <li class="event-item">
                        <div class="event-date">
                            <div class="event-day">15</div>
                            <div class="event-month">Jul</div>
                        </div>
                        <div class="event-content">
                            <h5 class="event-title">Science Lab Session</h5>
                            <div class="event-time">
                                <i class="far fa-clock"></i>
                                2:00 PM - 4:00 PM
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </main>
    
    <!-- Mobile Footer -->
    <footer class="mobile-footer">
        <a href="#" class="footer-item active" data-page="dashboard">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="#" class="footer-item" data-page="assignments">
            <i class="fas fa-tasks"></i>
            <span>Assignments</span>
        </a>
        <a href="#" class="footer-item" data-page="study">
            <i class="fas fa-book"></i>
            <span>Study</span>
        </a>
        <a href="#" class="footer-item" data-page="messages">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
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
                event.preventDefault();
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