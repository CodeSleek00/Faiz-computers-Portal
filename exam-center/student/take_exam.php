<?php
include '../../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

// Fetch student
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
if (!$student) {
    $student = $conn->query("SELECT * FROM students26 WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
}
if (!$student) die("Student not found.");

$student_id = $student['student_id'] ?? $student['id'];

// Exam ID
$exam_id = intval($_GET['exam_id'] ?? 0);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f2f4f8;
    margin: 0;
    padding: 0;
    user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
    -webkit-touch-callout: none;
}

.exam-container {
    max-width: 1000px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}

.exam-header {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.exam-info h2 { margin: 0; }
.exam-profile img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

#timer { color: red; font-weight: bold; }

.bubble-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.bubble {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 600;
}

.bubble.active {
    background: #4f46e5;
    color: white;
}

.question-card {
    display: none;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 15px;
    background: #fafafa;
}

.question-card.active { display: block; }

.btn {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
}

.submit-btn {
    background: green;
    width: 100%;
    margin-top: 15px;
}

@media(max-width:768px){
    .exam-header{ flex-direction: column; gap: 10px; }
}
</style>
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
        <img src="../../uploads/<?= htmlspecialchars($student['photo']) ?>">
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
<div class="question-card">
    <div><strong>Q<?= $index+1 ?>.</strong> <?= htmlspecialchars($q['question']) ?></div>
    <?php foreach(['a','b','c','d'] as $opt): ?>
        <div>
            <label>
                <input type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= $opt ?>">
                <?= strtoupper($opt) ?>. <?= htmlspecialchars($q["option_$opt"]) ?>
            </label>
        </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<div style="display:flex; justify-content:space-between;">
    <button type="button" class="btn" onclick="prevQuestion()" id="prevBtn">Previous</button>
    <button type="button" class="btn" onclick="nextQuestion()" id="nextBtn">Next</button>
</div>

<button type="submit" class="btn submit-btn" id="submitBtn" style="display:none;">Submit Exam</button>

</form>
</div>

<script>
let current = 0;
let total = <?= $total ?>;
let duration = <?= intval($exam['duration']) ?> * 60;

// Show Question
function showQuestion(index){
    let cards = document.querySelectorAll('.question-card');
    let bubbles = document.querySelectorAll('.bubble');
    cards.forEach((c,i)=>c.classList.toggle('active',i===index));
    bubbles.forEach((b,i)=>b.classList.toggle('active',i===index));
    document.getElementById('prevBtn').disabled = index===0;
    document.getElementById('nextBtn').disabled = index===total-1;
    document.getElementById('submitBtn').style.display = index===total-1?'block':'none';
}

function nextQuestion(){ if(current<total-1){current++; showQuestion(current);} }
function prevQuestion(){ if(current>0){current--; showQuestion(current);} }
function jumpTo(i){ current=i; showQuestion(i); }

// Timer
function startTimer(){
    const timerEl=document.getElementById("timer");
    const interval=setInterval(()=>{
        if(duration<=0){
            clearInterval(interval);
            alert("Time up! Submitting...");
            document.getElementById("examForm").submit();
        }
        let m=Math.floor(duration/60);
        let s=duration%60;
        timerEl.innerText=`${m}:${s<10?'0'+s:s}`;
        duration--;
    },1000);
}

// ================= SECURITY =================

// Disable selection
document.addEventListener("selectstart", e=>e.preventDefault());

// Disable right click
document.addEventListener("contextmenu", e=>e.preventDefault());

// Disable copy paste
["copy","cut","paste"].forEach(e=>{
    document.addEventListener(e,ev=>ev.preventDefault());
});

// Disable keys
document.addEventListener("keydown",function(e){
    if(e.ctrlKey && ['c','v','x','a','u','s','p'].includes(e.key.toLowerCase())) e.preventDefault();
    if(e.key==="F12") e.preventDefault();
});

// Back button block
history.pushState(null,null,location.href);
window.onpopstate=function(){history.go(1);};

// Tab/app switch auto submit
document.addEventListener("visibilitychange",function(){
    if(document.hidden){
        alert("You left exam. Submitting...");
        document.getElementById("examForm").submit();
    }
});

window.onblur=function(){
    alert("Exam minimized. Submitting...");
    document.getElementById("examForm").submit();
};

// Fullscreen
function openFullscreen(){
    let elem=document.documentElement;
    if(elem.requestFullscreen) elem.requestFullscreen();
    else if(elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
}

document.addEventListener("fullscreenchange",function(){
    if(!document.fullscreenElement){
        alert("Fullscreen required. Submitting...");
        document.getElementById("examForm").submit();
    }
});

// Disable long press mobile
let t;
document.addEventListener("touchstart",function(e){
    t=setTimeout(()=>e.preventDefault(),500);
});
document.addEventListener("touchend",()=>clearTimeout(t));

// Load
window.onload=function(){
    openFullscreen();
    showQuestion(0);
    startTimer();
};
</script>

</body>
</html>