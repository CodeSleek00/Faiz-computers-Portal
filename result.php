<?php
session_start();
include 'database_connection/db_connect.php';

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required");

/* ===== Detect Student Table ===== */
$student = $conn->query("
    SELECT student_id, 'students' AS student_table 
    FROM students 
    WHERE enrollment_id='$enrollment_id'
")->fetch_assoc();

if (!$student) {
    $student = $conn->query("
        SELECT id AS student_id, 'students26' AS student_table 
        FROM students26 
        WHERE enrollment_id='$enrollment_id'
    ")->fetch_assoc();
}

if (!$student) die("Student not found");

$student_id    = $student['student_id'];
$student_table = $student['student_table'];

/* ===== Fetch Declared Results ===== */
$data = $conn->query("
    SELECT 
        e.exam_name,
        s.score,
        (e.total_questions * e.marks_per_question) AS total_marks,
        s.submitted_at
    FROM exam_submissions s
    JOIN exams e ON e.exam_id = s.exam_id
    WHERE 
        s.student_id = $student_id
        AND s.student_table = '$student_table'
        AND e.result_declared = 1
    ORDER BY s.submitted_at ASC
");

$labels = [];
$scores = [];

while($r = $data->fetch_assoc()){
    $labels[] = $r['exam_name'];
    $scores[] = round(($r['score'] / $r['total_marks']) * 100);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Performance Chart</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{font-family:Poppins;background:#f3f4f6;padding:20px}
.box{
    max-width:900px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:10px
}
h2{text-align:center;margin-bottom:20px}
.back{
    display:inline-block;
    margin-bottom:15px;
    color:#4f46e5;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="box">
<a class="back" href="result.php">⬅ Back to Results</a>
<h2>📈 Performance Over Time</h2>

<canvas id="chart" height="120"></canvas>
</div>

<script>
const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Score (%)',
            data: <?= json_encode($scores) ?>,
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

</body>
</html>
