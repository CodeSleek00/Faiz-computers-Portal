<?php
include '../../database_connection/db_connect.php';
session_start();

// Disable cache (important)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

// Get exam ID
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if ($exam_id <= 0) {
    header("Location: test.php");
    exit;
}

// Fetch student
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
if (!$student) {
    $student = $conn->query("SELECT * FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
}
if (!$student) die("Student not found.");

$student_id = $student['student_id'] ?? $student['id'];

// ✅ Check if already submitted
$check = $conn->query("SELECT * FROM exam_submissions 
                       WHERE exam_id = $exam_id AND student_id = $student_id");

if ($check->num_rows > 0) {
    header("Location: test.php");
    exit;
}

// ✅ Session control (prevent back access)
if (!isset($_SESSION['exam_started']) || $_SESSION['current_exam_id'] != $exam_id) {
    $_SESSION['exam_started'] = true;
    $_SESSION['current_exam_id'] = $exam_id;
}

// Fetch exam
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
if (!$exam) {
    header("Location: test.php");
    exit;
}

// Fetch questions
$questions_result = $conn->query("SELECT * FROM exam_questions WHERE exam_id = $exam_id ORDER BY RAND()");
$questions = [];
while ($q = $questions_result->fetch_assoc()) {
    $questions[] = $q;
}
$total = count($questions);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($exam['exam_name']) ?> - Exam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <style>
        body { font-family: Arial; background: #f2f4f8; margin: 0; }
        .exam-container { max-width: 1000px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 12px; }
        .exam-header { display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px; }
        .bubble { width: 35px; height: 35px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .bubble.active { background: #4f46e5; color: #fff; }
        .question-card { display: none; padding: 20px; border: 1px solid #ccc; border-radius: 10px; }
        .question-card.active { display: block; }
        .btn { padding: 10px 20px; background: #4f46e5; color: #fff; border: none; cursor: pointer; }
        .submit-btn { background: green; width: 100%; margin-top: 20px; }
    </style>

    <script>
        let current = 0;
        let total = <?= $total ?>;
        let duration = <?= intval($exam['duration']) ?> * 60;

        function showQuestion(i){
            document.querySelectorAll('.question-card').forEach((el,index)=>{
                el.classList.toggle('active', index===i);
                document.querySelectorAll('.bubble')[index].classList.toggle('active', index===i);
            });
            document.getElementById('submitBtn').style.display = (i === total-1) ? 'block' : 'none';
        }

        function next(){ if(current < total-1){ current++; showQuestion(current);} }
        function prev(){ if(current > 0){ current--; showQuestion(current);} }

        function startTimer(){
            const t = document.getElementById("timer");
            const interval = setInterval(()=>{
                if(duration <= 0){
                    clearInterval(interval);
                    alert("Time's up!");
                    submitExam();
                }
                let m = Math.floor(duration/60);
                let s = duration%60;
                t.innerText = m + ":" + (s<10?"0"+s:s);
                duration--;
            },1000);
        }

        function submitExam(){
            document.getElementById("examForm").submit();
        }

        window.onload = ()=>{
            showQuestion(0);
            startTimer();
        }
    </script>
</head>

<body>

<div class="exam-container">

    <div class="exam-header">
        <div>
            <h2><?= htmlspecialchars($exam['exam_name']) ?></h2>
            <p><?= htmlspecialchars($student['name']) ?></p>
            <p><?= htmlspecialchars($student['enrollment_id']) ?></p>
        </div>
        <div>
            ⏱ <span id="timer"></span>
        </div>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px;">
        <?php for($i=0;$i<$total;$i++): ?>
            <div class="bubble" onclick="current=<?= $i ?>; showQuestion(current)"><?= $i+1 ?></div>
        <?php endfor; ?>
    </div>

    <form method="POST" action="submit_exam.php" id="examForm">
        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

        <?php foreach($questions as $index => $q): ?>
            <div class="question-card">
                <p><strong>Q<?= $index+1 ?>:</strong> <?= htmlspecialchars($q['question']) ?></p>

                <?php foreach(['a','b','c','d'] as $opt): ?>
                    <label>
                        <input type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= $opt ?>">
                        <?= strtoupper($opt) ?>. <?= htmlspecialchars($q["option_$opt"]) ?>
                    </label><br>
                <?php endforeach; ?>

            </div>
        <?php endforeach; ?>

        <div style="margin-top:20px;">
            <button type="button" class="btn" onclick="prev()">Previous</button>
            <button type="button" class="btn" onclick="next()">Next</button>
        </div>

        <button type="button" id="submitBtn" class="btn submit-btn" onclick="submitExam()" style="display:none;">
            Submit Exam
        </button>
    </form>

</div>

</body>
</html>