<?php
session_start();
include '../database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['student_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

/* ================= FETCH ATTENDANCE ================= */
$attendanceData = $conn->query("
    SELECT attendance_date, status 
    FROM attendance 
    WHERE student_id = $student_id
    ORDER BY attendance_date DESC
");

/* ================= COUNT SUMMARY ================= */
$countQuery = $conn->query("
    SELECT 
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_days,
        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) AS absent_days
    FROM attendance
    WHERE student_id = $student_id
");

$count = $countQuery->fetch_assoc();

$present = $count['present_days'] ?? 0;
$absent  = $count['absent_days'] ?? 0;
$total   = $present + $absent;

$percentage = ($total > 0) ? round(($present / $total) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | Student Dashboard</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 16px;
            color: var(--white);
        }

        .stat-icon.present { background: var(--success); }
        .stat-icon.absent { background: var(--danger); }
        .stat-icon.percentage { background: var(--primary); }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        /* Main Content */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .section-card {
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
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
        }

        .section-header i {
            color: var(--primary);
        }

        .section-body {
            padding: 20px;
        }

        /* Chart Container */
        .chart-container {
            height: 200px;
            position: relative;
        }

        /* Attendance Table */
        .table-container {
            overflow-x: auto;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-table thead {
            background: var(--light-gray);
        }

        .attendance-table th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        .attendance-table td {
            padding: 12px 16px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
        }

        .attendance-table tbody tr:hover {
            background: var(--light-gray);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-present {
            background: #d1fae5;
            color: var(--success);
        }

        .status-absent {
            background: #fee2e2;
            color: var(--danger);
        }

        .status-leave {
            background: #fef3c7;
            color: #92400e;
        }

        /* Alert Message */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .alert-success {
            background: #d1fae5;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .alert i {
            font-size: 14px;
        }

        /* No Data */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header-top {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
            
            .chart-container {
                height: 180px;
            }
        }

        @media (max-width: 480px) {
            .stat-card {
                padding: 16px;
            }
            
            .stat-number {
                font-size: 20px;
            }
            
            .attendance-table th,
            .attendance-table td {
                padding: 10px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <a href="../test.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
            </div>
            <div class="welcome-section">
                <h1>Attendance Record</h1>
                <p>Track your class attendance and performance</p>
            </div>
        </div>

        <!-- Alert Message -->
        <?php if ($percentage < 75 && $total > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Attendance is below 75%. Please attend classes regularly.
            </div>
        <?php elseif ($total > 0): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Attendance is good. Keep it up!
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon present">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?= $present ?></div>
                <div class="stat-label">Present Days</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon absent">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number"><?= $absent ?></div>
                <div class="stat-label">Absent Days</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon percentage">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-number"><?= $percentage ?>%</div>
                <div class="stat-label">Attendance</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-grid">
            <!-- Chart Section -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-chart-pie"></i>
                    <span>Attendance Overview</span>
                </div>
                <div class="section-body">
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Attendance History</span>
                </div>
                <div class="section-body">
                    <div class="table-container">
                        <?php if ($attendanceData->num_rows > 0): ?>
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $attendanceData->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date("d M Y", strtotime($row['attendance_date'])) ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch($row['status']) {
                                                    case 'Present': $status_class = 'status-present'; break;
                                                    case 'Absent': $status_class = 'status-absent'; break;
                                                    case 'Leave': $status_class = 'status-leave'; break;
                                                }
                                                ?>
                                                <span class="status-badge <?= $status_class ?>">
                                                    <?= $row['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h3>No Attendance Records</h3>
                                <p>No attendance data found for your account.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [<?= $present ?>, <?= $absent ?>],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 0,
                    borderRadius: 4,
                    spacing: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                cutout: '75%'
            }
        });
    </script>
</body>
</html>