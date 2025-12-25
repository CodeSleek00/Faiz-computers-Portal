<?php
include("db_connect.php");

// Get search parameter if exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with search filter
$query = "
    SELECT DISTINCT enrollment_id, name, photo, course_name, 
           , phone, created_at
    FROM student_monthly_fee
";

if (!empty($search)) {
    $query .= " WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR course_name LIKE '%" . $conn->real_escape_string($search) . "%'
                OR enrollment_id LIKE '%" . $conn->real_escape_string($search) . "%'
                OR  LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$query .= " ORDER BY created_at DESC";

$students = $conn->query($query);

// Get statistics
$statsQuery = "SELECT 
    COUNT(DISTINCT enrollment_id) as total_students,
    COUNT(DISTINCT course_name) as total_courses,
    SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) as new_this_month,
    (SELECT COUNT(DISTINCT enrollment_id) FROM student_monthly_fee WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_this_week
    FROM student_monthly_fee";

$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// For filtered count
if (!empty($search)) {
    $filteredQuery = "SELECT COUNT(DISTINCT enrollment_id) as filtered FROM student_monthly_fee 
                      WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                      OR course_name LIKE '%" . $conn->real_escape_string($search) . "%'
                      OR enrollment_id LIKE '%" . $conn->real_escape_string($search) . "%'
                      OR email LIKE '%" . $conn->real_escape_string($search) . "%'";
    $filteredResult = $conn->query($filteredQuery);
    $filteredStudents = $filteredResult->fetch_assoc()['filtered'];
} else {
    $filteredStudents = $stats['total_students'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Management Dashboard | Institute</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #e0e7ff;
            --secondary: #10b981;
            --secondary-dark: #0da271;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            --dark: #1e293b;
            --dark-light: #334155;
            --light: #f8fafc;
            --gray: #94a3b8;
            --gray-light: #e2e8f0;
            --white: #ffffff;
            
            --sidebar-width: 260px;
            --header-height: 70px;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--dark) 0%, #1a1f35 100%);
            color: var(--white);
            position: fixed;
            height: 100vh;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-lg);
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .logo-text {
            font-size: 22px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu {
            flex: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            margin-bottom: 8px;
            border-radius: var(--border-radius-sm);
            color: var(--gray);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .nav-item.active {
            background: var(--primary);
            color: var(--white);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            border-radius: var(--border-radius-sm);
            background: rgba(255, 255, 255, 0.05);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-info h4 {
            font-size: 14px;
            font-weight: 600;
        }

        .user-info p {
            font-size: 12px;
            color: var(--gray);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--white);
            padding: 20px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .page-title p {
            color: var(--gray);
            font-size: 14px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 300px;
            padding: 12px 20px 12px 45px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--dark);
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.students { border-left-color: var(--primary); }
        .stat-card.courses { border-left-color: var(--secondary); }
        .stat-card.week { border-left-color: var(--warning); }
        .stat-card.month { border-left-color: var(--info); }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card.students .stat-icon { background: var(--primary-light); color: var(--primary); }
        .stat-card.courses .stat-icon { background: #d1fae5; color: var(--secondary); }
        .stat-card.week .stat-icon { background: #fef3c7; color: var(--warning); }
        .stat-card.month .stat-icon { background: #dbeafe; color: var(--info); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }

        .stat-change {
            font-size: 12px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-change.positive { color: var(--secondary); }
        .stat-change.negative { color: var(--danger); }

        /* Main Content Card */
        .content-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .card-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            background: var(--white);
            color: var(--dark);
            font-size: 14px;
        }

        .export-btn {
            padding: 10px 20px;
            background: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            color: var(--dark);
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .export-btn:hover {
            background: var(--light);
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background-color: #f8fafc;
        }

        .data-table th {
            padding: 18px 30px;
            text-align: left;
            font-weight: 600;
            color: var(--dark-light);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-light);
            white-space: nowrap;
        }

        .data-table td {
            padding: 20px 30px;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: background-color 0.2s;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .student-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .student-avatar {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-info h4 {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .student-info p {
            font-size: 13px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .course-tag {
            display: inline-block;
            padding: 6px 14px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.active {
            background: #d1fae5;
            color: var(--secondary);
        }

        .status-badge.pending {
            background: #fef3c7;
            color: var(--warning);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: var(--border-radius-sm);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--dark);
            border: 1px solid var(--gray-light);
        }

        .btn-outline:hover {
            background: var(--light);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
        }

        .empty-state i {
            font-size: 60px;
            color: var(--gray-light);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--gray);
            margin-bottom: 25px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-top: 1px solid var(--gray-light);
        }

        .pagination-info {
            color: var(--gray);
            font-size: 14px;
        }

        .pagination-controls {
            display: flex;
            gap: 10px;
        }

        .page-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--gray-light);
            background: var(--white);
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s;
        }

        .page-btn:hover {
            background: var(--light);
        }

        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Footer */
        .dashboard-footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--gray-light);
            margin-top: 30px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            
            .logo-text, .nav-item span, .user-info {
                display: none;
            }
            
            .logo {
                justify-content: center;
                padding: 0 0 30px;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .nav-item {
                justify-content: center;
                padding: 15px;
            }
            
            .user-profile {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px 15px;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .search-box input {
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .card-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .data-table th, .data-table td {
                padding: 15px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="logo-text">EduManage</div>
        </div>
        
        <nav class="nav-menu">
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-user-graduate"></i>
                <span>Students</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-money-check-alt"></i>
                <span>Fee Management</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-question-circle"></i>
                <span>Help Center</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info">
                    <h4>Admin User</h4>
                    <p>Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar fade-in">
            <div class="page-title">
                <h1>Student Fee Management</h1>
                <p>Manage student fees, view statistics, and process payments</p>
            </div>
            
            <div class="top-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <form method="GET" action="" style="display: inline;">
                        <input type="text" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
                
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid fade-in">
            <div class="stat-card students">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['total_students']; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>12% from last month</span>
                </div>
            </div>
            
            <div class="stat-card courses">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                        <div class="stat-label">Active Courses</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>3 new this quarter</span>
                </div>
            </div>
            
            <div class="stat-card week">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['new_this_week']; ?></div>
                        <div class="stat-label">New This Week</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>2 from yesterday</span>
                </div>
            </div>
            
            <div class="stat-card month">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $stats['new_this_month']; ?></div>
                        <div class="stat-label">New This Month</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-change <?php echo $stats['new_this_month'] > 10 ? 'positive' : 'negative'; ?>">
                    <i class="fas fa-arrow-<?php echo $stats['new_this_month'] > 10 ? 'up' : 'down'; ?>"></i>
                    <span><?php echo $stats['new_this_month'] > 10 ? '15% from last month' : '5% from last month'; ?></span>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="content-card fade-in">
            <div class="card-header">
                <h2>Student Records</h2>
                <div class="card-actions">
                    <select class="filter-select">
                        <option>All Courses</option>
                        <option>Computer Science</option>
                        <option>Business Administration</option>
                        <option>Engineering</option>
                    </select>
                    
                    <button class="export-btn">
                        <i class="fas fa-download"></i>
                        Export Data
                    </button>
                    
                    <?php if (!empty($search)): ?>
                    <a href="?" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Clear Search
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="table-responsive">
                <?php if ($filteredStudents > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Enrollment ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $students->fetch_assoc()): ?>
                        <tr class="slide-in">
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">
                                        <img 
                                            src="../uploads/<?= htmlspecialchars($s['photo']) ?>" 
                                            alt="<?= htmlspecialchars($s['name']) ?>"
                                            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($s['name']) ?>&background=4f46e5&color=fff&size=150&font-size=0.4&bold=true'"
                                        >
                                    </div>
                                    <div class="student-info">
                                        <h4><?= htmlspecialchars($s['name']) ?></h4>
                                        <p>
                                            <i class="fas fa-envelope"></i>
                                            <?= htmlspecialchars($s['email'] ?? 'N/A') ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="course-tag"><?= htmlspecialchars($s['course_name']) ?></span>
                            </td>
                            <td>
                                <span class="student-id"><?= htmlspecialchars($s['enrollment_id']) ?></span>
                            </td>
                            <td>
                                <span class="status-badge active">Active</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-credit-card"></i>
                                        Pay Fee
                                    </a>
                                    <a href="#" class="btn btn-outline">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No students found</h3>
                    <p>
                        <?php if (!empty($search)): ?>
                        No students match your search criteria "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php else: ?>
                        No student records available in the database. Add your first student to get started.
                        <?php endif; ?>
                    </p>
                    <a href="?" class="btn btn-primary">
                        <i class="fas fa-users"></i>
                        View All Students
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($filteredStudents > 0): ?>
            <div class="pagination">
                <div class="pagination-info">
                    Showing <?php echo min($filteredStudents, 10); ?> of <?php echo $filteredStudents; ?> students
                    <?php if (!empty($search)): ?>
                    <span style="color: var(--primary); margin-left: 10px;">
                        <i class="fas fa-search"></i> Search results: <?php echo $filteredStudents; ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="pagination-controls">
                    <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> EduManage Institute. All rights reserved. | v2.1.0</p>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight search terms
            const searchTerm = "<?php echo addslashes($search); ?>";
            if (searchTerm.trim() !== '') {
                const elements = document.querySelectorAll('.student-info h4, .course-tag, .student-id');
                elements.forEach(element => {
                    const original = element.textContent;
                    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    const highlighted = original.replace(regex, '<span style="background-color: #FFEB3B; color: #000; padding: 2px 4px; border-radius: 3px; font-weight: bold;">$1</span>');
                    if (highlighted !== original) {
                        element.innerHTML = highlighted;
                    }
                });
            }
            
            // Add animation to table rows
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
            
            // Search box focus
            const searchInput = document.querySelector('.search-box input');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            
            // Export button functionality
            document.querySelector('.export-btn').addEventListener('click', function() {
                alert('Export functionality would be implemented here.');
            });
            
            // Notification bell
            document.querySelector('.notification-btn').addEventListener('click', function() {
                alert('Notifications panel would open here.');
            });
        });
    </script>
</body>
</html>