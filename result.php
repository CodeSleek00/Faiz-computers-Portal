<?php
session_start();
include 'database_connection/db_connect.php';

// Ensure student is logged in
if(!isset($_SESSION['student_id'], $_SESSION['student_table'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_table = $_SESSION['student_table'];

// Fetch all exam submissions of this student
$stmt = $conn->prepare("
    SELECT es.submission_id, es.exam_id, es.score, es.submitted_at
    FROM exam_submissions es
    WHERE es.student_id = ? AND es.student_table = ? AND es.is_declared = 1
    ORDER BY es.submitted_at ASC
");
$stmt->bind_param("is", $student_id, $student_table);
$stmt->execute();
$result = $stmt->get_result();

$exam_data = [];
while($row = $result->fetch_assoc()) {
    $exam_data[] = $row;
}

// Prepare data for line chart (dates vs score)
$chart_labels = [];
$chart_scores = [];
foreach($exam_data as $exam) {
    $chart_labels[] = date("d M", strtotime($exam['submitted_at']));
    $chart_scores[] = $exam['score'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Exam Results</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    th { background-color: #4CAF50; color: white; }
    h2 { color: #333; }
</style>
</head>
<body>

<h2>📊 My Exam Results</h2>

<?php if(count($exam_data) > 0): ?>
<table>
    <tr>
        <th>#</th>
        <th>Exam ID</th>
        <th>Score</th>
        <th>Date</th>
    </tr>
    <?php foreach($exam_data as $index => $exam): ?>
    <tr>
        <td><?= $index+1 ?></td>
        <td><?= htmlspecialchars($exam['exam_id']) ?></td>
        <td><?= htmlspecialchars($exam['score']) ?>%</td>
        <td><?= date("d M Y", strtotime($exam['submitted_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>📈 Performance Over Time</h3>
<canvas id="scoreChart" width="600" height="300"></canvas>
<script>
    const ctx = document.getElementById('scoreChart').getContext('2d');
    const scoreChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Score %',
                data: <?= json_encode($chart_scores) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
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

<?php else: ?>
<p>No results declared yet.</p>
<?php endif; ?>

</body>
</html>
