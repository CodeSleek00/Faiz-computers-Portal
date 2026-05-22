<?php
session_start();
include '../db.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];
$exam_id = $_GET['exam_id'];

/*
-------------------------
GET EXAM INFO
-------------------------
*/
$exam = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM exams WHERE exam_id = '$exam_id'"
));

/*
-------------------------
GET ANSWERS
-------------------------
*/
$answers = mysqli_query($conn, "
    SELECT *
    FROM student_answers
    WHERE student_id = '$student_id'
    AND exam_id = '$exam_id'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Details</title>

<style>
body{font-family:Arial;background:#f5f5f5;padding:20px}
.container{max-width:1000px;margin:auto;background:#fff;padding:20px;border-radius:10px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:10px;text-align:center}
th{background:#222;color:#fff}
.correct{color:green;font-weight:bold}
.wrong{color:red;font-weight:bold}
</style>
</head>

<body>

<div class="container">

<h2>📘 <?php echo $exam['exam_name']; ?> - Detailed Report</h2>

<table>
<tr>
    <th>Question ID</th>
    <th>Your Answer</th>
    <th>Status</th>
    <th>Time</th>
</tr>

<?php
while ($row = mysqli_fetch_assoc($answers)) {

    $status = ($row['is_correct'] == 1) ? "CORRECT" : "WRONG";
    $class = ($row['is_correct'] == 1) ? "correct" : "wrong";
?>

<tr>
    <td><?php echo $row['question_id']; ?></td>
    <td><?php echo $row['selected_option']; ?></td>
    <td class="<?php echo $class; ?>"><?php echo $status; ?></td>
    <td><?php echo $row['submitted_at']; ?></td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>