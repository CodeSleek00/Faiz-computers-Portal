<?php
session_start();
include 'database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];

/* ================= FIND STUDENT TABLE ================= */
$student = $conn->query("
    SELECT student_id, 'students' AS student_table 
    FROM students 
    WHERE enrollment_id='$enrollment_id'
")->fetch_assoc();

if (!$student) {
    $student = $conn->query("
        SELECT id AS student_id, 'students26' AS student_table 
        FROM students26 
        WHERE enrollment_id='$enrollment_id'
    ")->fetch_assoc();
}

if (!$student) {
    die("Student not found");
}

$student_id    = $student['student_id'];
$student_table = $student['student_table'];

/* ================= FETCH RESULT ================= */
$stmt = $conn->prepare("
    SELECT 
        e.exam_name,
        e.total_questions,
        e.marks_per_question,
        es.score,
        es.submitted_at
    FROM exam_submissions es
    INNER JOIN exams e ON e.exam_id = es.exam_id
    WHERE 
        es.student_id = ?
        AND es.student_table = ?
        AND es.is_declared = 1
        AND e.result_declared = 1
    ORDER BY es.score DESC
");

$stmt->bind_param("is", $student_id, $student_table);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>My Result</title>
<style>
body{font-family:Poppins;background:#f3f4f6;padding:20px}
.box{max-width:900px;margin:auto;background:#fff;padding:25px;border-radius:10px}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ddd;padding:10px;text-align:center}
th{background:#4f46e5;color:#fff}
.pass{color:green;font-weight:600}
.fail{color:red;font-weight:600}
</style>
</head>
<body>

<div class="box">
<h2>📄 My Exam Result</h2>

<?php if($result->num_rows > 0): ?>
<table>
<tr>
    <th>#</th>
    <th>Exam</th>
    <th>Score</th>
    <th>Total Marks</th>
    <th>Percentage</th>
    <th>Status</th>
</tr>

<?php 
$i=1;
while($row=$result->fetch_assoc()):
    $total_marks = $row['total_questions'] * $row['marks_per_question'];
    $percentage  = round(($row['score'] / $total_marks) * 100);
?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($row['exam_name']) ?></td>
    <td><?= $row['score'] ?></td>
    <td><?= $total_marks ?></td>
    <td><?= $percentage ?>%</td>
    <td class="<?= $percentage>=35?'pass':'fail' ?>">
        <?= $percentage>=35?'PASS':'FAIL' ?>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php else: ?>
<p>❌ Result not declared yet.</p>
<?php endif; ?>

</div>
</body>
</html>
