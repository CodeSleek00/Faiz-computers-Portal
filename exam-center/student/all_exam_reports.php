<?php
session_start();
include '../../database_connection/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

/*
---------------------------------
GET STUDENT NAME FROM students26
- students26 uses 'id' column
---------------------------------
*/
$result = mysqli_query($conn, "SELECT name FROM students26 WHERE id = '$student_id' LIMIT 1");
$student = mysqli_fetch_assoc($result);

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
}
.container {
    max-width: 1200px;
    margin: auto;
    background: #fff;
    padding: 28px;
    border-radius: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
h2 {
    color: #1a56db;
    margin-bottom: 10px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th {
    background: #1a56db;
    color: #fff;
    padding: 12px;
}
td {
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}
.status-pass {
    background: #e8f0fe;
    color: #1a56db;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}
.status-fail {
    background: #f1f5f9;
    color: #94a3b8;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
}
.button {
    background: #1a56db;
    color: white;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
}
</style>
</head>
<body>
<div class="container">
    <h2>📊 All Exam Reports</h2>
    <p><b>Name:</b> <?php echo $student['name'] ?? 'Unknown Student'; ?></p>

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
                    View Details
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