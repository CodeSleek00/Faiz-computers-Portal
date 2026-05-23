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

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    background:#f4f7fb;
    padding:20px;
}

.container{
    max-width:950px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:15px;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
}

.header{
    margin-bottom:25px;
}

.header h2{
    color:#1a56db;
    margin-bottom:8px;
}

.header p{
    color:#666;
}

.question-box{
    border:1px solid #ddd;
    border-radius:14px;
    padding:20px;
    margin-bottom:25px;
    background:#fafafa;
}

.question{
    font-size:18px;
    font-weight:bold;
    margin-bottom:18px;
    color:#111827;
}

.option{
    padding:14px;
    margin:10px 0;
    border:1px solid #d1d5db;
    border-radius:10px;
    background:#fff;
    transition:0.3s;
}

.correct{
    background:#dcfce7;
    border:2px solid #16a34a;
}

.wrong{
    background:#fee2e2;
    border:2px solid #dc2626;
}

.answer-info{
    margin-top:18px;
    padding:14px;
    border-radius:10px;
    background:#eef4ff;
    line-height:30px;
    font-size:15px;
}

.correct-text{
    color:#15803d;
    font-weight:bold;
}

.wrong-text{
    color:#dc2626;
    font-weight:bold;
}

.not-attempted{
    color:#6b7280;
    font-weight:bold;
}

.badge{
    display:inline-block;
    padding:3px 10px;
    border-radius:20px;
    font-size:13px;
    margin-left:10px;
}

.badge-correct{
    background:#16a34a;
    color:#fff;
}

.badge-wrong{
    background:#dc2626;
    color:#fff;
}

@media(max-width:768px){

    body{
        padding:10px;
    }

    .container{
        padding:15px;
    }

    .question{
        font-size:16px;
    }

    .option{
        font-size:14px;
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