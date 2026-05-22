<?php
session_start();
include '../db.php';

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
GET QUESTIONS + STUDENT ANSWERS
-------------------------
*/
$questions = mysqli_query($conn, "
    SELECT 
        q.*,
        sa.selected_option,
        sa.is_correct
    FROM exam_questions q
    LEFT JOIN student_answers sa 
        ON sa.question_id = q.id 
        AND sa.student_id = '$student_id'
        AND sa.exam_id = '$exam_id'
    WHERE q.exam_id = '$exam_id'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Review</title>

<style>
body{
    font-family:Arial;
    background:#f5f5f5;
    padding:20px;
}

.container{
    max-width:900px;
    margin:auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
}

.question-box{
    border:1px solid #ddd;
    padding:15px;
    margin-bottom:20px;
    border-radius:10px;
}

.question{
    font-weight:bold;
    margin-bottom:10px;
}

.option{
    padding:10px;
    margin:5px 0;
    border:1px solid #ccc;
    border-radius:6px;
}

/* 🟢 correct answer */
.correct{
    background:#c8f7c5;
    border:2px solid green;
}

/* 🔴 selected wrong answer */
.wrong{
    background:#f8c5c5;
    border:2px solid red;
}

.meta{
    margin-bottom:20px;
    font-size:18px;
}
</style>

</head>

<body>

<div class="container">

<h2>📘 <?php echo $exam['exam_name']; ?> - Detailed Review</h2>

<?php
$no = 1;

while ($q = mysqli_fetch_assoc($questions)) {

    $selected = $q['selected_option']; // A/B/C/D
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

        // 🟢 correct answer highlight
        if ($key == $q['correct_option']) {
            $class = "correct";
        }

        // 🔴 selected wrong answer highlight
        if ($selected == $key && $selected != $q['correct_option']) {
            $class = "wrong";
        }

        // 🟢 if selected and correct both same (still green)
        if ($selected == $key && $selected == $q['correct_option']) {
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