<?php
session_start();
include 'sqlite_config.php';

if (!isset($_SESSION['student_id']) && !isset($_SESSION['enrollment_id'])) {
    header('Location: ../login-system/login.php');
    exit;
}

$student_id = isset($_SESSION['student_id']) ? intval($_SESSION['student_id']) : 1; // Default for demo
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$month_start = $selected_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

try {
    $stmt = $db->prepare("
        SELECT attendance_date, status, remarks, marked_at
        FROM attendance
        WHERE student_id = ? AND attendance_date BETWEEN ? AND ?
        ORDER BY attendance_date DESC
    ");
    $stmt->execute([$student_id, $month_start, $month_end]);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $summaryStmt = $db->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = 'Leave' THEN 1 ELSE 0 END) as leave_days
        FROM attendance
        WHERE student_id = ? AND attendance_date BETWEEN ? AND ?
    ");
    $summaryStmt->execute([$student_id, $month_start, $month_end]);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    $overallStmt = $db->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days
        FROM attendance
        WHERE student_id = ?
    ");
    $overallStmt->execute([$student_id]);
    $overall = $overallStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = 'Error: ' . $e->getMessage();
    $attendance_records = [];
    $summary = null;
    $overall = null;
}

$month_total = $summary['total_days'] ?? 0;
$month_present = $summary['present_days'] ?? 0;
$month_percentage = ($month_total > 0) ? round(($month_present / $month_total) * 100, 2) : 0;

$overall_total = $overall['total_days'] ?? 0;
$overall_present = $overall['present_days'] ?? 0;
$overall_percentage = ($overall_total > 0) ? round(($overall_present / $overall_total) * 100, 2) : 0;

