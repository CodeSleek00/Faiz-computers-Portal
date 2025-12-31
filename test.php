<?php
session_start();
require_once 'database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table'];
$student_id    = (int) $_SESSION['student_id'];

/* ================= STUDENT DATA ================= */
$stmt = $conn->prepare("SELECT name,enrollment_id,photo FROM $table WHERE enrollment_id=? LIMIT 1");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

/* ================= ATTENDANCE ================= */
$stmt = $conn->prepare("
    SELECT COUNT(*) total_days,
           SUM(status='Present') present_days
    FROM attendance
    WHERE student_id=?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$att = $stmt->get_result()->fetch_assoc();

$total_days   = $att['total_days'] ?? 0;
$present_days = $att['present_days'] ?? 0;
$attendance_percent = $total_days ? round(($present_days/$total_days)*100) : 0;

/* ================= ASSIGNMENTS ================= */
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT a.assignment_id) total,
           COUNT(DISTINCT s.submission_id) submitted
    FROM assignments a
    INNER JOIN assignment_targets t ON a.assignment_id=t.assignment_id
    LEFT JOIN assignment_submissions s 
        ON s.assignment_id=a.assignment_id AND s.student_id=?
    WHERE t.student_id=? 
       OR t.batch_id IN (
            SELECT batch_id FROM student_batches WHERE student_id=?
       )
");
$stmt->bind_param("iii",$student_id,$student_id,$student_id);
$stmt->execute();
$as = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{margin:0;font-family:Poppins;background:#f4f6fb}
.navbar{
    height:70px;background:#fff;display:flex;
    justify-content:space-between;align-items:center;
    padding:0 25px;box-shadow:0 3px 10px rgba(0,0,0,.08)
}
.nav-left{display:flex;gap:15px;align-items:center}
.brand{font-size:20px;font-weight:700;color:#4361ee}
.att-pill{
    background:#e7f5ff;color:#4361ee;
    padding:6px 14px;border-radius:30px;
    font-weight:600
}
.nav-right{display:flex;gap:12px;align-items:center}
.nav-right img{width:40px;height:40px;border-radius:50%;object-fit:cover}

.container{padding:25px}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:20px}
.card{
    background:#fff;padding:20px;border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,.08)
}
.card h3{margin:0;font-size:16px;color:#666}
.card h1{margin:10px 0;font-size:32px}

.att-good{color:#2ecc71}
.att-bad{color:#e74c3c}

.sidebar{
    position:fixed;top:70px;left:0;
    width:230px;height:100%;
    background:#fff;box-shadow:2px 0 10px rgba(0,0,0,.05);
    padding-top:20px
}
.sidebar a{
    display:flex;gap:12px;
    padding:12px 20px;color:#555;
    text-decoration:none;font-weight:600
}
.sidebar a:hover{background:#f1f4ff;color:#4361ee}

.main{margin-left:230px;margin-top:70px}
@media(max-width:900px){
    .sidebar{display:none}
    .main{margin-left:0}
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">
        <span class="brand">Faiz Computer Institute</span>
    </div>

    <div class="att-pill">
        <i class="fa-solid fa-calendar-check"></i>
        <?= $attendance_percent ?>% Attendance
    </div>

    <div class="nav-right">
        <img src="uploads/<?= $student['photo'] ?>">
        <b><?= htmlspecialchars($student['name']) ?></b>
        <a href="login-system/logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar">
    <a href="#"><i class="fa fa-home"></i> Dashboard</a>
    <a href="assignment/student_dashboard.php"><i class="fa fa-tasks"></i> Assignments</a>
    <a href="study-center/view_materials_student.php"><i class="fa fa-book"></i> Study</a>
    <a href="exam-center/student/student_dashboard.php"><i class="fa fa-pen"></i> Exams</a>
    <a href="attendence/student_attendance.php"><i class="fa fa-calendar"></i> Attendance</a>
</div>

<!-- MAIN -->
<div class="main">
<div class="container">

<h2>Welcome, <?= htmlspecialchars($student['name']) ?> 👋</h2>

<div class="cards">
    <div class="card">
        <h3>Attendance</h3>
        <h1 class="<?= $attendance_percent>=75?'att-good':'att-bad' ?>">
            <?= $attendance_percent ?>%
        </h1>
        <small><?= $present_days ?> / <?= $total_days ?> Days</small>
    </div>

    <div class="card">
        <h3>Total Assignments</h3>
        <h1><?= $as['total'] ?? 0 ?></h1>
    </div>

    <div class="card">
        <h3>Submitted</h3>
        <h1><?= $as['submitted'] ?? 0 ?></h1>
    </div>

    <div class="card">
        <h3>Pending</h3>
        <h1><?= ($as['total']-$as['submitted']) ?></h1>
    </div>
</div>

</div>
</div>

</body>
</html>
