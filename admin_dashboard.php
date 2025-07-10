<?php
include 'database_connection/db_connect.php';

// Fetch counts
$total_students = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$total_batches = $conn->query("SELECT COUNT(*) AS c FROM batches")->fetch_assoc()['c'];
$total_exams = $conn->query("SELECT COUNT(*) AS c FROM exams")->fetch_assoc()['c'];
$total_assignments = $conn->query("SELECT COUNT(*) AS c FROM assignments")->fetch_assoc()['c'];
$total_materials = $conn->query("SELECT COUNT(*) AS c FROM study_materials")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #1d2b64, #f8cdda);
            min-height: 100vh;
            padding: 0;
            color: #f0f0f0;
        }

        header {
            padding: 30px 20px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
        }

        header h1 {
            font-size: 36px;
            color: #fff;
            margin-bottom: 10px;
        }

        header p {
            color: #ccc;
            font-size: 14px;
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
            justify-content: space-between;
        }

        .card {
            flex: 1 1 calc(20% - 20px);
            background: rgba(255, 255, 255, 0.05);
            padding: 25px;
            border-radius: 15px;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
        }

        .card h3 {
            font-size: 40px;
            color: #fff;
            margin-bottom: 5px;
        }

        .card p {
            color: #ddd;
            font-size: 14px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .feature-box {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 25px;
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            transition: 0.3s;
        }

        .feature-box:hover {
            transform: translateY(-6px);
        }

        .feature-box h4 {
            margin-bottom: 10px;
            color: #fff;
        }

        .feature-box a {
            margin-top: 10px;
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            background: #0d6efd;
            color: white;
            margin-right: 10px;
        }

        canvas {
            width: 100%;
            margin-top: 50px;
            background: rgba(255, 255, 255, 0.08);
            padding: 20px;
            border-radius: 14px;
        }

        @media (max-width: 768px) {
            .card { flex: 1 1 100%; }
        }
    </style>
</head>
<body>

<header>
    <h1>🚀 Admin Control Center</h1>
    <p>Monitor & Manage Everything in One Place</p>
</header>

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

    <div class="grid">
        <div class="feature-box">
            <h4>📘 Exam Center</h4>
            <a href="exam-center/admin/create_exam.php">Create Exam</a>
            <a href="exam-center/admin/exam_dashboard.php">Manage</a>
        </div>
        <div class="feature-box">
            <h4>📒 Assignments</h4>
            <a href="assignment/admin_assignment_dashboard.php">New Assignment</a>
            <a href="assignment/view_submissions.php">View Submissions</a>
        </div>
        <div class="feature-box">
            <h4>📚 Study Center</h4>
            <a href="study-center/assign_material.php">Upload PDF</a>
            <a href="study-center/view_materials_admin.php">Manage</a>
        </div>
        <div class="feature-box">
            <h4>📊 Results</h4>
            <a href="exam-center/admin/exam_dashboard.php">Declare</a>
            <a href="exam-center/admin/view_results_admin.php">Review</a>
        </div>
        <div class="feature-box">
            <h4> Admin </h4>
            <a href="declare_result.php">Manage Student</a>
            <a href="view_results_admin.php">edit_student</a>
            <a href="">view Student</a>
            <a href="">Add Student</a>
        </div>
        <div class="feature-box">
            <h4>Batch</h4>
            <a href="declare_result.php">Create Batch</a>
            <a href="view_results_admin.php">Edit Batch</a>
            <a href="view_results_admin.php">view Batch</a>
            <a href="view_results_admin.php">View Batches</a>
        </div>
        <div class="feature-box">
            <h4>Study Center</h4>
            <a href="declare_result.php">View Materials Student</a>
            <a href="view_results_admin.php">study Material Data</a>
        </div>
    </div>

    <canvas id="summaryChart" height="100"></canvas>

</div>

<script>
    const ctx = document.getElementById('summaryChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Students', 'Batches', 'Exams', 'Assignments', 'Materials'],
            datasets: [{
                label: 'Count Overview',
                data: [<?= $total_students ?>, <?= $total_batches ?>, <?= $total_exams ?>, <?= $total_assignments ?>, <?= $total_materials ?>],
                backgroundColor: ['#0d6efd', '#6610f2', '#198754', '#ffc107', '#dc3545'],
                borderRadius: 8
            }]
        },
        options: {
            plugins: { legend: { display: false } },
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
