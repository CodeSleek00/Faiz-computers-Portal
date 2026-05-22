<?php
session_start();
include '../../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Student Session
$student_id = $_SESSION['student_id'] ?? 0;
$student_table = $_SESSION['student_table'] ?? '';

if(!$student_id || !$student_table){
    die("Student Session Not Found");
}

// Student Fetch
$student_query = mysqli_query($conn,"
    SELECT * FROM $student_table
    WHERE id='$student_id'
");

$student = mysqli_fetch_assoc($student_query);

if(!$student){
    die("Student Not Found");
}

// Fetch All Attempted Exams
$exam_query = mysqli_query($conn,"
    SELECT DISTINCT exams.*
    FROM exams
    INNER JOIN student_answers 
    ON exams.id = student_answers.exam_id
    WHERE student_answers.student_id='$student_id'
    ORDER BY exams.id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>All Exam Reports</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    background:#f4f7fb;
    padding:25px;
}

.container{
    max-width:1400px;
    margin:auto;
}

.top-header{
    background:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:25px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
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

.report-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
}

.report-card{
    background:white;
    border-radius:18px;
    padding:25px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
    transition:0.3s;
    position:relative;
    overflow:hidden;
}

.report-card:hover{
    transform:translateY(-5px);
}

.exam-icon{
    width:60px;
    height:60px;
    border-radius:15px;
    background:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:25px;
    margin-bottom:20px;
}

.exam-name{
    font-size:22px;
    font-weight:bold;
    color:#111827;
    margin-bottom:15px;
}

.exam-details{
    margin-bottom:20px;
}

.exam-details p{
    margin-bottom:8px;
    color:#4b5563;
}

.score-box{
    display:flex;
    justify-content:space-between;
    margin-bottom:20px;
    flex-wrap:wrap;
    gap:10px;
}

.score-item{
    flex:1;
    min-width:100px;
    background:#f3f4f6;
    padding:15px;
    border-radius:10px;
    text-align:center;
}

.score-item h4{
    font-size:13px;
    color:#6b7280;
    margin-bottom:8px;
}

.score-item p{
    font-size:22px;
    font-weight:bold;
}

.correct{
    color:#16a34a;
}

.wrong{
    color:#dc2626;
}

.report-btn{
    width:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#2563eb;
    color:white;
    text-decoration:none;
    padding:14px;
    border-radius:12px;
    font-weight:bold;
    transition:0.3s;
}

.report-btn:hover{
    background:#1d4ed8;
}

.empty-box{
    background:white;
    padding:50px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.empty-box i{
    font-size:60px;
    color:#9ca3af;
    margin-bottom:20px;
}

.empty-box h2{
    color:#111827;
    margin-bottom:10px;
}

.empty-box p{
    color:#6b7280;
}

@media(max-width:768px){

    .student-box{
        flex-direction:column;
        text-align:center;
    }

}

</style>
</head>
<body>

<div class="container">

    <!-- Student Header -->
    <div class="top-header">

        <div class="student-box">

            <?php
            $photo = !empty($student['photo']) ? $student['photo'] : '../../default.png';
            ?>

            <img src="<?php echo $photo; ?>" class="student-photo">

            <div class="student-info">
                <h2><?php echo $student['name']; ?></h2>
                <p>
                    All Exam Reports & Analysis
                </p>
            </div>

        </div>

    </div>

    <?php if(mysqli_num_rows($exam_query) > 0){ ?>

    <div class="report-grid">

        <?php while($exam = mysqli_fetch_assoc($exam_query)){ ?>

        <?php

        $exam_id = $exam['id'];

        // Total Questions
        $total_q = mysqli_query($conn,"
            SELECT COUNT(*) as total
            FROM exam_questions
            WHERE exam_id='$exam_id'
        ");

        $total_questions = mysqli_fetch_assoc($total_q)['total'];

        // Correct Wrong
        $result_query = mysqli_query($conn,"
            SELECT 
            SUM(is_correct = 1) as correct_answers,
            SUM(is_correct = 0) as wrong_answers
            FROM student_answers
            WHERE exam_id='$exam_id'
            AND student_id='$student_id'
        ");

        $result = mysqli_fetch_assoc($result_query);

        $correct = $result['correct_answers'] ?? 0;
        $wrong = $result['wrong_answers'] ?? 0;

        $percentage = 0;

        if($total_questions > 0){
            $percentage = ($correct / $total_questions) * 100;
        }

        ?>

        <div class="report-card">

            <div class="exam-icon">
                <i class="fas fa-file-alt"></i>
            </div>

            <div class="exam-name">
                <?php echo $exam['exam_name']; ?>
            </div>

            <div class="exam-details">

                <p>
                    <b>Exam ID:</b>
                    #<?php echo $exam_id; ?>
                </p>

                <p>
                    <b>Total Questions:</b>
                    <?php echo $total_questions; ?>
                </p>

            </div>

            <div class="score-box">

                <div class="score-item">
                    <h4>Correct</h4>
                    <p class="correct">
                        <?php echo $correct; ?>
                    </p>
                </div>

                <div class="score-item">
                    <h4>Wrong</h4>
                    <p class="wrong">
                        <?php echo $wrong; ?>
                    </p>
                </div>

                <div class="score-item">
                    <h4>Percentage</h4>
                    <p>
                        <?php echo round($percentage,2); ?>%
                    </p>
                </div>

            </div>

            <a href="student_exam_report.php?exam_id=<?php echo $exam_id; ?>&student_id=<?php echo $student_id; ?>&student_table=<?php echo $student_table; ?>" class="report-btn">

                <i class="fas fa-chart-line"></i>
                View Full Report

            </a>

        </div>

        <?php } ?>

    </div>

    <?php } else { ?>

    <div class="empty-box">

        <i class="fas fa-file-circle-xmark"></i>

        <h2>No Exam Reports Found</h2>

        <p>
            You have not attempted any exams yet.
        </p>

    </div>

    <?php } ?>

</div>

</body>
</html>