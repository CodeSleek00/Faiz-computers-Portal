<?php
session_start();
include 'database_connection/db_connect.php';

/* =================== LOGIN CHECK =================== */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: login-system/login.php");
    exit;
}

$student_id    = $_SESSION['student_id'];
$student_table = $_SESSION['student_table'];
$enrollment_id = $_SESSION['enrollment_id'];
$batch_id      = $_SESSION['batch_id'] ?? 0; // safe check

/* =================== ATTENDANCE DATA =================== */
$attendance_result = $conn->query("
    SELECT status, COUNT(*) AS count 
    FROM attendance 
    WHERE student_id = $student_id 
    GROUP BY status
");

$attendance_data = ['Present' => 0, 'Absent' => 0];
while ($row = $attendance_result->fetch_assoc()) {
    $attendance_data[$row['status']] = (int)$row['count'];
}

/* =================== LAST 5 STUDY MATERIALS =================== */
$stmt_materials = $conn->prepare("
    SELECT sm.title, sm.file_name, sm.uploaded_at
    FROM study_materials sm
    JOIN study_material_targets smt ON sm.id = smt.material_id
    WHERE (smt.student_id = ? AND smt.student_table = ?)
       OR (smt.batch_id = ?)
    ORDER BY sm.uploaded_at DESC
    LIMIT 5
");
$stmt_materials->bind_param("isi", $student_id, $student_table, $batch_id);
$stmt_materials->execute();
$materials = $stmt_materials->get_result();

/* =================== CURRENT MONTH FEE STATUS =================== */
$current_month = date('n'); // 1-12
$stmt_fee = $conn->prepare("
    SELECT payment_status, fee_amount, payment_date 
    FROM student_monthly_fee 
    WHERE enrollment_id = ? 
      AND month_no = ?
    LIMIT 1
");
$stmt_fee->bind_param("si", $enrollment_id, $current_month);
$stmt_fee->execute();
$fee_result = $stmt_fee->get_result();
$fee_status = $fee_result->fetch_assoc();

/* =================== ASSIGNED EXAMS =================== */
$stmt_exams = $conn->prepare("
    SELECT DISTINCT e.exam_id, e.exam_name, e.duration
    FROM exam_assignments ea
    JOIN exams e ON e.exam_id = ea.exam_id
    WHERE ea.student_table = ?
      AND (ea.student_id = ? OR ea.batch_id = ?)
    ORDER BY e.exam_id DESC
");

$stmt_exams->bind_param("sii", $student_table, $student_id, $batch_id);
$stmt_exams->execute();
$exams = $stmt_exams->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
        th { background: #f4f4f4; }
        .card { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 6px; }
    </style>
</head>
<body>

<h1>Welcome, <?php echo $_SESSION['name'] ?? 'Student'; ?></h1>

<!-- Attendance Pie Chart -->
<div class="card">
    <h2>📅 Attendance</h2>
    <canvas id="attendanceChart" width="400" height="200"></canvas>
</div>

<!-- Last 5 Study Materials -->
<div class="card">
    <h2>📚 Last 5 Study Materials</h2>
    <table>
        <tr><th>Title</th><th>File</th><th>Uploaded At</th></tr>
        <?php while ($row = $materials->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td>
                    <?php if ($row['file_name']): ?>
                        <a href="uploads/<?php echo $row['file_name']; ?>" target="_blank">Download</a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td><?php echo $row['uploaded_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- Current Month Fee Status -->
<div class="card">
    <h2>💰 Fee Status - <?php echo date('F'); ?></h2>
    <p>Status: <strong><?php echo $fee_status['payment_status'] ?? 'Pending'; ?></strong></p>
    <p>Amount: ₹<?php echo $fee_status['fee_amount'] ?? '0.00'; ?></p>
    <p>Paid On: <?php echo $fee_status['payment_date'] ?? 'N/A'; ?></p>
</div>

<!-- Assigned Exams -->
<div class="card">
    <h2>📝 Assigned Exams</h2>
    <table>
        <tr><th>Exam Name</th><th>Duration</th><th>Action</th></tr>
        <?php while ($exam = $exams->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                <td><?php echo $exam['duration']; ?> mins</td>
                <td>
                    <a href="exam-center/student/take_exam.php?exam_id=<?php echo $exam['exam_id']; ?>">Start Exam</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                label: 'Attendance',
                data: [<?php echo $attendance_data['Present']; ?>, <?php echo $attendance_data['Absent']; ?>],
                backgroundColor: ['#4CAF50', '#F44336']
            }]
        },
        options: {
            responsive: true
        }
    });
</script>

</body>
</html>
