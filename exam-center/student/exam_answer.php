<?php
include 'db.php';

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$student_table = isset($_GET['student_table']) ? $_GET['student_table'] : 'students';

// Exam Fetch
$exam_query = mysqli_query($conn, "
    SELECT * FROM exams 
    WHERE id='$exam_id'
");

$exam = mysqli_fetch_assoc($exam_query);

if(!$exam){
    die("Exam Not Found");
}

// Student Fetch
$student_query = mysqli_query($conn, "
    SELECT * FROM $student_table
    WHERE id='$student_id'
");

$student = mysqli_fetch_assoc($student_query);

if(!$student){
    die("Student Not Found");
}

// Questions Fetch
$questions_query = mysqli_query($conn, "
    SELECT * FROM exam_questions
    WHERE exam_id='$exam_id'
    ORDER BY id ASC
");

$total_questions = mysqli_num_rows($questions_query);

$correct = 0;
$wrong = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Exam Report</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:#f4f7fb;
    padding:20px;
}

.container{
    width:100%;
    max-width:1300px;
    margin:auto;
}

.header{
    background:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.header-top{
    display:flex;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:20px;
}

.student-box{
    display:flex;
    align-items:center;
    gap:15px;
}

.student-photo{
    width:80px;
    height:80px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #2563eb;
}

.student-info h2{
    color:#111827;
    margin-bottom:5px;
}

.student-info p{
    color:#6b7280;
}

.exam-title{
    margin-top:20px;
    font-size:22px;
    font-weight:bold;
    color:#111827;
}

.stats{
    display:flex;
    gap:20px;
    margin-top:20px;
    flex-wrap:wrap;
}

.stat-box{
    flex:1;
    min-width:180px;
    background:#f3f4f6;
    padding:18px;
    border-radius:12px;
}

.stat-box h3{
    color:#6b7280;
    margin-bottom:8px;
    font-size:14px;
}

.stat-box p{
    font-size:28px;
    font-weight:bold;
}

.question-card{
    background:white;
    margin-bottom:20px;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.question-header{
    background:#111827;
    color:white;
    padding:18px;
}

.question-body{
    padding:25px;
}

.question{
    font-size:18px;
    margin-bottom:25px;
    line-height:1.7;
}

.option{
    padding:14px;
    margin-bottom:12px;
    border-radius:10px;
    background:#f3f4f6;
    border:2px solid transparent;
}

.correct-option{
    background:#dcfce7;
    border-color:#22c55e;
    color:#166534;
    font-weight:bold;
}

.wrong-option{
    background:#fee2e2;
    border-color:#ef4444;
    color:#991b1b;
    font-weight:bold;
}

.selected{
    border:2px solid #2563eb;
}

.answer-box{
    margin-top:20px;
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}

.answer-item{
    background:#f9fafb;
    padding:15px 20px;
    border-radius:10px;
    min-width:200px;
}

.answer-item h4{
    color:#6b7280;
    margin-bottom:5px;
    font-size:14px;
}

.answer-item p{
    font-size:20px;
    font-weight:bold;
}

.correct-text{
    color:#16a34a;
}

.wrong-text{
    color:#dc2626;
}

@media(max-width:768px){

    .header-top{
        flex-direction:column;
    }

}

</style>
</head>
<body>

<div class="container">

    <div class="header">

        <div class="header-top">

            <div class="student-box">

                <?php
                $photo = !empty($student['photo']) ? $student['photo'] : 'default.png';
                ?>

                <img src="<?php echo $photo; ?>" class="student-photo">

                <div class="student-info">
                    <h2><?php echo $student['name']; ?></h2>
                    <p>Student ID : <?php echo $student['id']; ?></p>
                </div>

            </div>

        </div>

        <div class="exam-title">
            <?php echo $exam['exam_name']; ?>
        </div>

        <div class="stats">

            <?php

            $count_answers = mysqli_query($conn,"
                SELECT 
                SUM(is_correct = 1) as correct_answers,
                SUM(is_correct = 0) as wrong_answers
                FROM student_answers
                WHERE exam_id='$exam_id'
                AND student_id='$student_id'
            ");

            $count = mysqli_fetch_assoc($count_answers);

            $correct = $count['correct_answers'] ?? 0;
            $wrong = $count['wrong_answers'] ?? 0;

            $percentage = ($correct / $total_questions) * 100;

            ?>

            <div class="stat-box">
                <h3>Total Questions</h3>
                <p><?php echo $total_questions; ?></p>
            </div>

            <div class="stat-box">
                <h3>Correct</h3>
                <p class="correct-text"><?php echo $correct; ?></p>
            </div>

            <div class="stat-box">
                <h3>Wrong</h3>
                <p class="wrong-text"><?php echo $wrong; ?></p>
            </div>

            <div class="stat-box">
                <h3>Percentage</h3>
                <p><?php echo round($percentage,2); ?>%</p>
            </div>

        </div>

    </div>

<?php

$question_no = 1;

mysqli_data_seek($questions_query, 0);

while($question = mysqli_fetch_assoc($questions_query)){

    $question_id = $question['id'];

    $answer_query = mysqli_query($conn,"
        SELECT * FROM student_answers
        WHERE exam_id='$exam_id'
        AND student_id='$student_id'
        AND question_id='$question_id'
    ");

    $answer = mysqli_fetch_assoc($answer_query);

    $selected_option = strtolower($answer['selected_option'] ?? '');
    $correct_option = strtolower($question['correct_option']);

?>

<div class="question-card">

    <div class="question-header">
        Question <?php echo $question_no; ?>
    </div>

    <div class="question-body">

        <div class="question">
            <?php echo $question['question']; ?>
        </div>

        <?php

        $options = [
            'a' => $question['option_a'],
            'b' => $question['option_b'],
            'c' => $question['option_c'],
            'd' => $question['option_d']
        ];

        foreach($options as $key => $value){

            $class = "option";

            if($key == $correct_option){
                $class .= " correct-option";
            }

            if($key == $selected_option && $selected_option != $correct_option){
                $class .= " wrong-option";
            }

            if($key == $selected_option){
                $class .= " selected";
            }

            echo "
            <div class='$class'>
                <b>".strtoupper($key).".</b> $value
            </div>
            ";

        }

        ?>

        <div class="answer-box">

            <div class="answer-item">
                <h4>Student Answer</h4>
                <p>
                    <?php echo strtoupper($selected_option); ?>
                </p>
            </div>

            <div class="answer-item">
                <h4>Correct Answer</h4>
                <p class='correct-text'>
                    <?php echo strtoupper($correct_option); ?>
                </p>
            </div>

            <div class="answer-item">
                <h4>Status</h4>

                <?php

                if($selected_option == $correct_option){

                    echo "<p class='correct-text'>Correct</p>";

                }else{

                    echo "<p class='wrong-text'>Wrong</p>";
                }

                ?>

            </div>

        </div>

    </div>

</div>

<?php
$question_no++;
}
?>

</div>

</body>
</html>