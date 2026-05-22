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
/* ============================================
   MINIMAL WHITE & BLUE DESIGN
   Font: Poppins
   100% Responsive | Clean | Modern
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
    padding: 24px;
    min-height: 100vh;
}

/* Main Container - Pure White */
.container {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 32px 28px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

/* Header */
h2 {
    font-size: 1.6rem;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 8px;
    letter-spacing: -0.3px;
}

/* Student Info Card - Light Blue */
.student-info {
    background: #e8f0fe;
    padding: 14px 20px;
    border-radius: 16px;
    margin: 20px 0 28px 0;
    border-left: 4px solid #1a56db;
}

.student-info span {
    font-weight: 600;
    color: #1a56db;
}

.student-info .student-name {
    font-weight: 700;
    color: #0a3a8a;
}

/* Table Wrapper - For horizontal scroll on small screens */
.table-wrapper {
    overflow-x: auto;
    border-radius: 16px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    min-width: 550px;
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

/* Score & Total */
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

/* Status Badges - Minimal */
.status-pass {
    color: #1a56db;
    font-weight: 700;
    background: #e8f0fe;
    display: inline-block;
    padding: 4px 16px;
    border-radius: 30px;
    font-size: 0.75rem;
}

.status-fail {
    color: #94a3b8;
    font-weight: 600;
    background: #f1f5f9;
    display: inline-block;
    padding: 4px 16px;
    border-radius: 30px;
    font-size: 0.75rem;
}

/* View Details Link - Blue Minimal Button */
.view-link {
    display: inline-block;
    background: transparent;
    color: #1a56db;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 6px 16px;
    border-radius: 30px;
    transition: all 0.2s ease;
    border: 1px solid #1a56db;
}

.view-link:hover {
    background: #1a56db;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #94a3b8;
}

/* ============================================
   RESPONSIVE BREAKPOINTS
   ============================================ */

/* Tablet */
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
    }

    .student-info {
        padding: 12px 16px;
        margin: 16px 0 22px 0;
        font-size: 0.85rem;
    }

    th {
        padding: 12px 8px;
        font-size: 0.8rem;
    }

    td {
        padding: 10px 8px;
        font-size: 0.85rem;
    }

    .status-pass, .status-fail {
        padding: 3px 12px;
        font-size: 0.7rem;
    }

    .view-link {
        padding: 5px 12px;
        font-size: 0.75rem;
    }
}

/* Mobile Landscape */
@media screen and (max-width: 550px) {
    body {
        padding: 12px;
    }

    .container {
        padding: 16px 14px;
        border-radius: 18px;
    }

    h2 {
        font-size: 1.3rem;
    }

    .student-info {
        padding: 10px 14px;
        margin: 14px 0 18px 0;
        font-size: 0.8rem;
    }

    th {
        padding: 10px 6px;
        font-size: 0.7rem;
    }

    td {
        padding: 8px 6px;
        font-size: 0.75rem;
    }

    .status-pass, .status-fail {
        padding: 2px 8px;
        font-size: 0.65rem;
    }

    .view-link {
        padding: 4px 10px;
        font-size: 0.7rem;
    }
}

/* Small Mobile */
@media screen and (max-width: 450px) {
    .container {
        padding: 14px 12px;
    }

    h2 {
        font-size: 1.2rem;
    }

    .student-info {
        font-size: 0.75rem;
        padding: 8px 12px;
    }

    th {
        padding: 8px 4px;
        font-size: 0.65rem;
    }

    td {
        padding: 8px 4px;
        font-size: 0.7rem;
    }

    .status-pass, .status-fail {
        padding: 2px 6px;
        font-size: 0.6rem;
    }

    .view-link {
        padding: 3px 8px;
        font-size: 0.65rem;
    }
}

/* Table horizontal scroll hint for very small screens */
@media screen and (max-width: 480px) {
    .table-wrapper::after {
        content: "← scroll →";
        display: block;
        text-align: center;
        font-size: 0.6rem;
        color: #94a3b8;
        margin-top: 8px;
    }
}

/* Smooth table scrollbar */
.table-wrapper::-webkit-scrollbar {
    height: 4px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #e8edf5;
    border-radius: 4px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #1a56db;
    border-radius: 4px;
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