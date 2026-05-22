<?php
session_start();
include '../../database_connection/db_connect.php'; // adjust path if needed

// 🔐 Check login
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access. Please login first.");
}

$student_id = $_SESSION['student_id'];

/*
  STEP 1: Get student details
*/
$studentQuery = "SELECT * FROM students WHERE student_id = '$student_id'";
$studentResult = mysqli_query($conn, $studentQuery);

if (!$studentResult || mysqli_num_rows($studentResult) == 0) {
    die("Student not found.");
}

$student = mysqli_fetch_assoc($studentResult);

/*
  STEP 2: Fetch exam reports from correct table
  IMPORTANT: your real table is exam_submissions
*/
$reportQuery = "
    SELECT es.*, e.exam_name
    FROM exam_submissions es
    LEFT JOIN exams e ON es.exam_id = e.id
    WHERE es.student_id = '$student_id'
    ORDER BY es.id DESC
";

$reportResult = mysqli_query($conn, $reportQuery);

if (!$reportResult) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Exam Reports</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 1000px;
            margin: auto;
        }

        h2 {
            text-align: center;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #222;
            color: white;
        }

        .no-data {
            text-align: center;
            color: red;
            padding: 20px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-size: 12px;
        }

        .pass {
            background: green;
        }

        .fail {
            background: red;
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
            <th>Marks</th>
            <th>Total</th>
            <th>Percentage</th>
            <th>Status</th>
            <th>Date</th>
        </tr>

        <?php
        if (mysqli_num_rows($reportResult) > 0) {
            while ($row = mysqli_fetch_assoc($reportResult)) {

                $marks = $row['marks'];
                $total = $row['total_marks'] ?? $row['total'];
                $percentage = ($total > 0) ? round(($marks / $total) * 100, 2) : 0;

                $status = ($percentage >= 33) ? "PASS" : "FAIL";
                $badgeClass = ($percentage >= 33) ? "pass" : "fail";
        ?>
            <tr>
                <td><?php echo $row['exam_name']; ?></td>
                <td><?php echo $marks; ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $percentage; ?>%</td>
                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span></td>
                <td><?php echo $row['created_at'] ?? 'N/A'; ?></td>
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