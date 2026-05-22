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
GET STUDENT DATA (SAFE FIX)
students26 contains ONLY id
so we validate student exists in students table
---------------------------------
*/
$student_check = mysqli_query($conn, "
    SELECT * FROM students 
    WHERE student_id = '$student_id'
    LIMIT 1
");

$student = mysqli_fetch_assoc($student_check);

if (!$student) {
    die("Student not found in students table.");
}

/*
---------------------------------
GET EXAM REPORTS
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
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f0f4fa;
    padding: 24px;
    min-height: 100vh;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 32px 28px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

h2 {
    font-size: 1.6rem;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th {
    background: #1a56db;
    color: white;
    padding: 14px;
    font-size: 0.85rem;
}

td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #e8edf5;
}

.status-pass {
    color: #1a56db;
    font-weight: 700;
    background: #e8f0fe;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-block;
}

.status-fail {
    color: #94a3b8;
    font-weight: 600;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-block;
}

.button {
    background-color: #1a56db;
    color: white;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
}
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
if (mysqli_num_rows($exams) > 0) {

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
    <td><span class="<?php echo $class; ?>"><?php echo $status; ?></span></td>
    <td>
        <a class="button" href="exam_details.php?exam_id=<?php echo $row['exam_id']; ?>">
            View
        </a>
    </td>
</tr>

<?php
    }

} else {
    echo "<tr><td colspan='6'>No exam records found</td></tr>";
}
?>

</table>

</div>

</body>
</html>