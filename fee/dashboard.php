<?php
include("db_connect.php");

// Get search parameter if exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query with search filter
$query = "
    SELECT DISTINCT enrollment_id, name, photo, course_name
    FROM student_monthly_fee
";

if (!empty($search)) {
    $query .= " WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR course_name LIKE '%" . $conn->real_escape_string($search) . "%'
                OR enrollment_id LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$query .= " ORDER BY name ASC";

$students = $conn->query($query);

// Get total count of students
$countQuery = "SELECT COUNT(DISTINCT enrollment_id) as total FROM student_monthly_fee";
$countResult = $conn->query($countQuery);
$totalStudents = $countResult->fetch_assoc()['total'];

// For filtered count
if (!empty($search)) {
    $filteredQuery = "SELECT COUNT(DISTINCT enrollment_id) as filtered FROM student_monthly_fee 
                      WHERE name LIKE '%" . $conn->real_escape_string($search) . "%' 
                      OR course_name LIKE '%" . $conn->real_escape_string($search) . "%'
                      OR enrollment_id LIKE '%" . $conn->real_escape_string($search) . "%'";
    $filteredResult = $conn->query($filteredQuery);
    $filteredStudents = $filteredResult->fetch_assoc()['filtered'];
} else {
    $filteredStudents = $totalStudents;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Management Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #eef2ff;
            --secondary: #10b981;
            --secondary-dark: #059669;
            --accent: #f59e0b;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            --dark: #1f2937;
            --dark-light: #374151;
            --light: #f9fafb;
            --gray: #9ca3af;
            --gray-light: #e5e7eb;
            --white: #ffffff;
            
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--dark);
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header */
        .dashboard-header {
            margin-bottom: 30px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .logo-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .logo-text p {
            color: var(--gray);
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--white);
            border: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s;
        }

        .notification-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            background: var(--white);
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-light);
            cursor: pointer;
            transition: all 0.3s;
        }

        .user-profile:hover {
            box-shadow: var(--shadow-md);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-info p {
            font-size: 12px;
            color: var(--gray);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-light);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-info h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: var(--secondary);
            font-weight: 500;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 24px;
        }

        /* Search Section */
        .search-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-light);
        }

        .search-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-input-container {
            flex: 1;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            font-size: 16px;
            font-weight: 400;
            color: var(--dark);
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 16px 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .clear-button {
            background: var(--white);
            color: var(--danger);
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius-sm);
            padding: 16px 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .clear-button:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }

        /* Main Content */
        .content-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid var(--gray-light);
        }

        .card-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--gray-light);
            background: var(--light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 i {
            color: var(--primary);
        }

        .card-info {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
        }

        .card-info .highlight {
            color: var(--primary);
            font-weight: 600;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: var(--light);
        }

        .data-table th {
            padding: 20px 30px;
            text-align: left;
            font-weight: 600;
            color: var(--dark-light);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-light);
            white-space: nowrap;
        }

        .data-table th:first-child {
            border-radius: var(--border-radius) 0 0 0;
        }

        .data-table th:last-child {
            border-radius: 0 var(--border-radius) 0 0;
        }

        .data-table td {
            padding: 20px 30px;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: all 0.3s;
        }

        .data-table tbody tr:hover {
            background: var(--primary-light);
            transform: scale(1.01);
        }

        .student-info-cell {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .student-avatar {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            border: 3px solid var(--primary-light);
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-details h4 {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .student-details p {
            font-size: 13px;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .student-id {
            font-family: 'Courier New', monospace;
            background: var(--light);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .course-badge {
            display: inline-block;
            padding: 8px 18px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        .action-button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
        }

        .action-button i {
            font-size: 12px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 30px;
        }

        .empty-state-icon {
            width: 100px;
            height: 100px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 48px;
            color: var(--primary);
        }

        .empty-state h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 16px;
            max-width: 500px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .empty-state .search-term {
            color: var(--primary);
            font-weight: 600;
            background: var(--primary-light);
            padding: 2px 8px;
            border-radius: 4px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .back-button:hover {
            gap: 15px;
        }

        /* Footer */
        .dashboard-footer {
            text-align: center;
            padding: 30px;
            color: var(--gray);
            font-size: 14px;
            border-top: 1px solid var(--gray-light);
            margin-top: 30px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            gap: 30px;
        }

        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-container {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-button, .clear-button {
                width: 100%;
                justify-content: center;
            }
            
            .card-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .student-info-cell {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .data-table th, .data-table td {
                padding: 15px;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 20px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .slide-in-right {
            animation: slideInRight 0.5s ease-out;
        }

        .stagger-delay-1 { animation-delay: 0.1s; }
        .stagger-delay-2 { animation-delay: 0.2s; }
        .stagger-delay-3 { animation-delay: 0.3s; }
        .stagger-delay-4 { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header fade-in-up">
            <div class="header-top">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="logo-text">
                        <h1>Student Fee Management</h1>
                        <p>Professional Dashboard for Institute Administration</p>
                    </div>
                </div>
                
                <div class="header-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    
                    <div class="user-profile">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <h4>Institute Admin</h4>
                            <p>Administrator</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up stagger-delay-1">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>Total Students</h3>
                        <div class="stat-value"><?php echo $totalStudents; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            <span>12% from last month</span>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($search)): ?>
            <div class="stat-card fade-in-up stagger-delay-2">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>Search Results</h3>
                        <div class="stat-value"><?php echo $filteredStudents; ?></div>
                        <div class="stat-change">
                            <i class="fas fa-search"></i>
                            <span><?php echo round(($filteredStudents / $totalStudents) * 100, 1); ?>% match rate</span>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card fade-in-up stagger-delay-3">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>Search Term</h3>
                        <div class="stat-value" style="font-size: 24px;">"<?php echo htmlspecialchars($search); ?>"</div>
                        <div class="stat-change">
                            <i class="fas fa-filter"></i>
                            <span>Active search filter</span>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Search Section -->
        <div class="search-section fade-in-up stagger-delay-2">
            <div class="search-container">
                <form method="GET" action="" class="search-form">
                    <div class="search-input-container">
                        <i class="fas fa-search search-icon"></i>
                        <input 
                            type="text" 
                            name="search" 
                            class="search-input" 
                            placeholder="Search students by name, course, or enrollment ID..." 
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="?" class="clear-button">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-card fade-in-up stagger-delay-3">
            <div class="card-header">
                <h2>
                    <i class="fas fa-users"></i>
                    Student Records
                </h2>
                <div class="card-info">
                    Showing 
                    <span class="highlight"><?php echo $filteredStudents; ?> students</span>
                    <?php if (!empty($search)): ?>
                    matching your search criteria
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="table-container">
                <?php if ($filteredStudents > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Information</th>
                            <th>Course</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($s = $students->fetch_assoc()): ?>
                        <tr class="slide-in-right">
                            <td>
                                <div class="student-info-cell">
                                    <div class="student-avatar">
                                        <img 
                                            src="../uploads/<?= htmlspecialchars($s['photo']) ?>" 
                                            alt="Student Photo" 
                                            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($s['name']) ?>&background=4f46e5&color=fff&size=150&font-size=0.4&bold=true'"
                                        >
                                    </div>
                                    <div class="student-details">
                                        <h4><?= htmlspecialchars($s['name']) ?></h4>
                                        <p>
                                            <i class="fas fa-id-card"></i>
                                            <span class="student-id"><?= htmlspecialchars($s['enrollment_id']) ?></span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="course-badge"><?= htmlspecialchars($s['course_name']) ?></span>
                            </td>
                            <td style="text-align: right;">
                                <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>" class="action-button">
                                    <i class="fas fa-credit-card"></i>
                                    Submit Fee
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>No students found</h3>
                    <p>
                        <?php if (!empty($search)): ?>
                        No students match your search for <span class="search-term">"<?php echo htmlspecialchars($search); ?>"</span>
                        <?php else: ?>
                        No students are currently registered in the system
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search)): ?>
                    <a href="?" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        View all students
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-footer">
            <div class="footer-content">
                <p>© <?php echo date('Y'); ?> Institute Student Management System. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                    <a href="#"><i class="fas fa-cog"></i> Settings</a>
                    <a href="#"><i class="fas fa-shield-alt"></i> Privacy</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
            
            // Highlight search terms in table
            const searchTerm = "<?php echo addslashes($search); ?>";
            if (searchTerm.trim() !== '') {
                const cells = document.querySelectorAll('.student-details h4, .course-badge, .student-id');
                cells.forEach(cell => {
                    const original = cell.textContent;
                    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    const highlighted = original.replace(regex, '<span style="background: linear-gradient(120deg, #fef3c7 0%, #fde68a 100%); color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: 700;">$1</span>');
                    if (highlighted !== original) {
                        cell.innerHTML = highlighted;
                    }
                });
            }
            
            // Focus search input on page load
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            
            // Notification bell animation
            const notificationBtn = document.querySelector('.notification-btn');
            if (notificationBtn) {
                notificationBtn.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                        alert('Notifications panel would open here');
                    }, 200);
                });
            }
            
            // Add hover effects to table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
        });
    </script>
</body>
</html>