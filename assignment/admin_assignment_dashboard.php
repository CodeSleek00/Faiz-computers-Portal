<?php
include '../database_connection/db_connect.php';

// Count total assignments
$total_assignments = $conn->query("SELECT COUNT(*) as total FROM assignments")->fetch_assoc()['total'];
$total_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions")->fetch_assoc()['total'];
$pending_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions WHERE marks_awarded IS NULL")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Assignment Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6C63FF;
            --secondary: #F0F2F5;
            --text-dark: #2C3E50;
            --text-light: #7F8C8D;
            --card-bg: #fff;
            --hover-bg: #554ef0;
            --border-radius: 16px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--secondary);
            color: var(--text-dark);
            padding: 40px 20px;
        }

        .dashboard {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 30px;
            font-weight: 700;
            color: var(--primary);
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .card p {
            font-size: 16px;
            color: var(--text-light);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: center;
        }

        .action-btn {
            background: var(--primary);
            color: #fff;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.3s ease;
            box-shadow: var(--shadow);
        }

        .action-btn:hover {
            background: var(--hover-bg);
        }

        .search-section {
            margin: 40px auto;
            max-width: 600px;
            text-align: center;
        }

        .search-section input {
            width: 70%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid var(--primary);
            border-radius: 8px;
            outline: none;
        }

        .search-section button {
            padding: 12px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            margin-left: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .search-section button:hover {
            background: var(--hover-bg);
        }

        @media screen and (max-width: 768px) {
            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .search-section input {
                width: 100%;
                margin-bottom: 10px;
            }

            .search-section button {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h1><i class="fas fa-chalkboard-teacher"></i> Assignment Control Center</h1>

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

    <div class="search-section">
        <form action="search_assignments.php" method="GET">
            <input type="text" name="query" placeholder="Search by title, student, or batch...">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <div class="actions">
        <a href="admin_assignments.php" class="action-btn"><i class="fas fa-plus-circle"></i> Create Assignment</a>
        <a href="assign_assignment.php" class="action-btn"><i class="fas fa-paper-plane"></i> Assign to Students</a>
        <a href="view_submissions.php" class="action-btn"><i class="fas fa-folder-open"></i> View Submissions</a>
        <a href="view_batches.php" class="action-btn"><i class="fas fa-users"></i> Manage Batches</a>
        <a href="view_students.php" class="action-btn"><i class="fas fa-user-graduate"></i> Manage Students</a>
    </div>
</div>

</body>
</html>
