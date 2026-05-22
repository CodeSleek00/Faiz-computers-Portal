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
$exam_id = $_GET['exam_id'];

/*
-------------------------
GET EXAM INFO
-------------------------
*/
$exam = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM exams WHERE exam_id = '$exam_id'"
));

/*
-------------------------
GET QUESTIONS
-------------------------
*/
$questions = mysqli_query($conn, "
    SELECT *
    FROM exam_questions
    WHERE exam_id = '$exam_id'
    ORDER BY question_id ASC
");

/*
-------------------------
GET ANSWERS (IMPORTANT FIX)
-------------------------
*/
$answers = [];
$res = mysqli_query($conn, "
    SELECT question_id, selected_option
    FROM student_answers
    WHERE student_id = '$student_id'
    AND exam_id = '$exam_id'
");

while ($row = mysqli_fetch_assoc($res)) {
    $answers[$row['question_id']] = strtoupper($row['selected_option']);
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
   Font: Poppins | 100% Responsive
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
    max-width: 950px;
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
    margin-bottom: 24px;
    letter-spacing: -0.3px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e8edf5;
}

/* Question Box */
.question-box {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 24px;
    transition: all 0.2s ease;
}

.question-box:hover {
    border-color: #1a56db40;
    box-shadow: 0 4px 12px rgba(26, 86, 219, 0.08);
}

/* Question Text */
.question {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 18px;
    color: #1e293b;
    line-height: 1.4;
    background: #f8fafd;
    padding: 12px 16px;
    border-radius: 14px;
}

/* Option Container */
.option {
    padding: 12px 16px;
    margin: 8px 0;
    border-radius: 12px;
    font-size: 0.9rem;
    line-height: 1.4;
    transition: all 0.15s ease;
}

.option b {
    font-weight: 700;
    margin-right: 8px;
}

/* Correct Answer - Green */
.correct {
    background: #e8f5e9;
    border-left: 4px solid #2e7d32;
    color: #1b5e20;
}

/* Wrong Answer - Red */
.wrong {
    background: #ffebee;
    border-left: 4px solid #c62828;
    color: #b71c1c;
    text-decoration: line-through;
    text-decoration-color: #c62828;
    text-decoration-thickness: 1.5px;
}

/* Override for when correct answer is also selected - keep green */
.correct.wrong {
    background: #e8f5e9;
    border-left-color: #2e7d32;
    text-decoration: none;
    color: #1b5e20;
}

/* Default option - no class */
.option:not(.correct):not(.wrong) {
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.option:not(.correct):not(.wrong):hover {
    border-color: #1a56db60;
    background: #f8fafd;
}

/* Back button / navigation */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: #1a56db;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.85rem;
    margin-bottom: 20px;
    padding: 8px 0;
    transition: all 0.2s;
}

.back-link:hover {
    color: #0a3a8a;
    gap: 12px;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 60px 20px;
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
        padding: 24px 20px;
        border-radius: 20px;
    }

    h2 {
        font-size: 1.4rem;
        margin-bottom: 20px;
    }

    .question-box {
        padding: 16px 18px;
        margin-bottom: 20px;
        border-radius: 18px;
    }

    .question {
        font-size: 0.9rem;
        padding: 10px 14px;
        margin-bottom: 14px;
    }

    .option {
        padding: 10px 14px;
        font-size: 0.85rem;
        margin: 6px 0;
    }
}

/* Mobile */
@media screen and (max-width: 550px) {
    body {
        padding: 12px;
    }

    .container {
        padding: 18px 14px;
        border-radius: 18px;
    }

    h2 {
        font-size: 1.25rem;
        margin-bottom: 16px;
    }

    .question-box {
        padding: 14px 14px;
        margin-bottom: 16px;
        border-radius: 16px;
    }

    .question {
        font-size: 0.85rem;
        padding: 8px 12px;
        margin-bottom: 12px;
    }

    .option {
        padding: 8px 12px;
        font-size: 0.8rem;
        margin: 5px 0;
        border-radius: 10px;
    }
}

/* Small Mobile */
@media screen and (max-width: 450px) {
    .container {
        padding: 14px 12px;
    }

    h2 {
        font-size: 1.15rem;
    }

    .question-box {
        padding: 12px 12px;
    }

    .question {
        font-size: 0.8rem;
        padding: 8px 10px;
    }

    .option {
        padding: 7px 10px;
        font-size: 0.75rem;
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
    .option {
        break-inside: avoid;
    }
    .back-link {
        display: none;
    }
}
</style>

</head>
<body>

<div class="container">

<h2>📘 <?php echo $exam['exam_name']; ?> - Review</h2>

<?php
$no = 1;

while ($q = mysqli_fetch_assoc($questions)) {

    $qid = $q['question_id'];

    $selected = isset($answers[$qid]) ? strtoupper($answers[$qid]) : "";
?>

<div class="question-box">

    <div class="question">
        <?php echo $no . ". " . $q['question']; ?>
    </div>

    <?php
    $options = [
        "A" => $q['option_a'],
        "B" => $q['option_b'],
        "C" => $q['option_c'],
        "D" => $q['option_d']
    ];

    foreach ($options as $key => $value) {

        $class = "";

        // 🟢 correct answer
        if ($key == strtoupper($q['correct_option'])) {
            $class = "correct";
        }

        // 🔴 selected wrong answer
        if ($selected == $key && $selected != strtoupper($q['correct_option'])) {
            $class = "wrong";
        }

        // 🟢 selected correct answer
        if ($selected == $key && $selected == strtoupper($q['correct_option'])) {
            $class = "correct";
        }
    ?>

        <div class="option <?php echo $class; ?>">
            <b><?php echo $key; ?>.</b> <?php echo $value; ?>
        </div>

    <?php } ?>

</div>

<?php $no++; } ?>

</div>

</body>
</html>