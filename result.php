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
        e.exam_name,
        e.total_questions
    FROM exam_submissions es
    INNER JOIN exams e ON e.exam_id = es.exam_id
    WHERE 
        es.student_id = ?
        AND es.student_table = ?
        AND e.is_declared = 1
    ORDER BY es.submitted_at ASC
");
$stmt->bind_param("is", $student_id, $student_table);
$stmt->execute();
$result = $stmt->get_result();

/* ================= PREPARE DATA ================= */
$exam_data = [];
$chart_labels = [];
$chart_scores = [];

while ($row = $result->fetch_assoc()) {
    $exam_data[] = $row;
    $chart_labels[] = date("d M", strtotime($row['submitted_at']));
    $percentage = ($row['total_questions'] > 0)
        ? round(($row['score'] / $row['total_questions']) * 100)
        : 0;
    $chart_scores[] = $percentage;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Exam Results</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    font-family: Poppins, Arial, sans-serif;
    background: #f1f5f9;
    padding: 20px;
}
.container {
    max-width: 1000px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
h2 {
    margin-bottom: 15px;
    color: #4f46e5;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
th, td {
    border: 1px solid #e5e7eb;
    padding: 12px;
    text-align: center;
    font-size: 14px;
}
th {
    background: #eef2ff;
    color: #3730a3;
}
tr:hover {
    background: #f9fafb;
}
.score {
    font-weight: 600;
    color: #16a34a;
}
.empty {
    text-align: center;
    padding: 20px;
    color: #6b7280;
}
.chart-box {
    margin-top: 35px;
}
</style>
</head>

<body>

<div class="container">
<h2>📊 My Exam Results</h2>

<?php if (count($exam_data) > 0): ?>

<table>
<tr>
    <th>#</th>
    <th>Exam Name</th>
    <th>Marks</th>
    <th>Percentage</th>
    <th>Date</th>
</tr>

<?php foreach ($exam_data as $i => $exam): 
    $percent = ($exam['total_questions'] > 0)
        ? round(($exam['score'] / $exam['total_questions']) * 100)
        : 0;
?>
<tr>
    <td><?= $i + 1 ?></td>
    <td><?= htmlspecialchars($exam['exam_name']) ?></td>
    <td class="score">
        <?= $exam['score'] ?> / <?= $exam['total_questions'] ?>
    </td>
    <td><?= $percent ?>%</td>
    <td><?= date("d M Y", strtotime($exam['submitted_at'])) ?></td>
</tr>
<?php endforeach; ?>
</table>

<div class="chart-box">
<h3>📈 Performance Over Time</h3>
<canvas id="scoreChart" height="120"></canvas>
</div>

<script>
const ctx = document.getElementById('scoreChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Score %',
            data: <?= json_encode($chart_scores) ?>,
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<?php else: ?>
<p class="empty">No declared exam results yet.</p>
<?php endif; ?>

</div>

</body>
</html>

<?php $conn->close(); ?>
