<?php
session_start();
include '../../database_connection/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

/*
---------------------------------
CHECK STUDENT TABLE
- students26 uses id
- students uses student_id
---------------------------------
*/

$student_name = "Unknown Student";
$student_table = "";

/* CHECK IN students26 */
$check_students26 = mysqli_query($conn, "
    SELECT id, name 
    FROM students26 
    WHERE id = '$student_id' 
    LIMIT 1
");

if (mysqli_num_rows($check_students26) > 0) {

    $student = mysqli_fetch_assoc($check_students26);
    $student_name = $student['name'];
    $student_table = "students26";

} else {

    /* CHECK IN students */
    $check_students = mysqli_query($conn, "
        SELECT student_id, name 
        FROM students 
        WHERE student_id = '$student_id' 
        LIMIT 1
    ");

    if (mysqli_num_rows($check_students) > 0) {

        $student = mysqli_fetch_assoc($check_students);
        $student_name = $student['name'];
        $student_table = "students";
    }
}

/*
---------------------------------
GET EXAMS
---------------------------------
*/
$exams = mysqli_query($conn, "
    SELECT 
        es.*,
        e.exam_name,
        e.total_questions
    FROM exam_submissions es
    LEFT JOIN exams e 
        ON es.exam_id = e.exam_id
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

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    background:#f0f4fa;
    padding:24px;
}

.container{
    max-width:1200px;
    margin:auto;
    background:#fff;
    padding:28px;
    border-radius:20px;
    box-shadow:0 2px 12px rgba(0,0,0,0.05);
}

h2{
    color:#1a56db;
    margin-bottom:12px;
}

.student-info{
    margin-top:10px;
    line-height:30px;
    font-size:15px;
}

.student-info span{
    font-weight:600;
    color:#1a56db;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:25px;
    overflow:hidden;
    border-radius:12px;
}

th{
    background:#1a56db;
    color:#fff;
    padding:14px;
    font-size:14px;
}

td{
    text-align:center;
    padding:12px;
    border-bottom:1px solid #eee;
    font-size:14px;
}

tr:hover{
    background:#f8fbff;
}

.status-pass{
    background:#dcfce7;
    color:#15803d;
    padding:5px 14px;
    border-radius:20px;
    font-weight:600;
    display:inline-block;
}

.status-fail{
    background:#fee2e2;
    color:#dc2626;
    padding:5px 14px;
    border-radius:20px;
    font-weight:600;
    display:inline-block;
}

.button{
    background:#1a56db;
    color:white;
    padding:7px 15px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:500;
    transition:0.3s;
}

.button:hover{
    background:#1748b3;
}

.no-data{
    padding:25px;
    text-align:center;
    color:#64748b;
    font-weight:500;
}

@media(max-width:768px){

    body{
        padding:12px;
    }

    .container{
        padding:18px;
        overflow-x:auto;
    }

    table{
        min-width:700px;
    }
}

</style>
</head>

<body>

<div class="container">

    <h2>📊 All Exam Reports</h2>

    <div class="student-info">
        <div><b>Name:</b> <span><?php echo $student_name; ?></span></div>
        <div><b>Student ID:</b> <span><?php echo $student_id; ?></span></div>
        <div><b>Table:</b> <span><?php echo $student_table; ?></span></div>
    </div>

    <table>

        <tr>
            <th>Exam Name</th>
            <th>Score</th>
            <th>Total Questions</th>
            <th>Percentage</th>
            <th>Status</th>
            <th>Details</th>
        </tr>

        <?php

        if(mysqli_num_rows($exams) > 0){

            while($row = mysqli_fetch_assoc($exams)){

                $score = $row['score'];
                $total = $row['total_questions'];

                $percent = ($total > 0)
                    ? round(($score / $total) * 100, 2)
                    : 0;

                $status = ($percent >= 33) ? "PASS" : "FAIL";

                $class = ($percent >= 33)
                    ? "status-pass"
                    : "status-fail";
        ?>

        <tr>

            <td>
                <?php echo htmlspecialchars($row['exam_name']); ?>
            </td>

            <td>
                <?php echo $score; ?>
            </td>

            <td>
                <?php echo $total; ?>
            </td>

            <td>
                <?php echo $percent; ?>%
            </td>

            <td>
                <span class="<?php echo $class; ?>">
                    <?php echo $status; ?>
                </span>
            </td>

            <td>
                <a class="button"
                   href="exam_details.php?exam_id=<?php echo $row['exam_id']; ?>">
                   View Details
                </a>
            </td>

        </tr>

        <?php
            }

        } else {

            echo "
            <tr>
                <td colspan='6' class='no-data'>
                    No exam records found
                </td>
            </tr>";
        }

        ?>

    </table>

</div>

</body>
</html>