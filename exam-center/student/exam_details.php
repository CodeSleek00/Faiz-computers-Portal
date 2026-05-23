<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../database_connection/db_connect.php';

/* =========================================
CHECK LOGIN
========================================= */
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];
$exam_id = intval($_GET['exam_id'] ?? 0);

/* =========================================
GET EXAM INFO
========================================= */
$exam_query = mysqli_query($conn, "
    SELECT *
    FROM exams
    WHERE exam_id = '$exam_id'
");

$exam = mysqli_fetch_assoc($exam_query);

if (!$exam) {
    die("Exam not found.");
}

/* =========================================
GET QUESTIONS
========================================= */
$questions = mysqli_query($conn, "
    SELECT *
    FROM exam_questions
    WHERE exam_id = '$exam_id'
    ORDER BY question_id ASC
");

/* =========================================
GET STUDENT ANSWERS
========================================= */
$answers = [];

$res = mysqli_query($conn, "
    SELECT question_id, selected_option
    FROM student_answers
    WHERE student_id = '$student_id'
    AND exam_id = '$exam_id'
");

while ($row = mysqli_fetch_assoc($res)) {

    $qid = trim($row['question_id']);

    // DATABASE me small letters stored hain
    $answers[$qid] = strtolower(trim($row['selected_option']));
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Exam Review</title>

<style>
/* ============================================
   MINIMAL WHITE & BLUE DESIGN - EXAM REVIEW
   Correct: Green | Wrong: Red
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
    max-width: 950px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 24px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

/* Header Section */
.header {
    margin-bottom: 28px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e8edf5;
}

.header h2 {
    font-size: 1.6rem;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 8px;
    letter-spacing: -0.3px;
}

.header p {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 400;
}

/* Question Box */
.question-box {
    border: 1px solid #e8edf5;
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 24px;
    background: #ffffff;
    transition: all 0.2s ease;
}

.question-box:hover {
    border-color: #1a56db40;
    box-shadow: 0 4px 12px rgba(26, 86, 219, 0.08);
}

/* Question Text */
.question {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1e293b;
    line-height: 1.45;
    background: #f8fafd;
    padding: 12px 16px;
    border-radius: 14px;
}

/* Option Container */
.option {
    padding: 12px 16px;
    margin: 8px 0;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    line-height: 1.4;
    position: relative;
}

.option b {
    font-weight: 700;
    margin-right: 8px;
    color: #1a56db;
}

/* Correct Answer - Green */
.correct {
    background: #e8f5e9;
    border: 2px solid #2e7d32;
}

/* Wrong Answer - Red */
.wrong {
    background: #ffebee;
    border: 2px solid #c62828;
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 12px;
}

.badge-correct {
    background: #2e7d32;
    color: #ffffff;
}

.badge-wrong {
    background: #c62828;
    color: #ffffff;
}

/* Answer Info Box */
.answer-info {
    margin-top: 20px;
    padding: 14px 18px;
    border-radius: 14px;
    background: #f8fafd;
    font-size: 0.85rem;
    line-height: 1.6;
}

.correct-text {
    color: #2e7d32;
    font-weight: 600;
}

.wrong-text {
    color: #c62828;
    font-weight: 600;
}

.not-attempted {
    color: #64748b;
    font-weight: 600;
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

    .header h2 {
        font-size: 1.4rem;
    }

    .header p {
        font-size: 0.85rem;
    }

    .question-box {
        padding: 16px 18px;
        margin-bottom: 20px;
        border-radius: 18px;
    }

    .question {
        font-size: 0.95rem;
        padding: 10px 14px;
        margin-bottom: 16px;
    }

    .option {
        padding: 10px 14px;
        font-size: 0.85rem;
        margin: 6px 0;
    }

    .badge {
        padding: 2px 10px;
        font-size: 0.65rem;
        margin-left: 8px;
    }

    .answer-info {
        padding: 12px 14px;
        font-size: 0.8rem;
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

    .header {
        margin-bottom: 20px;
        padding-bottom: 12px;
    }

    .header h2 {
        font-size: 1.25rem;
    }

    .header p {
        font-size: 0.8rem;
    }

    .question-box {
        padding: 14px 14px;
        margin-bottom: 16px;
        border-radius: 16px;
    }

    .question {
        font-size: 0.85rem;
        padding: 8px 12px;
        margin-bottom: 14px;
    }

    .option {
        padding: 8px 12px;
        font-size: 0.8rem;
        margin: 5px 0;
        border-radius: 10px;
    }

    .option b {
        margin-right: 6px;
    }

    .badge {
        padding: 2px 8px;
        font-size: 0.6rem;
        margin-left: 6px;
    }

    .answer-info {
        margin-top: 14px;
        padding: 10px 12px;
        font-size: 0.75rem;
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

    .header h2 {
        font-size: 1.1rem;
    }

    .header p {
        font-size: 0.75rem;
    }

    .question-box {
        padding: 12px 12px;
        margin-bottom: 14px;
        border-radius: 14px;
    }

    .question {
        font-size: 0.8rem;
        padding: 8px 10px;
        margin-bottom: 12px;
    }

    .option {
        padding: 7px 10px;
        font-size: 0.75rem;
        margin: 4px 0;
        border-radius: 8px;
    }

    .badge {
        padding: 2px 6px;
        font-size: 0.55rem;
        margin-left: 5px;
    }

    .answer-info {
        padding: 8px 10px;
        font-size: 0.7rem;
    }

    .correct-text, .wrong-text, .not-attempted {
        display: block;
        margin: 4px 0;
    }
}

/* Very Small (320px and below) */
@media screen and (max-width: 320px) {
    body {
        padding: 8px;
    }

    .container {
        padding: 12px 10px;
        border-radius: 14px;
    }

    .header h2 {
        font-size: 1rem;
    }

    .question-box {
        padding: 10px 10px;
        border-radius: 12px;
    }

    .question {
        font-size: 0.75rem;
        padding: 6px 8px;
    }

    .option {
        padding: 6px 8px;
        font-size: 0.7rem;
    }

    .badge {
        display: inline-block;
        margin-top: 4px;
        margin-left: 0;
        margin-right: 4px;
    }
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
    .badge {
        border: 1px solid #ccc;
        background: #f0f0f0;
        color: #000;
    }
    .correct {
        background: #e8f5e9;
    }
    .wrong {
        background: #ffebee;
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="header">
        <h2>📘 <?php echo htmlspecialchars($exam['exam_name']); ?> - Exam Review</h2>
        <p>Review your answers and check correct solutions.</p>
    </div>

<?php

$no = 1;

while ($q = mysqli_fetch_assoc($questions)) {

    $qid = trim($q['question_id']);

    // selected answer
    $selected = $answers[$qid] ?? "";

    // correct answer
    $correct_option = strtolower(trim($q['correct_option']));

?>

<div class="question-box">

    <div class="question">
        <?php echo $no . ". " . htmlspecialchars($q['question']); ?>
    </div>

<?php

$options = [
    "a" => $q['option_a'],
    "b" => $q['option_b'],
    "c" => $q['option_c'],
    "d" => $q['option_d']
];

foreach ($options as $key => $value) {

    $class = "";

    // Correct answer
    if ($key == $correct_option) {
        $class = "correct";
    }

    // Wrong selected answer
    if ($selected == $key && $selected != $correct_option) {
        $class = "wrong";
    }

?>

<div class="option <?php echo $class; ?>">

    <b><?php echo strtoupper($key); ?>.</b>

    <?php echo htmlspecialchars($value); ?>

    <?php

    // STUDENT SELECTED
    if ($selected == $key) {

        if ($selected == $correct_option) {

            echo "
            <span class='badge badge-correct'>
                Your Answer
            </span>";

        } else {

            echo "
            <span class='badge badge-wrong'>
                Your Selected Answer
            </span>";
        }
    }

    // CORRECT ANSWER LABEL
    if ($key == $correct_option) {

        echo "
        <span class='badge badge-correct'>
            Correct Answer
        </span>";
    }

    ?>

</div>

<?php } ?>

<div class="answer-info">

<?php

if ($selected == "") {

    echo "
    <span class='not-attempted'>
        ⚪ Not Attempted
    </span>";

} elseif ($selected == $correct_option) {

    echo "
    <span class='correct-text'>
        ✅ You selected the correct answer:
        " . strtoupper($selected) . "
    </span>";

} else {

    echo "
    <span class='wrong-text'>
        ❌ You selected:
        " . strtoupper($selected) . "
    </span>
    <br>

    <span class='correct-text'>
        ✅ Correct Answer:
        " . strtoupper($correct_option) . "
    </span>";
}

?>

</div>

</div>

<?php $no++; } ?>

</div>

</body>
</html>