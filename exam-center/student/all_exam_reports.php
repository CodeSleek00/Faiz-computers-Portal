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
   MODERN EXAM REPORTS UI - 70% White / 20% Blue / 10% Accent
   Font: Poppins
   Fully Responsive & Modern Design
   ============================================ */

/* Import Poppins font from Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

/* CSS Reset & Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #e8f0fe 0%, #d4e2f7 100%);
    padding: 40px 24px;
    min-height: 100vh;
}

/* Main Container - White (70% dominance) */
.container {
    max-width: 1300px;
    margin: 0 auto;
    background: #FFFFFF; /* White - 70% dominant */
    border-radius: 32px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05), 0 5px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 32px 28px;
    transition: all 0.3s ease;
}

/* Header Section */
.container h2 {
    font-size: 1.85rem;
    font-weight: 700;
    color: #1a2c4e; /* Dark blue shade (20% blue family) */
    margin-bottom: 12px;
    letter-spacing: -0.3px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.container h2::before {
    content: "📊";
    font-size: 1.8rem;
    background: #1e3a8a20;
    padding: 8px;
    border-radius: 16px;
}

/* Student Name Card (Blue accent - 20% blue) */
.container p {
    background: linear-gradient(135deg, #eef2ff 0%, #e0e8f5 100%);
    padding: 16px 20px;
    border-radius: 20px;
    font-size: 1rem;
    font-weight: 500;
    color: #1e3a8a;
    margin-bottom: 32px;
    border-left: 5px solid #2563eb;
    display: inline-block;
    width: auto;
    backdrop-filter: blur(2px);
}

.container p b {
    font-weight: 700;
    color: #0f2b5e;
    margin-right: 8px;
}

/* Table Wrapper for Responsive Overflow */
.table-wrapper {
    overflow-x: auto;
    border-radius: 20px;
    margin-top: 8px;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.95rem;
    min-width: 600px;
}

/* Table Header - 20% Blue dominance */
th {
    background: #1e3a8a; /* Deep Blue - 20% theme */
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 16px 14px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    border: none;
}

th:first-child {
    border-top-left-radius: 16px;
}

th:last-child {
    border-top-right-radius: 16px;
}

/* Table Cells */
td {
    padding: 14px 12px;
    background-color: #ffffff;
    border-bottom: 1px solid #eef2ff;
    color: #1f2937;
    font-weight: 500;
    transition: background 0.2s ease;
}

/* Zebra striping for readability */
tr:hover td {
    background-color: #f8fafc;
}

tr:last-child td:first-child {
    border-bottom-left-radius: 16px;
}

tr:last-child td:last-child {
    border-bottom-right-radius: 16px;
}

/* Score & Total numbers styling */
td:nth-child(2), td:nth-child(3) {
    font-weight: 600;
    color: #1e293b;
    font-family: 'Poppins', monospace;
}

/* Percentage column */
td:nth-child(4) {
    font-weight: 700;
    background: #fefce8;
    color: #854d0e;
}

/* Status Badge styling (PASS / FAIL) */
.pass, .fail {
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 40px;
    display: inline-block;
    font-size: 0.8rem;
    text-align: center;
    min-width: 80px;
    letter-spacing: 0.3px;
}

.pass {
    background: #e6f7e6;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.fail {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* Accent Color (10% - Vibrant Accent) on Links & Buttons */
a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #f97316; /* Vibrant Orange/Accent - 10% */
    color: white;
    padding: 8px 18px;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.25s;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(249, 115, 22, 0.2);
}

a:hover {
    background: #ea580c;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35);
}

a:active {
    transform: translateY(1px);
}

/* Status column alignment */
td:nth-child(5) {
    text-align: center;
}

/* Table row bottom border radius fix */
table tr:last-child td {
    border-bottom: none;
}

/* Empty state message styling */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
    font-size: 1rem;
}

.empty-state svg {
    margin-bottom: 16px;
    opacity: 0.6;
}

/* ===== RESPONSIVE DESIGN ===== */
@media screen and (max-width: 900px) {
    body {
        padding: 20px 16px;
    }
    
    .container {
        padding: 20px 16px;
        border-radius: 28px;
    }
    
    .container h2 {
        font-size: 1.5rem;
    }
    
    th, td {
        padding: 12px 10px;
        font-size: 0.85rem;
    }
    
    a {
        padding: 6px 14px;
        font-size: 0.75rem;
    }
    
    .pass, .fail {
        padding: 4px 8px;
        min-width: 70px;
        font-size: 0.75rem;
    }
}

@media screen and (max-width: 600px) {
    .container {
        padding: 16px 12px;
        border-radius: 24px;
    }
    
    .container h2 {
        font-size: 1.3rem;
        margin-bottom: 8px;
    }
    
    .container p {
        font-size: 0.85rem;
        padding: 12px 16px;
        margin-bottom: 20px;
    }
    
    th {
        font-size: 0.75rem;
        padding: 10px 6px;
    }
    
    td {
        padding: 10px 6px;
        font-size: 0.8rem;
    }
    
    a {
        padding: 5px 10px;
        font-size: 0.7rem;
    }
    
    .pass, .fail {
        font-size: 0.7rem;
        min-width: 60px;
        padding: 3px 6px;
    }
}

/* Custom scrollbar for table wrapper */
.table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #eef2ff;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #1e3a8a80;
    border-radius: 10px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #1e3a8a;
}

/* Additional 20% blue accent touches */
.container h2 span {
    background: linear-gradient(120deg, #1e3a8a, #3b82f6);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
}

/* Small decorative accent line - 10% accent */
.container::before {
    content: '';
    display: block;
    height: 4px;
    width: 80px;
    background: #f97316;
    border-radius: 4px;
    margin-bottom: 20px;
}

/* Responsive fine-tuning for extremely small devices */
@media screen and (max-width: 480px) {
    th, td {
        font-size: 0.7rem;
        padding: 8px 4px;
    }
    
    a {
        padding: 4px 8px;
        font-size: 0.65rem;
    }
    
    .pass, .fail {
        font-size: 0.6rem;
        min-width: 50px;
    }
    
    .container h2 {
        font-size: 1.2rem;
    }
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