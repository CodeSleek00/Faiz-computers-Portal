<?php
include 'database_connection/db_connect.php';

// Fetch counts
$total_students = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$total_batches = $conn->query("SELECT COUNT(*) AS c FROM batches")->fetch_assoc()['c'];
$total_exams = $conn->query("SELECT COUNT(*) AS c FROM exams")->fetch_assoc()['c'];
$total_assignments = $conn->query("SELECT COUNT(*) AS c FROM assignments")->fetch_assoc()['c'];
$total_materials = $conn->query("SELECT COUNT(*) AS c FROM study_materials")->fetch_assoc()['c'];


/* ===== COUNT STUDENTS ===== */
$result = $conn->query("SELECT COUNT(*) AS total_students FROM students26");
$row = $result->fetch_assoc();
$total = $row['total_students'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Learning Management System</title>
     <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --success: #4cc9a0;
            --warning: #f8961e;
            --danger: #f72585;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }
        
        .dashboard {
            display: none; /* Initially hidden until password is entered */
            min-height: 100vh;
        }
        
        /* Password Modal Styles */
        #passwordModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        #passwordModal > div {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        #passwordModal h2 {
            margin-bottom: 20px;
            text-align: center;
            color: var(--primary);
        }
        
        #passwordModal label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        #adminPassword {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        #submitPassword {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        #submitPassword:hover {
            background: var(--secondary);
        }
        
        #errorMsg {
            color: var(--danger);
            text-align: center;
            margin-top: 15px;
            display: none;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: 0 0 30px rgba(0,0,0,0.03);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .sidebar-header h3 i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .sidebar-menu {
            padding: 0 15px;
        }
        
        .menu-title {
            color: var(--gray);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin: 20px 0 10px;
            padding-left: 10px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            color: #555;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background: var(--primary-light);
            color: var(--primary);
        }
        
        .menu-item i {
            margin-right: 12px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            transition: all 0.3s;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 28px;
            color: var(--dark);
            font-weight: 700;
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
        
        .user-info h5 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .user-info p {
            font-size: 12px;
            color: var(--gray);
        }
        
        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
        }
        
        .stat-card.students { border-color: var(--primary); }
        .stat-card.batches { border-color: var(--success); }
        .stat-card.exams { border-color: var(--warning); }
        .stat-card.assignments { border-color: var(--danger); }
        .stat-card.materials { border-color: var(--secondary); }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .stat-icon.students { background: var(--primary); }
        .stat-icon.batches { background: var(--success); }
        .stat-icon.exams { background: var(--warning); }
        .stat-icon.assignments { background: var(--danger); }
        .stat-icon.materials { background: var(--secondary); }
        
        .stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .stat-card p {
            color: var(--gray);
            font-size: 14px;
        }
        
        /* Quick Actions */
        .quick-actions {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: all 0.3s;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.05);
        }
        
        .action-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
        }
        
        .action-card h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .action-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .action-link {
            display: inline-block;
            padding: 8px 15px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .action-link:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Chart Section */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            margin-bottom: 30px;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .user-profile {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Password Protection Modal -->
    <div id="passwordModal">
        <div>
            <h2><i class="fas fa-lock"></i> Admin Login</h2>
            <div>
                <label for="adminPassword">Enter Password</label>
                <input type="password" id="adminPassword" placeholder="Enter admin password">
                <button id="submitPassword"><i class="fas fa-sign-in-alt"></i> Submit</button>
                <p id="errorMsg">Incorrect password. Please try again.</p>
            </div>
        </div>
    </div>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-graduation-cap"></i> FAIZ'S</h3>
            </div>
            
            <div class="sidebar-menu">
                <p class="menu-title">Main</p>
                <a href="#" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <p class="menu-title">Management</p>
                <a href="exam-center/admin/exam_dashboard.php" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Exams</span>
                </a>
                <a href="assignment/admin_assignment_dashboard.php" class="menu-item">
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
                </a>
                <a href="study-center/view_materials_admin.php" class="menu-item">
                    <i class="fas fa-book"></i>
                    <span>Study Materials</span>
                </a>
                <a href="admin-panel/manage_student.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a href="batch/view_batch.php" class="menu-item">
                    <i class="fas fa-layer-group"></i>
                    <span>Batches</span>
                </a>
                <a href="fee/admin_fee_dashboard.php" class="menu-item">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Fee</span>
                </a>
                <a href="video-portal/view_videos_admin.php" class="menu-item">
                   <i class="fa-solid fa-video"></i>
                    <span>Video Upload</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card students">
                    <div class="stat-card-header">
                        <div>
                            <h3><?= $total_students ?></h3>
                            <p>Total Students</p>
                        </div>
                        <div class="stat-icon students">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card batches">
                    <div class="stat-card-header">
                        <div>
                            <h3><?= $total_batches ?></h3>
                            <p>Total Batches</p>
                        </div>
                        <div class="stat-icon batches">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card exams">
                    <div class="stat-card-header">
                        <div>
                            <h3><?= $total_exams ?></h3>
                            <p>Total Exams</p>
                        </div>
                        <div class="stat-icon exams">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card assignments">
                    <div class="stat-card-header">
                        <div>
                            <h3><?= $total_assignments ?></h3>
                            <p>Total Assignments</p>
                        </div>
                        <div class="stat-icon assignments">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card materials">
                    <div class="stat-card-header">
                        <div>
                            <h3><?= $total_materials ?></h3>
                            <p>Study Materials</p>
                        </div>
                        <div class="stat-icon materials">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                
                <div class="action-grid">
                    <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <h4>Exam Center</h4>
                        </div>
                        <div class="action-links">
                            <a href="exam-center/admin/create_exam.php" class="action-link">Create Exam</a>
                            <a href="exam-center/admin/exam_dashboard.php" class="action-link">Manage Exams</a>
                        </div>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h4>Assignments</h4>
                        </div>
                        <div class="action-links">
                            <a href="assignment/admin_assignment_dashboard.php" class="action-link">New Assignment</a>
                            <a href="assignment/view_submissions.php" class="action-link">View Submissions</a>
                        </div>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h4>Study Center</h4>
                        </div>
                        <div class="action-links">
                            <a href="study-center/assign_material.php" class="action-link">Upload PDF</a>
                            <a href="study-center/view_materials_admin.php" class="action-link">Manage Materials</a>
                        </div>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4>Student Management</h4>
                        </div>
                        <div class="action-links">
                            <a href="admin-panel/manage_student.php" class="action-link">Manage Students</a>
                            <a href="admin-panel/add_student.php" class="action-link">Add Student</a>
                        </div>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h4>Batch Management</h4>
                        </div>
                        <div class="action-links">
                            <a href="batch/create_batch.php" class="action-link">Create Batch</a>
                            <a href="batch/view_batch.php" class="action-link">View Batches</a>
                        </div>
                    </div>

                      <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                              <i class="fa-solid fa-receipt"></i>
                            </div>
                            <h4>Fee Management</h4>
                        </div>
                        <div class="action-links">
                            <a href="fee/.php" class="action-link">Fee Submissions</a>
        
                        </div>
                    </div>
                     <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                             <i class="fa-solid fa-video"></i>
                            </div>
                            <h4>Online Class Management</h4>
                        </div>
                        <div class="action-links">
                              <a href="video-portal/upload_video.php" class="action-link">Video Upload</a>
                              <a href="video-portal/video_reassign.php" class="action-link">Assign Video</a>
                              <a href="video-portal/view_videos_admin.php" class="action-link">View Video</a>
        
                        </div>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-card-header">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h4>Results</h4>
                        </div>
                        <div class="action-links">
                            <a href="exam-center/admin/exam_dashboard.php" class="action-link">Declare Results</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Section -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="section-title"><i class="fas fa-chart-line"></i> System Overview</h3>
                </div>
                <canvas id="summaryChart" height="100"></canvas>
            </div>
        </div>
    </div>

<div class="card">
    <h2>Total Students</h2>
    <div class="count"><?= $total ?></div>
    <p>(students26 table)</p>
</div>

    
    <script>
        // Password Protection
        document.addEventListener('DOMContentLoaded', function() {
            const passwordModal = document.getElementById('passwordModal');
            const adminPassword = document.getElementById('adminPassword');
            const submitBtn = document.getElementById('submitPassword');
            const errorMsg = document.getElementById('errorMsg');
            const dashboard = document.querySelector('.dashboard');
            
            // Set your password here (in a real application, this should be server-side)
            const correctPassword = "faiz2912"; // Change this to your desired password
            
            // Hide the main content initially
            dashboard.style.display = 'none';
            errorMsg.style.display = 'none';
            
            // Focus on password field when modal appears
            adminPassword.focus();
            
            submitBtn.addEventListener('click', function() {
                checkPassword();
            });
            
            // Allow pressing Enter to submit
            adminPassword.addEventListener('keyup', function(e) {
                if(e.key === 'Enter') {
                    checkPassword();
                }
            });
            
            function checkPassword() {
                if(adminPassword.value === correctPassword) {
                    passwordModal.style.display = 'none';
                    dashboard.style.display = 'flex';
                    
                    // Store session (for page refreshes)
                    sessionStorage.setItem('adminAuthenticated', 'true');
                    
                    // Initialize chart after successful login
                    initChart();
                } else {
                    errorMsg.style.display = 'block';
                    adminPassword.value = '';
                    adminPassword.focus();
                }
            }
            
            // Check if already authenticated in this session
            if(sessionStorage.getItem('adminAuthenticated') === 'true') {
                passwordModal.style.display = 'none';
                dashboard.style.display = 'flex';
                initChart();
            }
            
            // Chart.js Implementation
            function initChart() {
                const ctx = document.getElementById('summaryChart').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Students', 'Batches', 'Exams', 'Assignments', 'Materials'],
                        datasets: [{
                            label: 'Count Overview',
                            data: [<?= $total_students ?>, <?= $total_batches ?>, <?= $total_exams ?>, <?= $total_assignments ?>, <?= $total_materials ?>],
                            backgroundColor: [
                                'rgba(67, 97, 238, 0.7)',
                                'rgba(76, 201, 160, 0.7)',
                                'rgba(248, 150, 30, 0.7)',
                                'rgba(247, 37, 133, 0.7)',
                                'rgba(63, 55, 201, 0.7)'
                            ],
                            borderColor: [
                                'rgba(67, 97, 238, 1)',
                                'rgba(76, 201, 160, 1)',
                                'rgba(248, 150, 30, 1)',
                                'rgba(247, 37, 133, 1)',
                                'rgba(63, 55, 201, 1)'
                            ],
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                padding: 12,
                                cornerRadius: 6
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
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
            }
            
            // Mobile sidebar toggle (you can add a button for this)
            function toggleSidebar() {
                document.querySelector('.sidebar').classList.toggle('active');
            }
        });
    </script>
</body>
</html>