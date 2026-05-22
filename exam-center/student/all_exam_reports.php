<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../database_connection/db_connect.php'; // adjust path if needed

// 🔐 Check login
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access. Please login first.");
}

$student_id = $_SESSION['student_id'];

/*
  ✅ IMPORTANT FIX:
  - students table → student_id
  - students26 table → id
  So we will use correct mapping below
*/

// 🧠 STEP 1: Get student info (choose correct table)
$studentQuery = "SELECT * FROM students WHERE student_id = '$student_id'";
$studentResult = mysqli_query($conn, $studentQuery);

if (!$studentResult || mysqli_num_rows($studentResult) == 0) {
    die("Student not found in students table.");
}

$student = mysqli_fetch_assoc($studentResult);

/*
  🧠 STEP 2: Fetch exam reports
  (CHANGE TABLE NAME if yours is different like exam_results / results / marks etc.)
*/

$reportQuery = "
    SELECT *
    FROM exam_results
    WHERE student_id = '$student_id'
    ORDER BY id DESC
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
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background: #333;
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: red;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>📄 All Exam Reports</h2>

    <p><b>Name:</b> <?php echo $student['name']; ?></p>
    <p><b>Student ID:</b> <?php echo $student['student_id']; ?></p>

    <br>

    <table>
        <tr>
            <th>Exam ID</th>
            <th>Marks</th>
            <th>Total</th>
            <th>Percentage</th>
            <th>Date</th>
        </tr>

        <?php
        if (mysqli_num_rows($reportResult) > 0) {
            while ($row = mysqli_fetch_assoc($reportResult)) {
        ?>
            <tr>
                <td><?php echo $row['exam_id']; ?></td>
                <td><?php echo $row['marks']; ?></td>
                <td><?php echo $row['total']; ?></td>
                <td>
                    <?php 
                        echo ($row['total'] > 0) 
                            ? round(($row['marks'] / $row['total']) * 100, 2) . "%" 
                            : "0%";
                    ?>
                </td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='5' class='no-data'>No exam reports found</td></tr>";
        }
        ?>

    </table>

</div>

</body>
</html>