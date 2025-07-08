<?php
include 'database_connection/db_connect.php';

// Fetch all statistics in a single query for better performance
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM students) AS students,
        (SELECT COUNT(*) FROM batches) AS batches,
        (SELECT COUNT(*) FROM exams) AS exams,
        (SELECT COUNT(*) FROM assignments) AS assignments,
        (SELECT COUNT(*) FROM study_materials) AS materials,
        (SELECT COUNT(*) FROM results) AS results
")->fetch_assoc();

// Get recent activity (example query - adjust according to your actual tables)
$recentActivity = [];
$activityQuery = $conn->query("
    (SELECT 'exam' AS type, exam_name AS title, created_at FROM exams ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'assignment' AS type, title, created_at FROM assignments ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'material' AS type, title, uploaded_at AS created_at FROM study_materials ORDER BY uploaded_at DESC LIMIT 3)
    ORDER BY created_at DESC LIMIT 5
");
while ($row = $activityQuery->fetch_assoc()) {
    $recentActivity[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Learning Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: var(--white);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            padding: 0 1.5rem;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--primary);
        }
        
        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--gray);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        
        .nav-link i {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }
        
        /* Main Content Styles */
        .main-content {
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .page-title p {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .user-info h4 {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 2px;
        }
        
        .user-info p {
            font-size: 0.75rem;
            color: var(--gray);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon.students { background-color: #e0f4ff; color: #00a8ff; }
        .stat-icon.batches { background-color: #e0f7fa; color: #00bcd4; }
        .stat-icon.exams { background-color: #f1f8e9; color: #8bc34a; }
        .stat-icon.assignments { background-color: #fff8e1; color: #ffc107; }
        .stat-icon.materials { background-color: #f3e5f5; color: #9c27b0; }
        .stat-icon.results { background-color: #e8f5e9; color: #4caf50; }
        
        .stat-icon i {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .stat-title {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .stat-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
        }
        
        .stat-change.up {
            color: #4caf50;
        }
        
        .stat-change.down {
            color: #f44336;
        }
        
        /* Main Grid Layout */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }
        
        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Chart Section */
        .chart-container {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .chart-wrapper {
            height: 300px;
            position: relative;
        }
        
        /* Recent Activity */
        .activity-item {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .activity-icon.exam { background-color: #e8f5e9; color: #4caf50; }
        .activity-icon.assignment { background-color: #fff8e1; color: #ffc107; }
        .activity-icon.material { background-color: #e3f2fd; color: #2196f3; }
        
        .activity-content h4 {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .activity-content p {
            font-size: 0.75rem;
            color: var(--gray);
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
        }
        
        .activity-time i {
            width: 14px;
            height: 14px;
            margin-right: 4px;
            stroke-width: 2;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .action-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            background-color: var(--primary-light);
            color: var(--primary);
        }
        
        .action-icon i {
            width: 24px;
            height: 24px;
            stroke-width: 2;
        }
        
        .action-title {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .action-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .action-btn:hover {
            background: var(--secondary);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                height: auto;
                position: relative;
                padding: 1rem 0;
            }
            
            .logo {
                justify-content: flex-start;
                padding: 0 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-profile {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i data-feather="book-open" style="color: var(--primary);"></i>
                <span class="logo-text">EduAdmin</span>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i data-feather="home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="users"></i>
                        Students
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="layers"></i>
                        Batches
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="file-text"></i>
                        Exams
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="edit"></i>
                        Assignments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="book"></i>
                        Study Materials
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="award"></i>
                        Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-feather="settings"></i>
                        Settings
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Dashboard Overview</h1>
                    <p>Welcome back, Admin! Here's what's happening with your institution.</p>
                </div>
                
                <div class="user-profile">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Admin User">
                    <div class="user-info">
                        <h4>Admin User</h4>
                        <p>Super Admin</p>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon students">
                            <i data-feather="users"></i>
                        </div>
                        <span class="stat-change up">+12% <i data-feather="arrow-up"></i></span>
                    </div>
                    <div class="stat-value"><?= $stats['students'] ?></div>
                    <div class="stat-title">Total Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon batches">
                            <i data-feather="layers"></i>
                        </div>
                        <span class="stat-change up">+5% <i data-feather="arrow-up"></i></span>
                    </div>
                    <div class="stat-value"><?= $stats['batches'] ?></div>
                    <div class="stat-title">Active Batches</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon exams">
                            <i data-feather="file-text"></i>
                        </div>
                        <span class="stat-change up">+3 <i data-feather="arrow-up"></i></span>
                    </div>
                    <div class="stat-value"><?= $stats['exams'] ?></div>
                    <div class="stat-title">Upcoming Exams</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon assignments">
                            <i data-feather="edit"></i>
                        </div>
                        <span class="stat-change down">-2 <i data-feather="arrow-down"></i></span>
                    </div>
                    <div class="stat-value"><?= $stats['assignments'] ?></div>
                    <div class="stat-title">Pending Assignments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon materials">
                            <i data-feather="book"></i>
                        </div>
                        <span class="stat-change up">+8 <i data-feather="arrow-up"></i></span>
                    </div>
                    <div class="stat-value"><?= $stats['materials'] ?></div>
                    <div class="stat-title">Study Materials</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon results">
                            <i data-feather="award"></i>
                        </div>
                        <span class="stat-change up">+24% <i data-feather="arrow-up"></i></span>
                    </div>
                    <div class="stat-value"><?= $stats['results'] ?></div>
                    <div class="stat-title">Results Published</div>
                </div>
            </div>
            
            <!-- Main Grid -->
            <div class="main-grid">
                <!-- Left Column -->
                <div>
                    <!-- Chart Section -->
                    <div class="chart-container" style="margin-bottom: 1.5rem;">
                        <div class="section-header">
                            <h2 class="section-title">Student Performance</h2>
                            <select style="padding: 0.5rem; border-radius: 6px; border: 1px solid var(--gray-light);">
                                <option>Last 7 Days</option>
                                <option>Last 30 Days</option>
                                <option>Last 3 Months</option>
                                <option>This Year</option>
                            </select>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="chart-container">
                        <div class="section-header">
                            <h2 class="section-title">Quick Actions</h2>
                        </div>
                        <div class="quick-actions">
                            <div class="action-card">
                                <div class="action-icon">
                                    <i data-feather="file-text"></i>
                                </div>
                                <h3 class="action-title">Create New Exam</h3>
                                <a href="create_exam.php" class="action-btn">Create Now</a>
                            </div>
                            
                            <div class="action-card">
                                <div class="action-icon">
                                    <i data-feather="edit"></i>
                                </div>
                                <h3 class="action-title">Add Assignment</h3>
                                <a href="../assignments/admin_assignments.php" class="action-btn">Add Now</a>
                            </div>
                            
                            <div class="action-card">
                                <div class="action-icon">
                                    <i data-feather="book"></i>
                                </div>
                                <h3 class="action-title">Upload Material</h3>
                                <a href="../study-center/upload_material.php" class="action-btn">Upload Now</a>
                            </div>
                            
                            <div class="action-card">
                                <div class="action-icon">
                                    <i data-feather="award"></i>
                                </div>
                                <h3 class="action-title">Declare Results</h3>
                                <a href="declare_result.php" class="action-btn">Declare Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <!-- Recent Activity -->
                    <div class="chart-container">
                        <div class="section-header">
                            <h2 class="section-title">Recent Activity</h2>
                            <a href="#" style="font-size: 0.875rem; color: var(--primary); text-decoration: none;">View All</a>
                        </div>
                        
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?= $activity['type'] ?>">
                                <i data-feather="<?= 
                                    $activity['type'] == 'exam' ? 'file-text' : 
                                    ($activity['type'] == 'assignment' ? 'edit' : 'book') 
                                ?>"></i>
                            </div>
                            <div class="activity-content">
                                <h4><?= $activity['title'] ?></h4>
                                <p><?= ucfirst($activity['type']) ?> <?= 
                                    $activity['type'] == 'exam' ? 'created' : 
                                    ($activity['type'] == 'assignment' ? 'assigned' : 'uploaded') 
                                ?></p>
                                <div class="activity-time">
                                    <i data-feather="clock"></i>
                                    <?= date('M j, Y', strtotime($activity['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Initialize Feather Icons
        feather.replace();
        
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Average Score',
                    data: [65, 59, 80, 81, 56, 72, 85],
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Pass Percentage',
                    data: [82, 85, 78, 89, 76, 88, 92],
                    backgroundColor: 'rgba(76, 201, 240, 0.1)',
                    borderColor: 'rgba(76, 201, 240, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        
        // Pie Chart for Content Distribution (example)
        // You can add another chart if needed
    </script>
</body>
</html>