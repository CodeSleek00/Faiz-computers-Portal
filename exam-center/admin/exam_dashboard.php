<?php
include '../../database_connection/db_connect.php';

$exams = $conn->query("SELECT * FROM exams ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Dashboard</title>
    <style>
        body { font-family: Arial; background: #f2f2f2; padding: 40px; }
        .container {
            max-width: 1000px; margin: auto; background: white; padding: 30px;
            border-radius: 14px; box-shadow: 0 6px 18px rgba(0,0,0,0.07);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            padding: 12px; border-bottom: 1px solid #ddd; text-align: left;
        }
        th { background-color: #f7f7f7; }
        .btn {
            padding: 6px 12px; border: none; border-radius: 6px;
            color: white; text-decoration: none; margin-right: 10px;
        }
        .delete { background: #dc3545; }
        .view { background: #17a2b8; }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Exam Dashboard</h2>
    <table>
        <tr>
            <th>Exam Name</th>
            <th>Questions</th>
            <th>Duration (min)</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $exams->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['exam_name'] ?></td>
            <td><?= $row['total_questions'] ?></td>
            <td><?= $row['duration'] ?></td>
            <td><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
            <td>
                <a href="view_results_admin.php?exam_id=<?= $row['exam_id'] ?>" class="btn view">View Results</a>
                <a href="delete_exam.php?exam_id=<?= $row['exam_id'] ?>" class="btn delete" onclick="return confirm('Delete this exam and all data?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
