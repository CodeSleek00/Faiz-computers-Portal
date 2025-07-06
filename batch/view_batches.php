<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid batch ID.");
}

$batch_id = intval($_GET['id']);

// Fetch batch info
$batch_result = $conn->query("SELECT * FROM batches WHERE batch_id = $batch_id");
if ($batch_result->num_rows == 0) {
    die("❌ Batch not found.");
}
$batch = $batch_result->fetch_assoc();

// Fetch students in batch
$students = $conn->query("
    SELECT s.name, s.enrollment_id, s.course
    FROM student_batches sb
    JOIN students s ON sb.student_id = s.student_id
    WHERE sb.batch_id = $batch_id
    ORDER BY s.name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Batch - <?= htmlspecialchars($batch['batch_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #eef2f5;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .batch-details {
            text-align: center;
            font-size: 16px;
            margin-bottom: 30px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f6f9fc;
            font-weight: 600;
            color: #333;
        }

        table tr:hover {
            background: #f9fcff;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            font-weight: 500;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .empty {
            text-align: center;
            padding: 20px;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Batch: <?= htmlspecialchars($batch['batch_name']) ?></h2>
    <div class="batch-details">Timing: <?= htmlspecialchars($batch['timing']) ?></div>

    <?php if ($students->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Enrollment ID</th>
                    <th>Course</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $students->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="empty">No students assigned to this batch yet.</div>
    <?php } ?>

    <a class="back-link" href="view_batches.php">⬅ Back to All Batches</a>
</div>

</body>
</html>
