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
body{font-family:Arial;background:#f5f5f5;padding:20px}
.container{max-width:900px;margin:auto;background:#fff;padding:20px;border-radius:10px}
.question-box{border:1px solid #ddd;padding:15px;margin-bottom:20px;border-radius:10px}
.question{font-weight:bold;margin-bottom:10px}
.option{padding:10px;margin:5px 0;border:1px solid #ccc;border-radius:6px}
.correct{background:#c8f7c5;border:2px solid green}
.wrong{background:#f8c5c5;border:2px solid red}
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