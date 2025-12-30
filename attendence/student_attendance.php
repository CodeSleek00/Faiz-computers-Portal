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
<html>
<head>
    <title>My Attendance</title>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }

        h2 {
            margin-bottom: 10px;
        }

        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            background: #f9fafb;
            text-align: center;
            font-size: 18px;
        }

        .present { color: #27ae60; }
        .absent { color: #c0392b; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #2c3e50;
            color: #fff;
        }

        .chart-box {
            width: 350px;
            margin: 30px auto;
        }

        .warning {
            margin-top: 15px;
            padding: 10px;
            background: #fdecea;
            color: #c0392b;
            border-radius: 5px;
            text-align: center;
        }

        .good {
            margin-top: 15px;
            padding: 10px;
            background: #eafaf1;
            color: #27ae60;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>📅 My Attendance</h2>

    <!-- SUMMARY -->
    <div class="summary">
        <div class="card present">
            Present<br><strong><?= $present ?></strong>
        </div>
        <div class="card absent">
            Absent<br><strong><?= $absent ?></strong>
        </div>
        <div class="card">
            Percentage<br><strong><?= $percentage ?>%</strong>
        </div>
    </div>

    <!-- PIE CHART -->
    <div class="chart-box">
        <canvas id="attendanceChart"></canvas>
    </div>

    <?php if ($percentage < 75 && $total > 0) { ?>
        <div class="warning">
            ⚠ Attendance is below 75%. Please attend classes regularly.
        </div>
    <?php } elseif ($total > 0) { ?>
        <div class="good">
            ✅ Attendance is good. Keep it up!
        </div>
    <?php } ?>

    <!-- ATTENDANCE TABLE -->
    <table>
        <tr>
            <th>Date</th>
            <th>Status</th>
        </tr>

        <?php if ($attendanceData->num_rows > 0) { ?>
            <?php while($row = $attendanceData->fetch_assoc()) { ?>
            <tr>
                <td><?= date("d M Y", strtotime($row['attendance_date'])) ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="2">No attendance record found</td>
            </tr>
        <?php } ?>
    </table>

</div>

<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');

new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            data: [<?= $present ?>, <?= $absent ?>],
            backgroundColor: ['#2ecc71', '#e74c3c']
        }]
    },
    options: {
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>
