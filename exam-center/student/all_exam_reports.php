<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database_connection/db_connect.php'; // adjust path if needed

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

$student = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM students WHERE student_id = '$student_id'"
));

$exams = mysqli_query($conn, "
    SELECT es.*, e.exam_name, e.total_questions
    FROM exam_submissions es
    LEFT JOIN exams e ON es.exam_id = e.exam_id
    WHERE es.student_id = '$student_id'
    ORDER BY es.submission_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>My Exam Reports</title>

<style>
body{font-family:Arial;background:#f5f5f5;padding:20px}
.container{max-width:1000px;margin:auto;background:#fff;padding:20px;border-radius:10px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:10px;text-align:center}
th{background:#222;color:#fff}
.pass{color:green;font-weight:bold}
.fail{color:red;font-weight:bold}
a{color:blue;text-decoration:none}
</style>
</head>

<body>

<div class="container">

<h2>📊 All Exam Reports</h2>

<p><b>Name:</b> <?php echo $student['name']; ?></p>

<table>
<tr>
    <th>Exam Name</th>
    <th>Score</th>
    <th>Total</th>
    <th>Percentage</th>
    <th>Status</th>
    <th>Details</th>
</tr>

<?php
while ($row = mysqli_fetch_assoc($exams)) {

    $score = $row['score'];
    $total = $row['total_questions'];

    $percent = ($total > 0) ? round(($score / $total) * 100, 2) : 0;
    $status = ($percent >= 33) ? "PASS" : "FAIL";
    $class = ($percent >= 33) ? "pass" : "fail";
?>

<tr>
    <td><?php echo $row['exam_name']; ?></td>
    <td><?php echo $score; ?></td>
    <td><?php echo $total; ?></td>
    <td><?php echo $percent; ?>%</td>
    <td class="<?php echo $class; ?>"><?php echo $status; ?></td>
    <td>
        <a href="exam_details.php?exam_id=<?php echo $row['exam_id']; ?>">
            View Details
        </a>
    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>