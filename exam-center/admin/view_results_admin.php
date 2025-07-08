<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];

$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();

$results = $conn->query("
    SELECT s.*, st.name, st.enrollment_id 
    FROM exam_submissions s
    JOIN students st ON s.student_id = st.student_id
    WHERE s.exam_id = $exam_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Results</title>
    <style>
        body { font-family: Arial; background: #eef1f5; padding: 40px; }
        .container {
            max-width: 950px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            padding: 12px; border-bottom: 1px solid #ddd; text-align: left;
        }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>
<div class="container">
    <h2>🧾 Results - <?= htmlspecialchars($exam['exam_name']) ?></h2>
    <table>
        <tr>
            <th>Student Name</th>
            <th>Enrollment</th>
            <th>Score</th>
            <th>Submitted On</th>
        </tr>
        <?php while ($r = $results->fetch_assoc()) { ?>
        <tr>
            <td><?= $r['name'] ?></td>
            <td><?= $r['enrollment_id'] ?></td>
            <td><?= $r['score'] ?> / <?= $exam['total_questions'] ?></td>
            <td><?= date('d M Y, h:i A', strtotime($r['submitted_at'])) ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
