<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../database_connection/db_connect.php'; // adjust path if needed
// 🔐 Check login
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access. Please login first.");
}

$student_id = $_SESSION['student_id'];

/*
-------------------------
GET STUDENT INFO
-------------------------
*/
$studentQuery = "SELECT * FROM students WHERE student_id = '$student_id'";
$studentResult = mysqli_query($conn, $studentQuery);

if (!$studentResult || mysqli_num_rows($studentResult) == 0) {
    die("Student not found.");
}

$student = mysqli_fetch_assoc($studentResult);

/*
-------------------------
GET EXAM REPORTS
-------------------------
*/
$reportQuery = "
    SELECT 
        es.submission_id,
        es.student_id,
        es.exam_id,
        es.score,
        es.submitted_at,
        es.is_declared,
        e.exam_name,
        e.total_questions,
        e.marks_per_question
    FROM exam_submissions es
    LEFT JOIN exams e ON es.exam_id = e.exam_id
    WHERE es.student_id = '$student_id'
    ORDER BY es.submission_id DESC
";

$reportResult = mysqli_query($conn, $reportQuery);

if (!$reportResult) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Exam Reports</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        h2 {
            text-align: center;
        }

        .info {
            background: #eee;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #333;
            color: white;
        }

        .pass {
            color: green;
            font-weight: bold;
        }

        .fail {
            color: red;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            color: red;
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>📊 My Exam Reports</h2>

    <div class="info">
        <p><b>Name:</b> <?php echo $student['name']; ?></p>
        <p><b>Student ID:</b> <?php echo $student['student_id']; ?></p>
    </div>

    <table>
        <tr>
            <th>Exam Name</th>
            <th>Score</th>
            <th>Total Questions</th>
            <th>Percentage</th>
            <th>Status</th>
            <th>Submitted At</th>
        </tr>

        <?php
        if (mysqli_num_rows($reportResult) > 0) {
            while ($row = mysqli_fetch_assoc($reportResult)) {

                $score = $row['score'];
                $total = $row['total_questions'];

                $percentage = ($total > 0)
                    ? round(($score / $total) * 100, 2)
                    : 0;

                $status = ($percentage >= 33) ? "PASS" : "FAIL";
                $class = ($percentage >= 33) ? "pass" : "fail";
        ?>
            <tr>
                <td><?php echo $row['exam_name']; ?></td>
                <td><?php echo $score; ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $percentage; ?>%</td>
                <td class="<?php echo $class; ?>"><?php echo $status; ?></td>
                <td><?php echo $row['submitted_at']; ?></td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='6' class='no-data'>No exam reports found</td></tr>";
        }
        ?>

    </table>

</div>

</body>
</html>