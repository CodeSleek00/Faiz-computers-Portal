<?php
session_start();
include 'sqlite_config.php';

$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff']);
if (!$is_admin) {
    header('Location: ../login-system/login.php');
    exit;
}

$month_filter = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$query_params = [];
$where_clauses = [];

$query = "SELECT * FROM attendance WHERE 1=1";

$month_start = $month_filter . '-01';
$month_end = date('Y-m-t', strtotime($month_start));
$query .= " AND attendance_date BETWEEN ? AND ?";
$query_params[] = $month_start;
$query_params[] = $month_end;

$query .= " ORDER BY attendance_date DESC, student_name ASC LIMIT 500";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($query_params);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $attendance_records = [];
    $error_message = 'Error fetching records: ' . $e->getMessage();
}

function getStatistics($db, $month_filter) {
    try {
        $month_start = $month_filter . '-01';
        $month_end = date('Y-m-t', strtotime($month_start));

        $statsQuery = "
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'Leave' THEN 1 ELSE 0 END) as leave,
                COUNT(DISTINCT student_id) as total_students
            FROM attendance
            WHERE attendance_date BETWEEN ? AND ?
        ";

        $stmt = $db->prepare($statsQuery);
        $stmt->execute([$month_start, $month_end]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

$stats = getStatistics($db, $month_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance Records</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; padding: 25px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); text-align: center; }
        .stat-card .label { font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600; }
        .stat-card .value { font-size: 28px; font-weight: 700; color: #667eea; margin-top: 8px; }
        .stat-card.present .value { color: #10b981; }
        .stat-card.absent .value { color: #ef4444; }
        .stat-card.leave .value { color: #f59e0b; }
        .filters-section { padding: 25px; background: white; border-bottom: 1px solid #e0e0e0; }
        .filter-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px; }
        .form-group input, .form-group select { padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .btn { padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .content { padding: 25px; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th { padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #e0e0e0; }
        tbody tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-present { background: #d1fae5; color: #065f46; }
        .badge-absent { background: #fee2e2; color: #991b1b; }
        .badge-leave { background: #fef3c7; color: #92400e; }
        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        @media (max-width: 768px) { .header h1 { font-size: 22px; } .stats-grid { grid-template-columns: repeat(2, 1fr); } table { font-size: 12px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Attendance Records</h1>
            <p>View and manage attendance</p>
        </div>

        <?php if ($stats): ?>
        <div class="stats-grid">
            <div class="stat-card"><div class="label">Total Records</div><div class="value"><?php echo $stats['total_records'] ?? 0; ?></div></div>
            <div class="stat-card present"><div class="label">Present</div><div class="value"><?php echo $stats['present'] ?? 0; ?></div></div>
            <div class="stat-card absent"><div class="label">Absent</div><div class="value"><?php echo $stats['absent'] ?? 0; ?></div></div>
            <div class="stat-card leave"><div class="label">Leave</div><div class="value"><?php echo $stats['leave'] ?? 0; ?></div></div>
            <div class="stat-card"><div class="label">Students</div><div class="value"><?php echo $stats['total_students'] ?? 0; ?></div></div>
        </div>
        <?php endif; ?>

        <div class="filters-section">
            <form method="GET" id="filterForm">
                <div class="filter-group">
                    <div class="form-group">
                        <label for="month">Month:</label>
                        <input type="month" id="month" name="month" value="<?php echo $month_filter; ?>" onchange="document.getElementById('filterForm').submit()">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="content">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Enrollment ID</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Marked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendance_records)): ?>
                            <?php foreach ($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($record['attendance_date'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($record['enrollment_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($record['status']); ?>"><?php echo htmlspecialchars($record['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($record['marked_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6"><div class="empty-state"><p>No records found</p></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
