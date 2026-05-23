<?php
include '../../database_connection/db_connect.php';
session_start();

/* =====================================================
CHECK LOGIN
===================================================== */
$enrollment_id = $_SESSION['enrollment_id'] ?? null;

if (!$enrollment_id) {
    header("Location: ../../login-system/login.php");
    exit;
}

/* =====================================================
FETCH STUDENT FROM BOTH TABLES
students      => student_id
students26    => id
===================================================== */

$student = null;

/* CHECK students TABLE */
$query1 = mysqli_query($conn, "
    SELECT 
        'students' AS student_table,
        student_id,
        enrollment_id,
        name
    FROM students
    WHERE enrollment_id = '$enrollment_id'
    LIMIT 1
");

if (mysqli_num_rows($query1) > 0) {

    $student = mysqli_fetch_assoc($query1);

} else {

    /* CHECK students26 TABLE */
    $query2 = mysqli_query($conn, "
        SELECT 
            'students26' AS student_table,
            id AS student_id,
            enrollment_id,
            name
        FROM students26
        WHERE enrollment_id = '$enrollment_id'
        LIMIT 1
    ");

    if (mysqli_num_rows($query2) > 0) {
        $student = mysqli_fetch_assoc($query2);
    }
}

/* STUDENT NOT FOUND */
if (!$student) {
    die("Student not found.");
}

/* =====================================================
STORE STUDENT DATA
===================================================== */

$student_id       = $student['student_id'];
$student_name     = $student['name'];
$student_table    = $student['student_table'];

$submitted_exam_id = intval($_GET['exam_id'] ?? 0);

/* =====================================================
FETCH DECLARED RESULTS ONLY
===================================================== */

$sql = "
    SELECT 
        s.submission_id,
        s.exam_id,
        s.score,
        s.submitted_at,

        e.exam_name,
        e.total_questions,
        e.marks_per_question,
        e.created_at,
        e.result_declared

    FROM exam_submissions s

    LEFT JOIN exams e
        ON s.exam_id = e.exam_id

    WHERE 
        s.student_id = '$student_id'
        AND s.student_table = '$student_table'
        AND e.result_declared = 1

    ORDER BY s.submission_id DESC
";

$results = mysqli_query($conn, $sql);

if (!$results) {
    die("SQL Error: " . mysqli_error($conn));
}

$total_results = mysqli_num_rows($results);

?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Results</title>

<style>

body{
    font-family:Arial, sans-serif;
    background:#f4f7fb;
    padding:20px;
}

.container{
    max-width:1100px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:15px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

h2{
    color:#1a56db;
    margin-bottom:10px;
}

.info{
    margin-bottom:20px;
    line-height:28px;
}

.info span{
    color:#1a56db;
    font-weight:bold;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#1a56db;
    color:#fff;
    padding:12px;
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #eee;
}

.pass{
    color:green;
    font-weight:bold;
}

.fail{
    color:red;
    font-weight:bold;
}

.btn{
    background:#1a56db;
    color:white;
    padding:7px 14px;
    border-radius:6px;
    text-decoration:none;
}

.no-data{
    text-align:center;
    padding:25px;
    color:#777;
}

</style>

</head>
<body>

<div class="container">

    <h2>📊 My Exam Results</h2>

    <div class="info">
        <div><b>Name:</b> <span><?php echo $student_name; ?></span></div>
        <div><b>Enrollment:</b> <span><?php echo $enrollment_id; ?></span></div>
        <div><b>Student Table:</b> <span><?php echo $student_table; ?></span></div>
        <div><b>Total Results:</b> <span><?php echo $total_results; ?></span></div>
    </div>

    <table>

        <tr>
            <th>Exam Name</th>
            <th>Score</th>
            <th>Total Marks</th>
            <th>Percentage</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Action</th>
        </tr>

        <?php

        if ($total_results > 0) {

            while ($row = mysqli_fetch_assoc($results)) {

                $score = $row['score'];

                $total_marks = $row['total_questions'] * $row['marks_per_question'];

                $obtained_marks = $score * $row['marks_per_question'];

                $percentage = ($total_marks > 0)
                    ? round(($obtained_marks / $total_marks) * 100, 2)
                    : 0;

                $status = ($percentage >= 33) ? "PASS" : "FAIL";

                $status_class = ($percentage >= 33)
                    ? "pass"
                    : "fail";
        ?>

        <tr>

            <td>
                <?php echo htmlspecialchars($row['exam_name']); ?>
            </td>

            <td>
                <?php echo $obtained_marks; ?>
            </td>

            <td>
                <?php echo $total_marks; ?>
            </td>

            <td>
                <?php echo $percentage; ?>%
            </td>

            <td class="<?php echo $status_class; ?>">
                <?php echo $status; ?>
            </td>

            <td>
                <?php echo date("d M Y h:i A", strtotime($row['submitted_at'])); ?>
            </td>

            <td>
                <a class="btn"
                   href="exam_details.php?exam_id=<?php echo $row['exam_id']; ?>">
                   View
                </a>
            </td>

        </tr>

        <?php
            }

        } else {

            echo "
            <tr>
                <td colspan='7' class='no-data'>
                    No declared results found.
                </td>
            </tr>";
        }

        ?>

    </table>

</div>

</body>
</html>