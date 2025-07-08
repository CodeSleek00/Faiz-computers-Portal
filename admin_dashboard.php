<?php
include '../database_connection/db_connect.php';
session_start();

// Ensure admin is logged in (example check)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch stats
$totalStudents = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$totalExams    = $conn->query("SELECT COUNT(*) FROM exams")->fetch_row()[0];
$totalAssignments = $conn->query("SELECT COUNT(*) FROM assignments")->fetch_row()[0];
$totalMaterials = $conn->query("SELECT COUNT(*) FROM study_material")->fetch_row()[0];

// Recent Exams
$recentExams = $conn->query("SELECT exam_id, exam_name, created_at FROM exams ORDER BY created_at DESC LIMIT 5");

// Exam participation for chart
$chartData = $conn->query("
  SELECT e.exam_name, COUNT(s.submission_id) as attempts
  FROM exams e
  LEFT JOIN exam_submissions s ON e.exam_id = s.exam_id
  GROUP BY e.exam_id
");
$labels = []; $data = [];
while ($row = $chartData->fetch_assoc()) {
    $labels[] = $row['exam_name'];
    $data[] = $row['attempts'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'Poppins', sans-serif; margin:0; background:#f4f7fa; }
    .sidebar{width:250px;background:#1d1f27;color:white;position:fixed;top:0;bottom:0;padding:20px;}
    .content{margin-left:250px;padding:30px;}
    .card{background:white;padding:20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.05);display:inline-block;width:23%;margin:1%;vertical-align:top;text-align:center;}
    .card h3{margin:10px 0;}
    table {width:100%;background:white;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin-top:30px;}
    th,td{padding:12px;border-bottom:1px solid #eee;}
    canvas{margin-top:40px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);}
    @media(max-width:800px){.card{width:48%;}table,canvas{width:100%!important;overflow-x:auto;}}
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin</h2>
    <nav>
      <a href="dashboard.php">Dashboard</a><br>
      <a href="exam_dashboard.php">Exams</a><br>
      <a href="../assignment/admin/list.php">Assignments</a><br>
      <a href="../study-center/view_material_admin.php">Study Center</a><br>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
  <div class="content">
    <h1>🎯 Admin Dashboard</h1>
    <div class="card"><h3>Students</h3><p><?= $totalStudents ?></p></div>
    <div class="card"><h3>Exams</h3><p><?= $totalExams ?></p></div>
    <div class="card"><h3>Assignments</h3><p><?= $totalAssignments ?></p></div>
    <div class="card"><h3>Materials</h3><p><?= $totalMaterials ?></p></div>

    <table>
      <tr><th>ID</th><th>Name</th><th>Created At</th><th>Action</th></tr>
      <?php while($e=$recentExams->fetch_assoc()): ?>
        <tr>
          <td><?= $e['exam_id'] ?></td>
          <td><?= htmlspecialchars($e['exam_name']) ?></td>
          <td><?= date('d M, Y',strtotime($e['created_at'])) ?></td>
          <td><a href="view_results_admin.php?exam_id=<?= $e['exam_id'] ?>">View Results</a></td>
        </tr>
      <?php endwhile ?>
    </table>

    <canvas id="examChart" height="100"></canvas>
    <script>
      const ctx = document.getElementById('examChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: <?= json_encode($labels) ?>,
          datasets: [{
            label: 'Exam Attempts',
            data: <?= json_encode($data) ?>,
            backgroundColor: '#4e73df'
          }]
        },
        options: { responsive:true, scales:{ y:{beginAtZero:true} } }
      });
    </script>
  </div>
</body>
</html>
