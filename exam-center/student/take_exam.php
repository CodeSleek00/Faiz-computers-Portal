<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];
$exam_id = $_GET['exam_id'];

$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM exam_questions WHERE exam_id = $exam_id ORDER BY question_id ASC");

$question_array = [];
while ($q = $questions->fetch_assoc()) {
    $question_array[] = $q;
}

$total = count($question_array);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($exam['exam_name']) ?> - Exam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6fa; padding: 20px; margin: 0; }
        .container {
            max-width: 800px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        .header {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 25px;
        }
        .timer { font-weight: bold; color: #dc3545; }
        .question {
            display: none;
        }
        .question.active {
            display: block;
        }
        .question strong {
            font-size: 18px;
        }
        label {
            display: block;
            margin: 10px 0;
            font-size: 16px;
        }
        .btn-group {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            padding: 10px 20px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .submit-btn {
            background: #28a745;
            width: 100%;
            margin-top: 20px;
        }
        img.photo {
            border-radius: 50%;
            width: 60px;
        }

        @media (max-width: 600px) {
            .header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-group { flex-direction: column; gap: 10px; }
            .btn { width: 100%; }
        }
    </style>
    <script>
        let current = 0;
        let total = <?= $total ?>;
        let duration = <?= $exam['duration'] ?> * 60;

        function showQuestion(index) {
            document.querySelectorAll('.question').forEach((el, i) => {
                el.classList.toggle('active', i === index);
            });

            document.getElementById('prevBtn').disabled = index === 0;
            document.getElementById('nextBtn').disabled = index === total - 1;
            document.getElementById('submitBtn').style.display = index === total - 1 ? 'block' : 'none';
        }

        function nextQuestion() {
            if (current < total - 1) {
                current++;
                showQuestion(current);
            }
        }

        function prevQuestion() {
            if (current > 0) {
                current--;
                showQuestion(current);
            }
        }

        function startTimer() {
            const timerEl = document.getElementById("timer");
            const interval = setInterval(() => {
                if (duration <= 0) {
                    clearInterval(interval);
                    alert("Time's up! Submitting exam.");
                    document.getElementById("examForm").submit();
                }
                let mins = Math.floor(duration / 60);
                let secs = duration % 60;
                timerEl.innerText = `${mins}:${secs < 10 ? '0' + secs : secs}`;
                duration--;
            }, 1000);
        }

        window.onload = () => {
            showQuestion(0);
            startTimer();
        }
    </script>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h2><?= htmlspecialchars($exam['exam_name']) ?></h2>
            <p><strong><?= $student['name'] ?></strong> | <?= $student['enrollment_id'] ?></p>
        </div>
        <div>
            <img src="../../uploads/<?= $student['photo'] ?>" alt="Profile" class="photo">
            <p>⏱️ <span id="timer">--:--</span></p>
        </div>
    </div>

    <form method="POST" action="submit_exam.php" id="examForm">
        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

        <?php foreach ($question_array as $index => $q): ?>
            <div class="question" id="q<?= $index ?>">
                <strong>Q<?= $index + 1 ?>. <?= htmlspecialchars($q['question']) ?></strong>
                <?php foreach (['a', 'b', 'c', 'd'] as $opt): ?>
                    <label>
                        <input type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= $opt ?>" required>
                        <?= strtoupper($opt) ?>. <?= htmlspecialchars($q["option_$opt"]) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="btn-group">
            <button type="button" id="prevBtn" onclick="prevQuestion()" class="btn">⬅ Previous</button>
            <button type="button" id="nextBtn" onclick="nextQuestion()" class="btn">Next ➡</button>
        </div>

        <button type="submit" class="btn submit-btn" id="submitBtn" style="display:none;">✅ Submit Exam</button>
    </form>
</div>

</body>
</html>
