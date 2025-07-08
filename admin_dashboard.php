<?php
include 'database_connection/db_connect.php';

// Total students
$students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
// Total batches
$batches = $conn->query("SELECT COUNT(*) AS total FROM batches")->fetch_assoc()['total'];
// Total exams
$exams = $conn->query("SELECT COUNT(*) AS total FROM exams")->fetch_assoc()['total'];
// Total assignments
$assignments = $conn->query("SELECT COUNT(*) AS total FROM assignments")->fetch_assoc()['total'];
// Total study materials
$materials = $conn->query("SELECT COUNT(*) AS total FROM study_material")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #f1f5f9;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            flex: 1 1 calc(33% - 20px);
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.05);
            text-align: center;
        }
        .card h2 {
            margin: 10px 0;
            font-size: 26px;
            color: #007bff;
        }
        .card p {
            color: #555;
        }
        .sections {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .section {
            flex: 1 1 calc(50% - 20px);
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .section h3 {
            margin-top: 0;
            color: #333;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            margin-top: 15px;
            border-radius: 6px;
            text-decoration: none;
        }
        .btn:hover {
            background: #0056b3;
        }
        @media (max-width: 768px) {
            .card, .section {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>👨‍🏫 Admin Dashboard</h1>
</div>

<div class="container">

    <div class="cards">
        <div class="card">
            <h2><?= $students ?></h2>
            <p>Total Students</p>
        </div>
        <div class="card">
            <h2><?= $batches ?></h2>
            <p>Total Batches</p>
        </div>
        <div class="card">
            <h2><?= $exams ?></h2>
            <p>Total Exams</p>
        </div>
        <div class="card">
            <h2><?= $assignments ?></h2>
            <p>Total Assignments</p>
        </div>
        <div class="card">
            <h2><?= $materials ?></h2>
            <p>Study Materials</p>
        </div>
    </div>

    <div class="sections">
        <div class="section">
            <h3>📋 Exam Center</h3>
            <a href="create_exam.php" class="btn">Create Exam</a>
            <a href="exam_dashboard.php" class="btn">Manage Exams</a>
        </div>

        <div class="section">
            <h3>📝 Assignment Center</h3>
            <a href="../assignments/admin_assignments.php" class="btn">Create Assignment</a>
            <a href="../assignments/view_submissions.php" class="btn">View Submissions</a>
        </div>

        <div class="section">
            <h3>📚 Study Center</h3>
            <a href="../study-center/upload_material.php" class="btn">Upload Material</a>
            <a href="../study-center/view_materials_admin.php" class="btn">View Materials</a>
        </div>

        <div class="section">
            <h3>🎯 Results</h3>
            <a href="view_results_admin.php" class="btn">View All Results</a>
            <a href="declare_result.php" class="btn">Declare Results</a>
        </div>
    </div>

</div>

</body>
</html>
