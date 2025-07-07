<?php
include '../database_connection/db_connect.php';

// Fetch stats
$total_assignments = $conn->query("SELECT COUNT(*) as total FROM assignments")->fetch_assoc()['total'];
$total_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions")->fetch_assoc()['total'];
$pending_submissions = $conn->query("SELECT COUNT(*) as total FROM assignment_submissions WHERE marks_awarded IS NULL")->fetch_assoc()['total'];

// Fetch batch list
$batch_result = $conn->query("SELECT DISTINCT batch_name FROM batches");
$batches = [];
while($row = $batch_result->fetch_assoc()) {
    $batches[] = $row['batch_name'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assignment Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f4f9;
            margin: 0;
            padding: 30px;
        }

        .dashboard {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            color: #222;
        }

        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .card {
            background: #fff;
            flex: 1;
            min-width: 250px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            text-align: center;
        }

        .card h2 {
            font-size: 36px;
            color: #007bff;
            margin-bottom: 8px;
        }

        .card p {
            color: #555;
            font-size: 15px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 40px;
        }

        .action-btn {
            background-color: #007bff;
            color: #fff;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s ease-in-out;
            min-width: 220px;
            text-align: center;
        }

        .action-btn:hover {
            background-color: #0056b3;
        }

        .filter-section {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-form input, .filter-form select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            min-width: 200px;
        }

        .filter-form button {
            padding: 12px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .results {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .results table {
            width: 100%;
            border-collapse: collapse;
        }

        .results th, .results td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .results th {
            background-color: #f1f4f9;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h1>📚 Assignment Admin Dashboard</h1>

    <!-- Cards -->
    <div class="cards">
        <div class="card">
            <h2><?= $total_assignments ?></h2>
            <p>Total Assignments</p>
        </div>
        <div class="card">
            <h2><?= $total_submissions ?></h2>
            <p>Submissions Received</p>
        </div>
        <div class="card">
            <h2><?= $pending_submissions ?></h2>
            <p>Pending for Grading</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions">
        <a href="admin_assignments.php" class="action-btn">➕ Create Assignment</a>
        <a href="assign_assignment.php" class="action-btn">📤 Assign to Students</a>
        <a href="view_submissions.php" class="action-btn">📂 View Submissions</a>
        <a href="view_batches.php" class="action-btn">👥 Manage Batches</a>
        <a href="view_students.php" class="action-btn">🧑‍🎓 Manage Students</a>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form class="filter-form" method="GET" action="">
            <input type="text" name="student_name" placeholder="Search by Student Name" value="<?= $_GET['student_name'] ?? '' ?>">
            <select name="batch">
                <option value="">-- Select Batch --</option>
                <?php foreach ($batches as $batch): ?>
                    <option value="<?= $batch ?>" <?= (isset($_GET['batch']) && $_GET['batch'] == $batch) ? 'selected' : '' ?>>
                        <?= $batch ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <!-- Results Table -->
    <div class="results">
        <h3>📑 Assignment Report</h3>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Assignment Title</th>
                    <th>Batch</th>
                    <th>Marks</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($_GET) {
                    $name = $_GET['student_name'] ?? '';
                    $batch = $_GET['batch'] ?? '';

                    $query = "
                        SELECT s.name, a.title, s.batch, sub.marks_awarded
                        FROM assignment_submissions sub
                        JOIN my_student s ON s.student_id = sub.student_id
                        JOIN assignments a ON a.assignment_id = sub.assignment_id
                        WHERE 1
                    ";

                    if (!empty($name)) {
                        $query .= " AND s.name LIKE '%" . $conn->real_escape_string($name) . "%'";
                    }
                    if (!empty($batch)) {
                        $query .= " AND s.batch = '" . $conn->real_escape_string($batch) . "'";
                    }

                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status = $row['marks_awarded'] === null ? '⏳ Pending' : '✅ Graded';
                            echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['batch']}</td>
                                <td>" . ($row['marks_awarded'] ?? '-') . "</td>
                                <td>{$status}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No matching records found.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Use filters above to search assignments.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
