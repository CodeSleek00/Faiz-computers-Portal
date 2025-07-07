<?php
include '../database_connection/db_connect.php';

// Count total assignments
$total_assignments = $conn->query("SELECT COUNT(*) as total FROM assignments")->fetch_assoc()['total'];
$total_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions")->fetch_assoc()['total'];
$pending_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions WHERE marks_awarded IS NULL")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Assignment Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 40px;
        }

        .dashboard {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            padding: 30px 25px;
            border-radius: 16px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.06);
            text-align: center;
        }

        .card h2 {
            font-size: 38px;
            margin-bottom: 10px;
            color: #007bff;
        }

        .card p {
            font-size: 16px;
            color: #666;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .action-btn {
            background: #007bff;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: 0.3s ease;
            min-width: 220px;
        }

        .action-btn:hover {
            background: #0056b3;
        }

        @media screen and (max-width: 600px) {
            .action-btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h1>📚 Assignment Control Center</h1>

    <div class="cards">
        <div class="card">
            <h2><?= $total_assignments ?></h2>
            <p>Total Assignments</p>
        </div>
        <div class="card">
            <h2><?= $total_submissions ?></h2>
            <p>Total Submissions</p>
        </div>
        <div class="card">
            <h2><?= $pending_submissions ?></h2>
            <p>Pending Grading</p>
        </div>
    </div>

    <div class="actions">
        <a href="admin_assignments.php" class="action-btn">➕ Create Assignment</a>
        <a href="assign_assignment.php" class="action-btn">📤 Assign to Students</a>
        <a href="view_submissions.php" class="action-btn">📂 View Submissions</a>
        <a href="view_batches.php" class="action-btn">👥 Manage Batches</a>
        <a href="view_students.php" class="action-btn">🧑‍🎓 Manage Students</a>
    </div>
</div>

</body>
</html>
