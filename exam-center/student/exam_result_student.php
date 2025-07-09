<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch all declared exam results for this student
$sql = "
    SELECT e.exam_name, e.total_questions, s.score
    FROM exam_submissions s
    JOIN exams e ON s.exam_id = e.exam_id
    WHERE s.student_id = $student_id AND e.result_declared = 1
    ORDER BY e.created_at DESC
";
$results = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Exam Results</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef2f5; padding: 50px; }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        h2 { text-align: center; margin-bottom: 30px; color: #333; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }
        th { background: #f8f8f8; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 <?= htmlspecialchars($student['name']) ?> - Your Exam Results</h2>

    <?php if ($results->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Exam Name</th>
                    <th>Score</th>
                    <th>Total Questions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $results->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['exam_name']) ?></td>
                        <td><?= $row['score'] ?></td>
                        <td><?= $row['total_questions'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p style="text-align:center;">📭 No declared results available yet.</p>
    <?php } ?>
</div>

</body>
</html>
