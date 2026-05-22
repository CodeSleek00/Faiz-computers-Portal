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
   Font: Poppins | Ultra Responsive for Mobile
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
    padding: 16px;
    min-height: 100vh;
}

/* Main Container - Pure White */
.container {
    max-width: 950px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 20px;
    padding: 20px 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

/* Header */
h2 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 20px;
    letter-spacing: -0.3px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e8edf5;
    word-break: break-word;
}

/* Question Box */
.question-box {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
    transition: all 0.2s ease;
}

/* Question Text */
.question {
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 16px;
    color: #1e293b;
    line-height: 1.45;
    background: #f8fafd;
    padding: 12px;
    border-radius: 12px;
    word-break: break-word;
}

/* Option Container */
.option {
    padding: 10px 12px;
    margin: 6px 0;
    border-radius: 10px;
    font-size: 0.85rem;
    line-height: 1.4;
    transition: all 0.15s ease;
    word-break: break-word;
}

.option b {
    font-weight: 700;
    margin-right: 8px;
    display: inline-block;
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

/* Back button */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    color: #1a56db;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.8rem;
    margin-bottom: 16px;
    padding: 6px 0;
    transition: all 0.2s;
}

/* ============================================
   EXTREME MOBILE RESPONSIVE
   ============================================ */

/* Small Mobile (up to 480px) */
@media screen and (max-width: 480px) {
    body {
        padding: 10px;
    }

    .container {
        padding: 14px 12px;
        border-radius: 16px;
    }

    h2 {
        font-size: 1.2rem;
        margin-bottom: 16px;
        padding-bottom: 8px;
    }

    .question-box {
        padding: 12px;
        margin-bottom: 16px;
        border-radius: 14px;
    }

    .question {
        font-size: 0.85rem;
        padding: 10px;
        margin-bottom: 12px;
        border-radius: 10px;
    }

    .option {
        padding: 8px 10px;
        font-size: 0.78rem;
        margin: 5px 0;
        border-radius: 8px;
    }

    .option b {
        margin-right: 6px;
        font-size: 0.8rem;
    }
}

/* Very Small Mobile (up to 380px) */
@media screen and (max-width: 380px) {
    body {
        padding: 8px;
    }

    .container {
        padding: 12px 10px;
        border-radius: 14px;
    }

    h2 {
        font-size: 1.05rem;
        margin-bottom: 14px;
    }

    .question-box {
        padding: 10px;
        margin-bottom: 14px;
        border-radius: 12px;
    }

    .question {
        font-size: 0.78rem;
        padding: 8px;
        margin-bottom: 10px;
    }

    .option {
        padding: 7px 8px;
        font-size: 0.72rem;
        margin: 4px 0;
        border-radius: 7px;
    }

    .option b {
        margin-right: 5px;
        font-size: 0.74rem;
    }
}

/* Extra Small (up to 320px) */
@media screen and (max-width: 320px) {
    body {
        padding: 6px;
    }

    .container {
        padding: 10px 8px;
        border-radius: 12px;
    }

    h2 {
        font-size: 0.95rem;
        margin-bottom: 12px;
    }

    .question-box {
        padding: 8px;
        margin-bottom: 12px;
        border-radius: 10px;
    }

    .question {
        font-size: 0.72rem;
        padding: 6px;
        margin-bottom: 8px;
    }

    .option {
        padding: 6px 6px;
        font-size: 0.68rem;
        margin: 3px 0;
        border-radius: 6px;
    }

    .option b {
        margin-right: 4px;
        font-size: 0.7rem;
    }
}

/* Tablet Landscape (up to 768px) */
@media screen and (min-width: 481px) and (max-width: 768px) {
    body {
        padding: 20px;
    }

    .container {
        padding: 24px 20px;
        border-radius: 20px;
    }

    h2 {
        font-size: 1.5rem;
    }

    .question-box {
        padding: 18px;
        margin-bottom: 20px;
    }

    .question {
        font-size: 0.95rem;
        padding: 12px;
    }

    .option {
        padding: 10px 14px;
        font-size: 0.88rem;
    }
}

/* Desktop (above 768px) */
@media screen and (min-width: 769px) {
    body {
        padding: 24px;
    }

    .container {
        padding: 32px 28px;
        border-radius: 24px;
    }

    h2 {
        font-size: 1.6rem;
    }

    .question-box {
        padding: 20px 24px;
        margin-bottom: 24px;
        border-radius: 20px;
    }

    .question {
        font-size: 1rem;
        padding: 12px 16px;
    }

    .option {
        padding: 12px 16px;
        font-size: 0.9rem;
    }
}

/* Touch-friendly - increase tap area on mobile */
@media (max-width: 768px) {
    .option {
        cursor: pointer;
    }
    
    .back-link {
        padding: 8px 0;
        margin-bottom: 12px;
    }
}

/* Prevent horizontal scroll */
body {
    overflow-x: hidden;
}

.container {
    overflow-x: hidden;
}

.option {
    overflow-x: auto;
    overflow-x: hidden;
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