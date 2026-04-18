<?php
session_start();
include 'sqlite_config.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['enrollment_id']);
$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff']);
$is_student = isset($_SESSION['enrollment_id']) && !$is_admin;

// Student trying to access admin page - show error
if ($is_student) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
            .error-container { background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); padding: 50px 40px; max-width: 500px; text-align: center; }
            .error-icon { font-size: 60px; margin-bottom: 20px; }
            h1 { color: #ef4444; font-size: 28px; margin-bottom: 15px; }
            p { color: #666; font-size: 16px; margin-bottom: 30px; line-height: 1.6; }
            .error-msg { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin-bottom: 30px; text-align: left; border-radius: 4px; }
            .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; transition: all 0.3s; border: none; cursor: pointer; }
            .btn:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); }
            .btn-secondary { background: #999; margin-left: 10px; }
            .btn-secondary:hover { background: #777; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">🚫</div>
            <h1>Access Denied</h1>
            <div class="error-msg">
                <strong>Student Account Detected:</strong><br>
                You are logged in as a Student. Only Admin/Staff can mark attendance.
            </div>
            <p>👤 <strong>Your Login:</strong> <?php echo htmlspecialchars($_SESSION['enrollment_id']); ?></p>
            <p>📝 <strong>Your Role:</strong> Student</p>
            <p style="color: #999; font-size: 14px; margin-top: 20px;">If you need to view your attendance, please use the Student Attendance page.</p>
            <div style="margin-top: 30px;">
                <a href="../test.php" class="btn">Go to Dashboard</a>
                <a href="student_attendance.php" class="btn btn-secondary">View My Attendance</a>
            </div>
            <p style="color: #999; font-size: 13px; margin-top: 20px;">Contact Admin if you need attendance marking access.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Not logged in - redirect to login
if (!$is_logged_in) {
    header('Location: ../login-system/login.php');
    exit;
}

$marked_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$batch_filter = isset($_GET['batch']) ? trim($_GET['batch']) : '';

if ($marked_date > date('Y-m-d')) {
    $marked_date = date('Y-m-d');
}

$students = [];
$batches = ['Batch 1', 'Batch 2', 'All Batches'];

$existing_attendance = [];
try {
    $stmt = $db->prepare("SELECT student_id, status, remarks FROM attendance WHERE attendance_date = ?");
    $stmt->execute([$marked_date]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $existing_attendance[$row['student_id']] = [
            'status' => $row['status'],
            'remarks' => $row['remarks']
        ];
    }
} catch (PDOException $e) {
    error_log('Error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .filters-section { padding: 25px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0; }
        .filter-group { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px; }
        .form-group input, .form-group select { padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn { padding: 10px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .bulk-actions { padding: 15px; background: white; border-bottom: 1px solid #e0e0e0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .bulk-actions label { display: flex; align-items: center; gap: 8px; font-weight: 500; margin-right: 15px; }
        .bulk-actions input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .content { padding: 20px; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th { padding: 15px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #e0e0e0; }
        tbody tr:hover { background: #f8f9fa; }
        .student-checkbox { width: 18px; height: 18px; cursor: pointer; }
        .student-photo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .status-select { padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; min-width: 120px; }
        .remarks-input { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; }
        .submit-section { padding: 25px; background: #f8f9fa; border-top: 1px solid #e0e0e0; display: flex; gap: 10px; justify-content: flex-end; }
        .summary { padding: 20px; background: #f0f4ff; border-radius: 8px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        .summary-item { text-align: center; }
        .summary-item .label { font-size: 12px; color: #666; text-transform: uppercase; font-weight: 600; }
        .summary-item .value { font-size: 24px; font-weight: 700; color: #667eea; }
        @media (max-width: 768px) { .header h1 { font-size: 22px; } .filter-group { grid-template-columns: 1fr; } table { font-size: 12px; } th, td { padding: 10px 8px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mark Attendance</h1>
            <p>Date: <strong><?php echo date('l, F j, Y', strtotime($marked_date)); ?></strong></p>
        </div>

        <div class="filters-section">
            <form method="GET" id="filterForm">
                <div class="filter-group">
                    <div class="form-group">
                        <label for="date">Attendance Date:</label>
                        <input type="date" id="date" name="date" value="<?php echo $marked_date; ?>" max="<?php echo date('Y-m-d'); ?>" required onchange="document.getElementById('filterForm').submit()">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <div class="bulk-actions">
            <label><input type="checkbox" id="selectAll"> Select All</label>
            <button type="button" class="btn btn-success" onclick="bulkAction('Present')">Mark All Present</button>
            <button type="button" class="btn btn-danger" onclick="bulkAction('Absent')">Mark All Absent</button>
            <button type="button" class="btn" style="background: #f59e0b; color: white;" onclick="bulkAction('Leave')">Mark All Leave</button>
        </div>

        <form method="POST" action="save_attendance.php" id="attendanceForm">
            <div class="content">
                <div class="summary">
                    <div class="summary-item"><div class="label">Total Students</div><div class="value" id="totalStudents">0</div></div>
                    <div class="summary-item"><div class="label">Present</div><div class="value" id="presentCount">0</div></div>
                    <div class="summary-item"><div class="label">Absent</div><div class="value" id="absentCount">0</div></div>
                    <div class="summary-item"><div class="label">Leave</div><div class="value" id="leaveCount">0</div></div>
                </div>

                <input type="hidden" name="marked_date" value="<?php echo $marked_date; ?>">
                <input type="hidden" name="marked_by" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; ?>">

                <div class="table-wrapper">
                    <table id="attendanceTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Select</th>
                                <th>Enrollment ID</th>
                                <th>Name</th>
                                <th style="width: 120px;">Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" style="text-align: center; padding: 40px;">Load students by selecting a date above</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="submit-section">
                <button type="reset" class="btn" style="background: #999; color: white;">Reset</button>
                <button type="submit" class="btn btn-success">Save Attendance</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSummary();
        });

        function bulkAction(status) {
            const selects = document.querySelectorAll('.status-select');
            selects.forEach(select => select.value = status);
            updateSummary();
        }

        function updateSummary() {
            const selects = document.querySelectorAll('.status-select');
            let present = 0, absent = 0, leave = 0;

            selects.forEach(select => {
                switch(select.value) {
                    case 'Present': present++; break;
                    case 'Absent': absent++; break;
                    case 'Leave': leave++; break;
                }
            });

            document.getElementById('totalStudents').textContent = selects.length;
            document.getElementById('presentCount').textContent = present;
            document.getElementById('absentCount').textContent = absent;
            document.getElementById('leaveCount').textContent = leave;
        }

        updateSummary();
    </script>
</body>
</html>
