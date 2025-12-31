<?php
session_start();
include 'database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['student_id'], $_SESSION['student_table'])) {
    header("Location: login.php");
    exit;
}

$student_id    = $_SESSION['student_id'];
$student_table = $_SESSION['student_table'];

/* ================= FETCH DECLARED RESULTS ================= */
$stmt = $conn->prepare("
    SELECT 
        es.exam_id,
        es.score,
        es.submitted_at,
        es.is_declared,
        e.exam_name,
        e.total_questions
    FROM exam_submissions es
    INNER JOIN exams e ON e.exam_id = es.exam_id
    WHERE 
        es.student_id = ?
        AND es.student_table = ?
        AND es.is_declared = 1
    ORDER BY es.submitted_at ASC
");

$stmt->bind_param("is", $student_id, $student_table);
$stmt->execute();
$result = $stmt->get_result();

/* ================= DATA PREP ================= */
$results = [];
$labels  = [];
$scores  = [];

while ($row = $result->fetch_assoc()) {
    $results[] = $row;
    $labels[] = date("d M", strtotime($row['submitted_at']));

    $percent = ($row['total_questions'] > 0)
        ? round(($row['score'] / $row['total_questions']) * 100)
        : 0;

    $scores[] = $percent;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>My Results</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{font-family:Poppins;background:#f4f6fa;padding:20px}
.box{max-width:1000px;margin:auto;background:#fff;padding:25px;border-radius:10px}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:10px;text-align:center}
th{background:#4f46e5;color:#fff}
</style>
</head>
<body>

<div class="box">
<h2>📊 My Exam Results</h2>

<?php if(count($results) > 0): ?>
<table>
<tr>
    <th>#</th>
    <th>Exam</th>
    <th>Marks</th>
    <th>Percentage</th>
    <th>Date</th>
</tr>

<?php foreach($results as $i=>$r):
    $percent = round(($r['score']/$r['total_questions'])*100);
?>
<tr>
    <td><?= $i+1 ?></td>
    <td><?= htmlspecialchars($r['exam_name']) ?></td>
    <td><?= $r['score'] ?> / <?= $r['total_questions'] ?></td>
    <td><?= $percent ?>%</td>
    <td><?= date("d M Y",strtotime($r['submitted_at'])) ?></td>
</tr>
<?php endforeach; ?>
</table>

<canvas id="chart" height="120"></canvas>

<script>
new Chart(document.getElementById('chart'),{
    type:'line',
    data:{
        labels:<?= json_encode($labels) ?>,
        datasets:[{
            label:'Score %',
            data:<?= json_encode($scores) ?>,
            borderWidth:3,
            tension:0.4,
            fill:true
        }]
    },
    options:{scales:{y:{beginAtZero:true,max:100}}}
});
</script>

<?php else: ?>
<p>No results declared yet.</p>
<?php endif; ?>

</div>
</body>
</html>
