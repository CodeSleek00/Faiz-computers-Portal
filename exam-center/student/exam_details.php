<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../database_connection/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];
$exam_id = intval($_GET['exam_id']);

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
GET STUDENT ANSWERS
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

    $answers[$row['question_id']] = strtoupper(trim($row['selected_option']));
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Exam Review</title>

<style>

body{
    font-family:Arial;
    background:#f5f7fb;
    padding:20px;
}

.container{
    max-width:950px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

h2{
    color:#1a56db;
    margin-bottom:25px;
}

.question-box{
    border:1px solid #ddd;
    padding:18px;
    margin-bottom:25px;
    border-radius:12px;
    background:#fafafa;
}

.question{
    font-weight:bold;
    margin-bottom:15px;
    font-size:17px;
}

.option{
    padding:12px;
    margin:8px 0;
    border:1px solid #ccc;
    border-radius:8px;
    background:#fff;
}

.correct{
    background:#d4edda;
    border:2px solid #28a745;
}

.wrong{
    background:#f8d7da;
    border:2px solid #dc3545;
}

.answer-info{
    margin-top:15px;
    padding:12px;
    border-radius:8px;
    background:#eef4ff;
    line-height:28px;
}

.correct-text{
    color:green;
    font-weight:bold;
}

.wrong-text{
    color:red;
    font-weight:bold;
}

.not-attempted{
    color:#666;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="container">

<h2>📘 <?php echo htmlspecialchars($exam['exam_name']); ?> - Exam Review</h2>

<?php

$no = 1;

while ($q = mysqli_fetch_assoc($questions)) {

    $qid = $q['question_id'];

    $selected = isset($answers[$qid])
        ? strtoupper($answers[$qid])
        : "";

    $correct_option = strtoupper($q['correct_option']);

?>

<div class="question-box">

    <div class="question">
        <?php echo $no . ". " . htmlspecialchars($q['question']); ?>
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

        /* CORRECT ANSWER */
        if ($key == $correct_option) {
            $class = "correct";
        }

        /* WRONG SELECTED ANSWER */
        if ($selected == $key && $selected != $correct_option) {
            $class = "wrong";
        }

        /* SELECTED + CORRECT */
        if ($selected == $key && $selected == $correct_option) {
            $class = "correct";
        }

    ?>

    <div class="option <?php echo $class; ?>">

        <b><?php echo $key; ?>.</b>
        <?php echo htmlspecialchars($value); ?>

        <?php

        // STUDENT SELECTED THIS OPTION
        if ($selected == $key) {

            if ($selected == $correct_option) {

                echo " ✅ <b>(Your Answer)</b>";

            } else {

                echo " ❌ <b>(Your Selected Answer)</b>";
            }
        }

        // SHOW CORRECT ANSWER LABEL
        if ($key == $correct_option) {

            echo " 🟢 <b>(Correct Answer)</b>";
        }

        ?>

    </div>

    <?php } ?>

    <div class="answer-info">

        <?php

        if ($selected == "") {

            echo "
            <span class='not-attempted'>
                Not Attempted
            </span>";

        } elseif ($selected == $correct_option) {

            echo "
            <span class='correct-text'>
                ✔ You selected the correct answer: $selected
            </span>";

        } else {

            echo "
            <span class='wrong-text'>
                ✘ You selected: $selected
            </span>
            <br>

            <span class='correct-text'>
                ✔ Correct Answer: $correct_option
            </span>";
        }

        ?>

    </div>

</div>

<?php $no++; } ?>

</div>

</body>
</html>