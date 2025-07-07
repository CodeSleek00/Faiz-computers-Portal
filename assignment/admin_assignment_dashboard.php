<?php
include '../database_connection/db_connect.php';

// Count totals
$total_assignments = $conn->query("SELECT COUNT(*) as total FROM assignments")->fetch_assoc()['total'] ?? 0;
$total_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions")->fetch_assoc()['total'] ?? 0;
$pending_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions WHERE marks_awarded IS NULL")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #5D5FEF;
            --secondary: #f8f9fc;
            --text: #2f3542;
            --muted: #778ca3;
            --white: #ffffff;
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            --radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--secondary);
            color: var(--text);
            padding: 30px 16px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 40px;
            color: var(--primary);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .card {
            background: var(--white);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card h2 {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .card p {
            color: var(--muted);
            font-size: 16px;
        }

        .search-bar {
            max-width: 600px;
            margin: 0 auto 40px;
            display: flex;
            gap: 12px;
        }

        .search-bar input {
            flex: 1;
            padding: 12px;
            border-radius: var(--radius);
            border: 2px solid var(--primary);
            font-size: 16px;
        }

        .search-bar button {
            padding: 12px 20px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 16px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
        }

        .action-btn {
            background: var(--primary);
            color: var(--white);
            padding: 14px 20px;
            border-radius: var(--radius);
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            box-shadow: var(--shadow);
            transition: 0.3s;
        }

        .action-btn:hover {
            background: #4446db;
        }

        @media screen and (max-width: 600px) {
            .search-bar {
                flex-direction: column;
            }

            .search-bar input,
            .search-bar button {
                width: 100%;
            }

            .action-btn {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-chalkboard"></i> Assignment Admin Dashboard</h1>

    <div class="grid">
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

    <form class="search-bar" action="search_assignments.php" method="GET">
        <input type="text" name="query" placeholder="Search by title, batch, student...">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>

    <div class="actions">
        <a href="admin_assignments.php" class="action-btn"><i class="fas fa-plus"></i> Create Assignment</a>
        <a href="assign_assignment.php" class="action-btn"><i class="fas fa-share"></i> Assign to Students</a>
        <a href="view_submissions.php" class="action-btn"><i class="fas fa-eye"></i> View Submissions</a>
        <a href="view_batches.php" class="action-btn"><i class="fas fa-layer-group"></i> Manage Batches</a>
        <a href="view_students.php" class="action-btn"><i class="fas fa-user-graduate"></i> Manage Students</a>
    </div>
</div>
</body>
</html>