$attendance_map = [];
foreach ($attendance_records as $record) {
    $attendance_map[$record['attendance_date']] = $record;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .stats-section { padding: 30px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
        .stat-card .label { font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600; }
        .stat-card .value { font-size: 32px; font-weight: 700; color: #667eea; margin-top: 8px; }
        .stat-card.present .value { color: #10b981; }
        .stat-card.absent .value { color: #ef4444; }
        .stat-card.percentage .value { color: #667eea; }
        .progress-bar { background: #e0e0e0; height: 8px; border-radius: 4px; margin-top: 10px; overflow: hidden; }
        .progress-fill { background: linear-gradient(90deg, #10b981, #059669); height: 100%; border-radius: 4px; }
        .filters-section { padding: 25px 30px; background: white; border-bottom: 1px solid #e0e0e0; display: flex; gap: 15px; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 6px; color: #333; font-size: 14px; }
        .form-group input { padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .btn { padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; background: #667eea; color: white; }
        .content { padding: 30px; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .calendar-header h3 { font-size: 18px; color: #333; }
        .calendar { width: 100%; }
        .calendar-day-names { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; margin-bottom: 10px; }
        .calendar-day-name { text-align: center; font-weight: 600; color: #667; font-size: 12px; text-transform: uppercase; padding: 10px; }
        .calendar-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
        .calendar-day { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-weight: 600; font-size: 12px; border: 1px solid #e0e0e0; }
        .calendar-day.empty { background: #f5f5f5; border-color: transparent; }
        .calendar-day.present { background: #d1fae5; color: #065f46; border-color: #10b981; }
        .calendar-day.absent { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
        .calendar-day.leave { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th { padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #e0e0e0; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-present { background: #d1fae5; color: #065f46; }
        .badge-absent { background: #fee2e2; color: #991b1b; }
        .badge-leave { background: #fef3c7; color: #92400e; }
        .legend { display: flex; gap: 20px; flex-wrap: wrap; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-top: 20px; }
        .legend-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .legend-box { width: 20px; height: 20px; border-radius: 4px; }
        .legend-box.present { background: #10b981; }
        .legend-box.absent { background: #ef4444; }
        .legend-box.leave { background: #f59e0b; }
        @media (max-width: 768px) { .header h1 { font-size: 22px; } .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Attendance</h1>
        </div>

        <div class="stats-section">
            <div style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px;">Current Month - <?php echo date('F Y', strtotime($selected_month . '-01')); ?></div>
            <div class="stats-grid">
                <div class="stat-card"><div class="label">Total Days</div><div class="value"><?php echo $month_total; ?></div></div>
                <div class="stat-card present"><div class="label">Present</div><div class="value"><?php echo $month_present; ?></div></div>
                <div class="stat-card absent"><div class="label">Absent</div><div class="value"><?php echo $summary['absent_days'] ?? 0; ?></div></div>
                <div class="stat-card percentage"><div class="label">Attendance %</div><div class="value"><?php echo $month_percentage; ?>%</div><div class="progress-bar"><div class="progress-fill" style="width: <?php echo min($month_percentage, 100); ?>%"></div></div></div>
            </div>

            <div style="padding: 20px 0; border-top: 1px solid #e0e0e0; margin-top: 20px; padding-top: 20px;">
                <div style="font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px;">Overall Summary</div>
                <div class="stats-grid">
                    <div class="stat-card"><div class="label">Total Days</div><div class="value"><?php echo $overall_total; ?></div></div>
                    <div class="stat-card present"><div class="label">Present</div><div class="value"><?php echo $overall_present; ?></div></div>
                    <div class="stat-card percentage"><div class="label">Overall %</div><div class="value"><?php echo $overall_percentage; ?>%</div><div class="progress-bar"><div class="progress-fill" style="width: <?php echo min($overall_percentage, 100); ?>%"></div></div></div>
                </div>
            </div>
        </div>

        <div class="filters-section">
            <div class="form-group">
                <label for="month">Month:</label>
                <input type="month" id="month" value="<?php echo $selected_month; ?>" onchange="location.href='?month=' + this.value">
            </div>
        </div>

        <div class="content">
            <div class="calendar-header">
                <h3>Calendar View</h3>
            </div>

            <div class="calendar">
                <div class="calendar-day-names">
                    <div class="calendar-day-name">Sun</div>
                    <div class="calendar-day-name">Mon</div>
                    <div class="calendar-day-name">Tue</div>
                    <div class="calendar-day-name">Wed</div>
                    <div class="calendar-day-name">Thu</div>
                    <div class="calendar-day-name">Fri</div>
                    <div class="calendar-day-name">Sat</div>
                </div>

                <div class="calendar-days">
                    <?php
                    $first_day_of_week = date('w', strtotime($month_start));
                    $days_in_month = date('t', strtotime($month_start));

                    for ($i = 0; $i < $first_day_of_week; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }

                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date_string = $selected_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                        $has_record = isset($attendance_map[$date_string]);
                        $status = $has_record ? $attendance_map[$date_string]['status'] : '';
                        $status_class = $has_record ? strtolower($status) : '';

                        $is_future = (strtotime($date_string) > strtotime(date('Y-m-d')));

                        if ($is_future) {
                            echo '<div class="calendar-day empty"></div>';
                        } else {
                            echo '<div class="calendar-day ' . $status_class . '">' . $day . '</div>';
                        }
                    }

                    $total_cells = $first_day_of_week + $days_in_month;
                    $remaining_cells = (7 - ($total_cells % 7)) % 7;
                    for ($i = 0; $i < $remaining_cells; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }
                    ?>
                </div>
            </div>

            <div class="legend">
                <div class="legend-item"><div class="legend-box present"></div><span>Present</span></div>
                <div class="legend-item"><div class="legend-box absent"></div><span>Absent</span></div>
                <div class="legend-item"><div class="legend-box leave"></div><span>Leave</span></div>
            </div>

            <h3 style="margin-top: 30px; margin-bottom: 20px; color: #333;">Detailed Records</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Marked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendance_records)): ?>
                            <?php foreach ($attendance_records as $record): ?>
                                <tr>
                                    <td><strong><?php echo date('M d, Y (l)', strtotime($record['attendance_date'])); ?></strong></td>
                                    <td><span class="badge badge-<?php echo strtolower($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></span></td>
                                    <td><?php echo !empty($record['remarks']) ? htmlspecialchars($record['remarks']) : '-'; ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($record['marked_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; padding: 40px;">No records found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
