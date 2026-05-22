<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../database_connection/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

/*
---------------------------------
GET STUDENT INFO (FROM students + students26)
---------------------------------
Assumption:
students26 contains only: id (student_id reference)
---------------------------------
*/
$student = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT s.*
     FROM students s
     INNER JOIN students26 s26 ON s.student_id = s26.id
     WHERE s.student_id = '$student_id'"
));

/*
---------------------------------
GET EXAMS
---------------------------------
*/
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
/* (YOUR SAME CSS - NO CHANGE NEEDED) */
.button {
    background-color: #1a56db;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="container">

<h2>📊 All Exam Reports</h2>

<p><b>Name:</b> <?php echo $student['name'] ?? 'Not Found'; ?></p>

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
    $class = ($percent >= 33) ? "status-pass" : "status-fail";
?>

<tr>
    <td><?php echo $row['exam_name']; ?></td>
    <td><?php echo $score; ?></td>
    <td><?php echo $total; ?></td>
    <td><?php echo $percent; ?>%</td>
    <td class="<?php echo $class; ?>"><?php echo $status; ?></td>
    <td>
        <a class="button" href="exam_details.php?exam_id=<?php echo $row['exam_id']; ?>">
            View Details
        </a>
    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>