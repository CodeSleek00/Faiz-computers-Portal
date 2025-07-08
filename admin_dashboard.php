<?php
include '../database_connection/db_connect.php';

// Fetch counts
$total_students = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$total_batches = $conn->query("SELECT COUNT(*) AS c FROM batches")->fetch_assoc()['c'];
$total_exams = $conn->query("SELECT COUNT(*) AS c FROM exams")->fetch_assoc()['c'];
$total_assignments = $conn->query("SELECT COUNT(*) AS c FROM assignments")->fetch_assoc()['c'];
$total_materials = $conn->query("SELECT COUNT(*) AS c FROM study_material")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f4f7fc;
        }
        .header {
            background: #0d6efd;
            padding: 20px;
            color: white;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 30px 20px;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            flex: 1 1 calc(20% - 20px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .card h3 {
            font-size: 36px;
            color: #0d6efd;
            margin: 0;
        }
        .card p {
            margin: 8px 0 0;
            color: #777;
            font-size: 14px;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .feature-box {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
        }
        .feature-box h4 {
            margin: 0 0 10px;
            color: #333;
        }
        .feature-box a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            border-radius: 6px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
        }
        canvas {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .card {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>📊 Admin Dashboard</h1>
    <p>Manage Exams, Assignments, Study Materials, and Students</p>
</div>

<div class="container">

    <div class="cards">
        <div class="card">
            <h3><?= $total_students ?></h3>
            <p>Students</p>
        </div>
        <div class="card">
            <h3><?= $total_batches ?></h3>
            <p>Batches</p>
        </div>
        <div class="card">
            <h3><?= $total_exams ?></h3>
            <p>Exams</p>
        </div>
        <div class="card">
            <h3><?= $total_assignments ?></h3>
            <p>Assignments</p>
        </div>
        <div class="card">
            <h3><?= $total_materials ?></h3>
            <p>Study Materials</p>
        </div>
    </div>

    <div class="features">
        <div class="feature-box">
            <h4>📝 Exam Center</h4>
            <a href="create_exam.php">Create Exam</a>
            <a href="exam_dashboard.php">Manage Exams</a>
        </div>
        <div class="feature-box">
            <h4>📂 Assignment Center</h4>
            <a href="../assignments/admin_assignments.php">Create Assignment</a>
            <a href="../assignments/view_submissions.php">Submissions</a>
        </div>
        <div class="feature-box">
            <h4>📚 Study Center</h4>
            <a href="../study-center/upload_material.php">Upload Material</a>
            <a href="../study-center/view_materials_admin.php">Manage Material</a>
        </div>
        <div class="feature-box">
            <h4>🎯 Results & Review</h4>
            <a href="declare_result.php">Declare Results</a>
            <a href="view_results_admin.php">View Submissions</a>
        </div>
    </div>

    <h3 style="margin: 20px 0;">📈 Data Overview</h3>
    <canvas id="summaryChart" height="130"></canvas>

</div>

<script>
    const ctx = document.getElementById('summaryChart').getContext('2d');
    const summaryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Students', 'Batches', 'Exams', 'Assignments', 'Materials'],
            datasets: [{
                label: 'Overview Count',
                data: [<?= $total_students ?>, <?= $total_batches ?>, <?= $total_exams ?>, <?= $total_assignments ?>, <?= $total_materials ?>],
                backgroundColor: [
                    '#0d6efd', '#6610f2', '#198754', '#ffc107', '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>

</body>
</html>
