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
$exam_id = $_GET['exam_id'] ?? 0;

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
    $answers[$row['question_id']] = strtoupper($row['selected_option']);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Review</title>

<style>
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
    overflow-x: hidden;
}

.container {
    max-width: 950px;
    margin: 0 auto;
    background: #fff;
    border-radius: 20px;
    padding: 20px 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

h2 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #1a56db;
    margin-bottom: 20px;
    border-bottom: 2px solid #e8edf5;
    padding-bottom: 10px;
}

.question-box {
    border: 1px solid #e8edf5;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
    background: #fff;
}

.question {
    font-weight: 700;
    font-size: 0.95rem;
    margin-bottom: 14px;
    background: #f8fafd;
    padding: 12px;
    border-radius: 12px;
}

.option {
    padding: 10px 12px;
    margin: 6px 0;
    border-radius: 10px;
    font-size: 0.85rem;
    border: 1px solid #e2e8f0;
}

/* correct answer */
.correct {
    background: #e8f5e9;
    border-left: 4px solid #2e7d32;
    color: #1b5e20;
}

/* wrong selected answer */
.wrong {
    background: #ffebee;
    border-left: 4px solid #c62828;
    color: #b71c1c;
    text-decoration: line-through;
}

/* selected correct (same as correct) */
.selected-correct {
    background: #e8f5e9;
    border-left: 4px solid #2e7d32;
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

    $correct = strtoupper($q['correct_option']);
    $selected = $answers[$qid] ?? "";
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

        // ✅ selected correct
        if ($key == $selected && $key == $correct) {
            $class = "selected-correct";
        }

        // 🟢 correct answer always
        elseif ($key == $correct) {
            $class = "correct";
        }

        // 🔴 wrong selected answer
        elseif ($key == $selected) {
            $class = "wrong";
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