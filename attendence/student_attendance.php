<?php
session_start();
include '../database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['student_id']) && !isset($_SESSION['enrollment_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

// Determine student ID based on session
$student_id = null;
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} elseif (isset($_SESSION['enrollment_id'])) {
    // If enrollment_id is in session, fetch student_id from database
    $enrollment_id = $_SESSION['enrollment_id'];
    $student_query = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment_id'
                                   UNION
                                   SELECT id FROM students26 WHERE enrollment_id = '$enrollment_id'");
    if ($student_query && $student_query->num_rows > 0) {
        $student_data = $student_query->fetch_assoc();
        $student_id = $student_data['student_id'] ?? $student_data['id'];
    }
}

if (!$student_id) {
    header("Location: ../login-system/login.php");
    exit;
}

/* ================= FETCH ATTENDANCE ================= */
$attendanceData = $conn->query("
    SELECT DISTINCT date as attendance_date, status
    FROM attendance
    WHERE student_id = $student_id
    ORDER BY date DESC
");

if (!$attendanceData) {
    die("Database error: " . $conn->error);
}

// Create attendance array for calendar
$attendanceMap = [];
while ($row = $attendanceData->fetch_assoc()) {
    $attendanceMap[$row['attendance_date']] = $row['status'];
}

/* ================= COUNT SUMMARY ================= */
$countQuery = $conn->query("
    SELECT
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_days,
        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) AS absent_days
    FROM attendance
    WHERE student_id = $student_id
");

if (!$countQuery) {
    die("Database error: " . $conn->error);
}

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

        /* Calendar Styles */
        .calendar-container {
            max-width: 100%;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-nav-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .calendar-nav-btn:hover {
            background: var(--primary-light);
        }

        .calendar-header h3 {
            margin: 0;
            color: var(--dark);
            font-size: 18px;
            font-weight: 600;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .day-name {
            text-align: center;
            font-weight: 600;
            color: var(--gray);
            font-size: 12px;
            padding: 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .calendar-days {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            border: 2px solid transparent;
        }

        .calendar-day.present {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success);
            color: var(--success);
        }

        .calendar-day.absent {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--danger);
            color: var(--danger);
        }

        .calendar-day.today {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            font-weight: 700;
        }

        .calendar-day.other-month {
            color: var(--light-gray);
            opacity: 0.5;
        }

        .calendar-day:hover {
            transform: scale(1.1);
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

            .calendar-day {
                font-size: 12px;
            }

            .day-name {
                font-size: 11px;
                padding: 6px 0;
            }
        }

        @media (max-width: 480px) {
            .stat-card {
                padding: 16px;
            }
            
            .stat-number {
                font-size: 20px;
            }
            
            .calendar-nav-btn {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .calendar-header h3 {
                font-size: 16px;
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

            <!-- Calendar Section -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Attendance Calendar</span>
                </div>
                <div class="section-body">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button id="prevMonth" class="calendar-nav-btn">‹</button>
                            <h3 id="calendarTitle"></h3>
                            <button id="nextMonth" class="calendar-nav-btn">›</button>
                        </div>
                        <div class="calendar-grid">
                            <div class="day-name">Sun</div>
                            <div class="day-name">Mon</div>
                            <div class="day-name">Tue</div>
                            <div class="day-name">Wed</div>
                            <div class="day-name">Thu</div>
                            <div class="day-name">Fri</div>
                            <div class="day-name">Sat</div>
                            <div id="calendarDays" class="calendar-days"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Attendance data from PHP
        const attendanceData = <?php echo json_encode($attendanceMap); ?>;

        // Calendar functionality
        let currentDate = new Date();

        function renderCalendar(date) {
            const year = date.getFullYear();
            const month = date.getMonth();

            // Update title
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('calendarTitle').textContent = `${monthNames[month]} ${year}`;

            // Get first day of month and last day
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';

            // Generate calendar days
            for (let i = 0; i < 42; i++) {
                const day = new Date(startDate);
                day.setDate(startDate.getDate() + i);

                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                dayDiv.textContent = day.getDate();

                // Check if day is in current month
                if (day.getMonth() !== month) {
                    dayDiv.classList.add('other-month');
                }

                // Check if it's today
                const today = new Date();
                if (day.toDateString() === today.toDateString()) {
                    dayDiv.classList.add('today');
                }

                // Check attendance status
                const dateKey = day.toISOString().split('T')[0];
                if (attendanceData[dateKey]) {
                    if (attendanceData[dateKey] === 'Present') {
                        dayDiv.classList.add('present');
                    } else if (attendanceData[dateKey] === 'Absent') {
                        dayDiv.classList.add('absent');
                    }
                }

                calendarDays.appendChild(dayDiv);
            }
        }

        // Navigation
        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });

        // Initialize calendar
        renderCalendar(currentDate);

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