<?php
include '../../database_connection/db_connect.php';
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check if student is logged in
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

// Fetch student from students or students26
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_table = 'students';
if (!$student) {
    $student = $conn->query("SELECT * FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
    $student_table = 'students26';
}

if (!$student) die("Student not found.");

$student_id = $student['student_id'] ?? $student['id']; // id for students26

// Get exam ID from GET
$exam_id = $_GET['exam_id'] ?? 0;
$exam_id = intval($exam_id);

// If already submitted, do not allow returning to exam page
$already_submitted = $conn->query("
    SELECT 1
    FROM exam_submissions
    WHERE exam_id = $exam_id
      AND student_id = $student_id
      AND student_table = '$student_table'
    LIMIT 1
");
if ($already_submitted && $already_submitted->num_rows > 0) {
    header("Location: student_dashboard.php");
    exit;
}

// Fetch exam
$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
if (!$exam) die("Exam not found.");

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
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
    user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
    -moz-user-select: none;
    -webkit-touch-callout: none; /* Mobile long press disable */
}
        body { font-family: 'Poppins', sans-serif; background: #f2f4f8; margin: 0; padding: 0; }
        .exam-container { max-width: 1000px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
        .exam-header { display: flex; justify-content: space-between; flex-wrap: wrap; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 25px; }
        .exam-info h2 { margin: 0; color: #2c3e50; }
        .exam-info p { margin: 5px 0; color: #555; font-size: 15px; }
        .exam-profile { text-align: right; }
        .exam-profile img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 5px; }
        #timer { font-weight: bold; font-size: 16px; color: #e74c3c; }
        .bubble-nav { margin-bottom: 25px; display: flex; flex-wrap: wrap; gap: 10px; }
        .bubble { width: 36px; height: 36px; background: #ecf0f1; color: #333; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .bubble.active { background: #4f46e5; color: #fff; }
        .question-card { display: none; padding: 25px; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; margin-bottom: 20px; }
        .question-card.active { display: block; }
        .question-title { font-weight: 600; font-size: 17px; margin-bottom: 15px; color: #333; }
        .option { margin: 10px 0; }
        .btns { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn { background: #4f46e5; border: none; color: white; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .submit-btn { background: #28a745; width: 100%; margin-top: 25px; padding: 12px; }
        @media (max-width: 768px) { .exam-header { flex-direction: column; align-items: flex-start; gap: 15px; } .exam-profile { text-align: left; } .bubble { width: 30px; height: 30px; font-size: 14px; } }
    </style>
    <script>
        let current = 0;
        let total = <?= $total ?>;
        let duration = <?= intval($exam['duration']) ?> * 60; // in seconds

        function showQuestion(index) {
            document.querySelectorAll('.question-card').forEach((el,i) => { el.classList.toggle('active', i===index); document.querySelectorAll('.bubble')[i].classList.toggle('active', i===index); });
            document.getElementById('prevBtn').disabled = index===0;
            document.getElementById('nextBtn').disabled = index===total-1;
            document.getElementById('submitBtn').style.display = index===total-1 ? 'block' : 'none';
        }

        function nextQuestion(){ if(current<total-1){ current++; showQuestion(current); } }
        function prevQuestion(){ if(current>0){ current--; showQuestion(current); } }
        function jumpTo(index){ current=index; showQuestion(current); }

        function startTimer(){
            const timerEl=document.getElementById("timer");
            const interval=setInterval(()=>{
                if(duration<=0){ clearInterval(interval); alert("Time's up! Submitting exam."); document.getElementById("examForm").submit(); }
                let mins=Math.floor(duration/60);
                let secs=duration%60;
                timerEl.innerText=`${mins}:${secs<10?'0'+secs:secs}`;
                duration--;
            },1000);
        }

        window.onload = ()=>{ showQuestion(0); startTimer(); }
    </script>
</head>
<body>
<div class="exam-container">
    <div class="exam-header">
        <div class="exam-info">
            <h2><?= htmlspecialchars($exam['exam_name']) ?></h2>
            <p><strong>Student:</strong> <?= htmlspecialchars($student['name']) ?></p>
            <p><strong>Enrollment:</strong> <?= htmlspecialchars($student['enrollment_id']) ?></p>
            <p><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
        </div>
        <div class="exam-profile">
            <img src="../../uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Photo">
            <p>⏱ <span id="timer">--:--</span></p>
        </div>
    </div>

    <div class="bubble-nav">
        <?php for($i=0;$i<$total;$i++): ?>
            <div class="bubble" onclick="jumpTo(<?= $i ?>)"><?= $i+1 ?></div>
        <?php endfor; ?>
    </div>

    <form method="POST" action="submit_exam.php" id="examForm">
        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

        <?php foreach($questions as $index => $q): ?>
            <div class="question-card" id="q<?= $index ?>">
                <div class="question-title">Q<?= $index+1 ?>. <?= htmlspecialchars($q['question']) ?></div>
                <?php foreach(['a','b','c','d'] as $opt): ?>
                    <div class="option">
                        <label>
                            <input type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= $opt ?>"> <?= strtoupper($opt) ?>. <?= htmlspecialchars($q["option_$opt"]) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="btns">
            <button type="button" class="btn" onclick="prevQuestion()" id="prevBtn">⬅ Previous</button>
            <button type="button" class="btn" onclick="nextQuestion()" id="nextBtn">Next ➡</button>
        </div>

        <button type="submit" class="btn submit-btn" id="submitBtn" style="display:none;">✅ Submit Exam</button>
    </form>
</div>
<script>
const examIdForBackGuard = <?= $exam_id ?>;

function redirectSubmittedExamFromHistory() {
    if (sessionStorage.getItem(`submitted_exam_${examIdForBackGuard}`) === '1') {
        window.location.replace('student_dashboard.php');
    }
}

redirectSubmittedExamFromHistory();
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        redirectSubmittedExamFromHistory();
    }
});

// Disable text selection
document.addEventListener("selectstart", function(e) {
    e.preventDefault();
});

// Disable copy, cut, paste
['copy', 'cut', 'paste'].forEach(function(event) {
    document.addEventListener(event, function(e) {
        e.preventDefault();
    });
});

// Disable right click
document.addEventListener("contextmenu", function(e) {
    e.preventDefault();
});
</script>
</body>
</html>
