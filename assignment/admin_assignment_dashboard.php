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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .filter-dropdown {
            position: relative;
            min-width: 200px;
        }

        .filter-dropdown select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            appearance: none;
            background-color: white;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.75rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .stat-card h3 {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-card .trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            color: var(--gray);
            margin-top: auto;
        }

        .stat-card .trend.up {
            color: #10b981;
        }

        .stat-card .trend.down {
            color: var(--danger);
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
        }

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border-top: 4px solid var(--primary);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .action-card i {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .action-card h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .action-card p {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1.25rem;
        }

        .action-card .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            background: var(--primary);
            color: white;
            margin-top: auto;
            width: 100%;
            transition: var(--transition);
        }

        .action-card .btn:hover {
            background: var(--secondary);
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.75rem;
            }
            
            .search-filter-bar {
                flex-direction: column;
            }
            
            .search-box, .filter-dropdown {
                min-width: 100%;
            }
            
            .stats-grid, .action-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">
                <i class="fas fa-tasks"></i>
                Assignment Control Center
            </h1>
            <p class="page-subtitle">Manage all assignments, submissions, and grading in one place</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search assignments...">
            </div>
            <div class="filter-dropdown">
                <select>
                    <option value="">All Batches</option>
                    <option value="1">Batch 2023</option>
                    <option value="2">Batch 2024</option>
                    <option value="3">Batch 2025</option>
                </select>
            </div>
            <div class="filter-dropdown">
                <select>
                    <option value="">All Status</option>
                    <option value="pending">Pending Grading</option>
                    <option value="graded">Graded</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Assignments</h3>
                <div class="value"><?= $total_assignments ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>12% from last month</span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Total Submissions</h3>
                <div class="value"><?= $total_submissions ?></div>
                <div class="trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>8% from last week</span>
                </div>
            </div>
            <div class="stat-card">
                <h3>Pending Grading</h3>
                <div class="value"><?= $pending_submissions ?></div>
                <div class="trend down">
                    <i class="fas fa-arrow-down"></i>
                    <span>3% remaining</span>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="action-grid">
            <div class="action-card">
                <i class="fas fa-plus"></i>
                <h3>Create Assignment</h3>
                <p>Create new assignments with detailed instructions and deadlines</p>
                <a href="admin_assignments.php" class="btn">Create</a>
            </div>
            <div class="action-card">
                <i class="fas fa-paper-plane"></i>
                <h3>Assign to Students</h3>
                <p>Distribute assignments to specific batches or students</p>
                <a href="assign_assignment.php" class="btn">Assign</a>
            </div>
            <div class="action-card">
                <i class="fas fa-file-alt"></i>
                <h3>View Submissions</h3>
                <p>Review and grade student submissions</p>
                <a href="view_submissions.php" class="btn">View</a>
            </div>
            <div class="action-card">
                <i class="fas fa-users"></i>
                <h3>Manage Batches</h3>
                <p>Organize students into batches for easier management</p>
                <a href="view_batches.php" class="btn">Manage</a>
            </div>
            <div class="action-card">
                <i class="fas fa-user-graduate"></i>
                <h3>Manage Students</h3>
                <p>Add, edit, or remove student profiles</p>
                <a href="view_students.php" class="btn">Manage</a>
            </div>
        </div>
    </div>
</body>
</html>