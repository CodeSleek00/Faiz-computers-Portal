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
/* ============================================
   MINIMAL WHITE & BLUE DESIGN - EXAM RESULTS
   Font: Poppins | Fully Responsive
   ============================================ */

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f0f4fa;
    padding: 20px;
    min-height: 100vh;
}

/* Main Container - Pure White */
.container {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 24px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

/* Header */
h2 {
    font-size: 1.6rem;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 20px;
    letter-spacing: -0.3px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e8edf5;
}

/* Info Cards */
.info {
    background: #f8fafd;
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
}

.info div {
    font-size: 0.9rem;
    color: #475569;
}

.info span {
    color: #1a56db;
    font-weight: 600;
    margin-left: 6px;
}

/* Table Wrapper - For horizontal scroll on mobile */
.table-wrapper {
    overflow-x: auto;
    border-radius: 16px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    min-width: 700px;
}

/* Table Header - Blue */
th {
    background: #1a56db;
    color: white;
    font-weight: 600;
    padding: 14px 12px;
    text-align: center;
    font-size: 0.85rem;
    letter-spacing: 0.3px;
}

th:first-child {
    border-top-left-radius: 12px;
}

th:last-child {
    border-top-right-radius: 12px;
}

/* Table Cells */
td {
    padding: 12px 10px;
    text-align: center;
    border-bottom: 1px solid #e8edf5;
    color: #2c3e50;
    font-weight: 500;
}

/* Row Hover Effect */
tr:hover td {
    background-color: #f8fafd;
}

/* Score & Total Marks */
td:nth-child(2),
td:nth-child(3) {
    font-weight: 600;
    color: #1a56db;
}

/* Percentage */
td:nth-child(4) {
    font-weight: 700;
    color: #1e293b;
}

/* Status Badges */
.pass {
    color: #2e7d32;
    font-weight: 700;
    background: #e8f5e9;
    display: inline-block;
    padding: 4px 16px;
    border-radius: 30px;
    font-size: 0.75rem;
}

.fail {
    color: #c62828;
    font-weight: 600;
    background: #ffebee;
    display: inline-block;
    padding: 4px 16px;
    border-radius: 30px;
    font-size: 0.75rem;
}

/* View Button - Minimal Blue */
.btn {
    display: inline-block;
    background: transparent;
    color: #1a56db;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.75rem;
    padding: 6px 18px;
    border-radius: 30px;
    transition: all 0.2s ease;
    border: 1px solid #1a56db;
}

.btn:hover {
    background: #1a56db;
    color: white;
}

/* No Data Message */
.no-data {
    text-align: center;
    padding: 50px 20px;
    color: #94a3b8;
    font-size: 0.9rem;
}

/* ============================================
   RESPONSIVE BREAKPOINTS
   ============================================ */

/* Tablet (768px and below) */
@media screen and (max-width: 768px) {
    body {
        padding: 16px;
    }

    .container {
        padding: 20px 18px;
        border-radius: 20px;
    }

    h2 {
        font-size: 1.4rem;
        margin-bottom: 16px;
    }

    .info {
        padding: 14px 16px;
        gap: 16px;
        margin-bottom: 20px;
    }

    .info div {
        font-size: 0.85rem;
    }

    th {
        padding: 12px 10px;
        font-size: 0.8rem;
    }

    td {
        padding: 10px 8px;
        font-size: 0.82rem;
    }

    .pass, .fail {
        padding: 3px 12px;
        font-size: 0.7rem;
    }

    .btn {
        padding: 5px 14px;
        font-size: 0.7rem;
    }
}

/* Mobile (550px and below) */
@media screen and (max-width: 550px) {
    body {
        padding: 12px;
    }

    .container {
        padding: 16px 14px;
        border-radius: 18px;
    }

    h2 {
        font-size: 1.25rem;
        margin-bottom: 14px;
        padding-bottom: 10px;
    }

    .info {
        padding: 12px 14px;
        gap: 12px;
        flex-direction: column;
        gap: 8px;
    }

    .info div {
        font-size: 0.8rem;
    }

    th {
        padding: 10px 8px;
        font-size: 0.75rem;
    }

    td {
        padding: 8px 6px;
        font-size: 0.75rem;
    }

    .pass, .fail {
        padding: 2px 10px;
        font-size: 0.65rem;
    }

    .btn {
        padding: 4px 12px;
        font-size: 0.65rem;
    }
}

/* Small Mobile (400px and below) */
@media screen and (max-width: 400px) {
    body {
        padding: 10px;
    }

    .container {
        padding: 14px 12px;
        border-radius: 16px;
    }

    h2 {
        font-size: 1.15rem;
    }

    .info div {
        font-size: 0.75rem;
    }

    th {
        padding: 8px 6px;
        font-size: 0.7rem;
    }

    td {
        padding: 8px 4px;
        font-size: 0.7rem;
    }

    .pass, .fail {
        padding: 2px 8px;
        font-size: 0.6rem;
    }

    .btn {
        padding: 3px 10px;
        font-size: 0.6rem;
    }
}

/* Table scroll hint for very small screens */
@media screen and (max-width: 550px) {
    .table-wrapper {
        position: relative;
    }
    
    .table-wrapper::after {
        content: "← scroll →";
        display: block;
        text-align: center;
        font-size: 0.6rem;
        color: #94a3b8;
        margin-top: 10px;
        padding-bottom: 4px;
    }
}

/* Custom scrollbar */
.table-wrapper::-webkit-scrollbar {
    height: 5px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #e8edf5;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #1a56db;
    border-radius: 10px;
}

/* Print styles */
@media print {
    body {
        background: white;
        padding: 0;
    }
    .container {
        box-shadow: none;
        padding: 0;
    }
    .btn {
        border: none;
        text-decoration: underline;
    }
    .table-wrapper::after {
        display: none;
    }
}
</style>

</head>
<body>

<div class="container">

    <h2>📊 My Exam Results</h2>

    <div class="info">
        <div><b>Name:</b> <span><?php echo $student_name; ?></span></div>
        <div><b>Enrollment:</b> <span><?php echo $enrollment_id; ?></span></div>
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
                <?php echo date("d M Y ", strtotime($row['submitted_at'])); ?>
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