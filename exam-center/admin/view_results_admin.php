<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];

// Fetch exam info
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();

// Fetch all submissions with unified student info
$results = $conn->query("
    SELECT es.score, es.submitted_at, st.name, st.enrollment_id
    FROM exam_submissions es
    LEFT JOIN students st ON es.student_id = st.student_id AND es.student_table = 'students'
    LEFT JOIN students26 st2 ON es.student_id = st2.id AND es.student_table = 'students26'
    ORDER BY es.submitted_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Results</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
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
        <?php while ($r = $results->fetch_assoc()) { 
            // Choose correct name/enrollment depending on which table is not null
            $name = $r['name'] ?? $r['name']; // if needed, adjust later
            $enroll = $r['enrollment_id'] ?? $r['enrollment_id'];
        ?>
        <tr>
            <td><?= htmlspecialchars($name) ?></td>
            <td><?= htmlspecialchars($enroll) ?></td>
            <td><?= $r['score'] ?> / <?= $exam['total_questions'] ?></td>
            <td><?= date('d M Y, h:i A', strtotime($r['submitted_at'])) ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
