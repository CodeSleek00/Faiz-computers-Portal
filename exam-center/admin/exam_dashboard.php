<?php
include '../../database_connection/db_connect.php';

// Fetch all exams
$exams = $conn->query("SELECT * FROM exams ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Exam Dashboard</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f4f4f4; }
        .container {
            max-width: 1000px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            border: 1px solid #ccc; padding: 10px;
            text-align: left;
        }
        th { background: #f0f0f0; }
        a.btn {
            padding: 6px 12px; border-radius: 6px; text-decoration: none; color: white;
        }
        .declare { background: green; }
        .clear { background: red; }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 All Exams</h2>
    <table>
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Total Questions</th>
                <th>Duration</th>
                <th>Created</th>
                <th>Result</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($exam = $exams->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($exam['exam_name']) ?></td>
                <td><?= $exam['total_questions'] ?></td>
                <td><?= $exam['duration'] ?> min</td>
                <td><?= date("d M Y", strtotime($exam['created_at'])) ?></td>
                <td>
                    <?php if ($exam['result_declared']) { ?>
                        ✅ Declared
                        <a href="undeclare_result.php?exam_id=<?= $exam['exam_id'] ?>" class="btn clear" onclick="return confirm('Remove result declaration?')">❌ Clear</a>
                    <?php } else { ?>
                        ❌ Not Declared
                        <a href="declare_result.php?exam_id=<?= $exam['exam_id'] ?>" class="btn declare">✅ Declare Now</a>
                    <?php } ?>
                </td>
                <td>
                    <a href="view_results_admin.php?exam_id=<?= $exam['exam_id'] ?>">📊 View</a> |
                    <a href="delete_exam.php?exam_id=<?= $exam['exam_id'] ?>" onclick="return confirm('Are you sure?')">🗑 Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
