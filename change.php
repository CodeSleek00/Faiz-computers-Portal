<?php
session_start();
require_once 'database_connection/db_connect.php';

/* =================== LOGIN CHECK =================== */
if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];

/* =================== DETECT STUDENT TABLE =================== */
$student = $conn->query("
    SELECT student_id, 'students' AS student_table, batch_id, name
    FROM students
    WHERE enrollment_id='$enrollment_id'
")->fetch_assoc();

if (!$student) {
    $student = $conn->query("
        SELECT id AS student_id, 'students26' AS student_table, batch_id, name
        FROM students26
        WHERE enrollment_id='$enrollment_id'
    ")->fetch_assoc();
}

if (!$student) die("Student not found");

$student_id    = $student['student_id'];
$student_table = $student['student_table'];
$batch_id      = $student['batch_id'] ?? 0;
$student_name  = $student['name'] ?? 'Student';

/* =================== ATTENDANCE =================== */
$attendance_data = ['Present'=>0,'Absent'=>0];
$att = $conn->query("
    SELECT status, COUNT(*) total 
    FROM attendance 
    WHERE student_id=$student_id 
    GROUP BY status
");
while($r=$att->fetch_assoc()){
    $attendance_data[$r['status']] = (int)$r['total'];
}

/* =================== LAST 5 STUDY MATERIALS =================== */
$stmt = $conn->prepare("
    SELECT sm.title, sm.file_name, sm.uploaded_at
    FROM study_materials sm
    JOIN study_material_targets t ON sm.id=t.material_id
    WHERE 
        (t.student_id=? AND t.student_table=?)
        OR (t.batch_id=?)
    ORDER BY sm.uploaded_at DESC
    LIMIT 5
");
$stmt->bind_param("isi",$student_id,$student_table,$batch_id);
$stmt->execute();
$materials = $stmt->get_result();

/* =================== CURRENT MONTH FEE =================== */
$month = date('n');
$stmt = $conn->prepare("
    SELECT payment_status, fee_amount, payment_date
    FROM student_monthly_fee
    WHERE enrollment_id=? AND month_no=?
    LIMIT 1
");
$stmt->bind_param("si",$enrollment_id,$month);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();

/* =================== ASSIGNED EXAMS =================== */
$stmt = $conn->prepare("
    SELECT DISTINCT e.exam_id, e.exam_name, e.duration
    FROM exam_assignments ea
    JOIN exams e ON e.exam_id=ea.exam_id
    WHERE ea.student_table=?
      AND (ea.student_id=? OR ea.batch_id=?)
    ORDER BY e.exam_id DESC
");
$stmt->bind_param("sii",$student_table,$student_id,$batch_id);
$stmt->execute();
$exams = $stmt->get_result();

/* =================== PERFORMANCE CHART =================== */
$res = $conn->query("
    SELECT e.exam_name, s.score,
           (e.total_questions*e.marks_per_question) total_marks
    FROM exam_submissions s
    JOIN exams e ON e.exam_id=s.exam_id
    WHERE s.student_id=$student_id
      AND s.student_table='$student_table'
      AND e.result_declared=1
    ORDER BY s.submitted_at ASC
");

$labels=[]; $scores=[];
while($r=$res->fetch_assoc()){
    $labels[] = $r['exam_name'];
    $scores[] = round(($r['score']/$r['total_marks'])*100);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{font-family:Poppins;background:#f3f4f6;margin:0;padding:20px}
h1{margin-bottom:20px}
.card{
    background:#fff;
    padding:20px;
    border-radius:10px;
    margin-bottom:25px;
}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;border-bottom:1px solid #ddd;text-align:left}
th{background:#f9fafb}
a{color:#4f46e5;text-decoration:none}
.badge{padding:5px 10px;border-radius:6px;font-size:13px}
.success{background:#dcfce7;color:#166534}
.pending{background:#fee2e2;color:#991b1b}
</style>
</head>

<body>

<h1>👋 Welcome, <?= htmlspecialchars($student_name) ?></h1>

<!-- Attendance -->
<div class="card">
<h2>📅 Attendance</h2>
<canvas id="attendanceChart" height="120"></canvas>
</div>

<!-- Study Material -->
<div class="card">
<h2>📚 Recent Study Material</h2>
<table>
<tr><th>Title</th><th>File</th><th>Date</th></tr>
<?php while($m=$materials->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($m['title']) ?></td>
<td>
<?php if($m['file_name']): ?>
<a href="uploads/<?= $m['file_name'] ?>" target="_blank">Download</a>
<?php else: ?>N/A<?php endif; ?>
</td>
<td><?= date('d M Y',strtotime($m['uploaded_at'])) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- Fee -->
<div class="card">
<h2>💰 Fee Status (<?= date('F') ?>)</h2>
<p>Status:
<span class="badge <?= ($fee['payment_status']??'Pending')=='Paid'?'success':'pending' ?>">
<?= $fee['payment_status'] ?? 'Pending' ?>
</span>
</p>
<p>Amount: ₹<?= $fee['fee_amount'] ?? '0' ?></p>
<p>Date: <?= $fee['payment_date'] ?? 'N/A' ?></p>
</div>

<!-- Exams -->
<div class="card">
<h2>📝 Assigned Exams</h2>
<table>
<tr><th>Exam</th><th>Duration</th><th>Action</th></tr>
<?php while($e=$exams->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($e['exam_name']) ?></td>
<td><?= $e['duration'] ?> mins</td>
<td><a href="exam-center/student/take_exam.php?exam_id=<?= $e['exam_id'] ?>">Start</a></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- Performance -->
<div class="card">
<h2>📈 Performance</h2>
<canvas id="performanceChart" height="120"></canvas>
</div>

<script>
new Chart(document.getElementById('attendanceChart'),{
type:'pie',
data:{
labels:['Present','Absent'],
datasets:[{
data:[<?= $attendance_data['Present'] ?>,<?= $attendance_data['Absent'] ?>],
backgroundColor:['#22c55e','#ef4444']
}]
}
});

new Chart(document.getElementById('performanceChart'),{
type:'line',
data:{
labels:<?= json_encode($labels) ?>,
datasets:[{
label:'Score %',
data:<?= json_encode($scores) ?>,
borderWidth:3,
tension:.4,
fill:true
}]
},
options:{scales:{y:{beginAtZero:true,max:100}}}
});
</script>

</body>
</html>
