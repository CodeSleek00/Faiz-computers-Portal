<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];

$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();

/* ===================== FIXED RESULT QUERY ===================== */
$results = $conn->query("
    SELECT 
        s.score, s.submitted_at,
        st.name, st.enrollment_id
    FROM exam_submissions s
    JOIN students st 
        ON s.student_id = st.student_id 
       AND s.student_table = 'students'
    WHERE s.exam_id = $exam_id

    UNION ALL

    SELECT 
        s.score, s.submitted_at,
        st26.name, st26.enrollment_id
    FROM exam_submissions s
    JOIN students26 st26 
        ON s.student_id = st26.id
       AND s.student_table = 'students26'
    WHERE s.exam_id = $exam_id

    ORDER BY submitted_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exam Results</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    padding: 40px 20px;
    font-family: 'Poppins', sans-serif;
    background: #f1f5f9;
}
.container {
    max-width: 1000px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
    flex-wrap: wrap;
}
.header h2 {
    margin: 0;
    color: #4f46e5;
}
.exam-meta {
    font-size: 14px;
    color: #6b7280;
}
table {
    width: 100%;
    border-collapse: collapse;
}
thead { background: #eef2ff; }
th, td {
    padding: 14px;
    font-size: 14px;
}
th {
    text-align: left;
    color: #3730a3;
}
tr:hover { background: #f9fafb; }
.score {
    font-weight: 600;
    color: #16a34a;
}
.no-data {
    text-align: center;
    padding: 20px;
    color: #6b7280;
}
.footer {
    margin-top: 20px;
    font-size: 13px;
    text-align: right;
    color: #6b7280;
}
</style>
</head>

<body>
<div class="container">

    <div class="header">
        <h2>🧾 Exam Results</h2>
        <div class="exam-meta">
            <strong><?= htmlspecialchars($exam['exam_name']) ?></strong><br>
            Total Questions: <?= $exam['total_questions'] ?>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Enrollment No</th>
            <th>Score</th>
            <th>Submitted On</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($results->num_rows > 0) {
            $i = 1;
            while ($r = $results->fetch_assoc()) {
        ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['enrollment_id']) ?></td>
                <td class="score"><?= $r['score'] ?> / <?= $exam['total_questions'] ?></td>
                <td><?= date('d M Y, h:i A', strtotime($r['submitted_at'])) ?></td>
            </tr>
        <?php } } else { ?>
            <tr>
                <td colspan="5" class="no-data">No submissions found</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div class="footer">
        Generated on <?= date('d M Y ') ?>
    </div>

</div>
</body>
</html>
