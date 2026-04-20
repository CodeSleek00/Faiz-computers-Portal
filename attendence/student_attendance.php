<?php
session_start();
include '../database_connection/db_connect.php';

$enrollment_id = $_SESSION['enrollment_id'];

$student = null;
$table_name = "";

/* find student */
$res = $conn->query("SELECT * FROM students WHERE enrollment_id='$enrollment_id'");
if($res->num_rows){
    $student = $res->fetch_assoc();
    $student_id = $student['student_id'];
    $table_name = "students";
}else{
    $res = $conn->query("SELECT * FROM students26 WHERE enrollment_id='$enrollment_id'");
    $student = $res->fetch_assoc();
    $student_id = $student['id'];
    $table_name = "students26";
}

/* 🔥 Fetch Attendance */
$data = $conn->query("
SELECT * FROM attendance 
WHERE student_id='$student_id' AND table_name='$table_name'
ORDER BY date DESC
");

/* 🔥 Stats */
$total = 0;
$present = 0;
$absent = 0;

$records = [];

while($row = $data->fetch_assoc()){
    $records[] = $row;
    $total++;
    if($row['status'] == 'Present') $present++;
    else $absent++;
}

$percentage = $total ? round(($present/$total)*100) : 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>My Attendance</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    font-family:'Poppins',sans-serif;
    background:#f4f6f9;
    padding:20px;
}
.container{
    max-width:1100px;
    margin:auto;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}
h2{
    margin-bottom:10px;
}

/* Stats */
.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
}
.stat{
    background:#2563eb;
    color:white;
    padding:15px;
    border-radius:10px;
    text-align:center;
}
.stat h3{
    margin:0;
    font-size:20px;
}
.stat p{
    margin:0;
    font-size:12px;
}

/* Progress */
.progress{
    background:#e5e7eb;
    border-radius:10px;
    height:10px;
    margin-top:10px;
}
.progress-bar{
    height:10px;
    background:#2563eb;
    border-radius:10px;
}

/* Table */
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:10px;
    border-bottom:1px solid #eee;
    text-align:center;
}
th{
    background:#2563eb;
    color:white;
}
.present{color:green;font-weight:600;}
.absent{color:red;font-weight:600;}
</style>

</head>
<body>

<div class="container">

<!-- 🔵 Header -->
<div class="card">
<h2>📊 My Attendance Dashboard</h2>
<p><?= $student['name'] ?> | <?= $student['course'] ?></p>
</div>

<!-- 🔵 Stats -->
<div class="stats">
    <div class="stat">
        <h3><?= $total ?></h3>
        <p>Total Days</p>
    </div>

    <div class="stat">
        <h3><?= $present ?></h3>
        <p>Present</p>
    </div>

    <div class="stat">
        <h3><?= $absent ?></h3>
        <p>Absent</p>
    </div>

    <div class="stat">
        <h3><?= $percentage ?>%</h3>
        <p>Attendance %</p>
    </div>
</div>

<!-- 🔵 Progress -->
<div class="card">
<h3>Attendance Progress</h3>
<div class="progress">
    <div class="progress-bar" style="width:<?= $percentage ?>%"></div>
</div>
</div>

<!-- 🔵 Chart -->
<div class="card">
<h3>Attendance Chart</h3>
<canvas id="chart"></canvas>
</div>

<!-- 🔵 Table -->
<div class="card">
<h3>Attendance History</h3>

<table>
<tr>
<th>Date</th>
<th>Status</th>
</tr>

<?php foreach($records as $row){ ?>
<tr>
<td><?= date('d M Y', strtotime($row['date'])) ?></td>
<td class="<?= strtolower($row['status']) ?>">
<?= $row['status'] ?>
</td>
</tr>
<?php } ?>

</table>

</div>

</div>

<script>
new Chart(document.getElementById('chart'), {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{
            data: [<?= $present ?>, <?= $absent ?>]
        }]
    }
});
</script>

</body>
</html>