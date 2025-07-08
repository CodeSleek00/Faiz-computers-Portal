<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required.");

$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
$student_id = $student['student_id'];
$exam_id = $_GET['exam_id'];

$exam = $conn->query("SELECT * FROM exams WHERE exam_id = $exam_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM exam_questions WHERE exam_id = $exam_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $exam['exam_name'] ?> - Exam</title>
    <script>
        let duration = <?= $exam['duration'] ?> * 60;
        function countdown() {
            if (duration <= 0) {
                alert("Time's up!");
                document.getElementById("examForm").submit();
            }
            let mins = Math.floor(duration / 60);
            let secs = duration % 60;
            document.getElementById("timer").innerText = mins + ":" + (secs < 10 ? "0" : "") + secs;
            duration--;
        }
        setInterval(countdown, 1000);
    </script>
    <style>
        body { font-family: Arial; padding: 30px; background: #eef2f5; }
        .container {
            max-width: 900px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .question { margin-top: 25px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h2><?= htmlspecialchars($exam['exam_name']) ?></h2>
            <p><strong>Name:</strong> <?= $student['name'] ?> | <strong>Enroll:</strong> <?= $student['enrollment_id'] ?></p>
        </div>
        <div>
            <img src="../uploads/<?= $student['photo'] ?>" width="80" style="border-radius: 50%">
            <p><strong>⏱️ <span id="timer"></span></strong></p>
        </div>
    </div>

    <form method="POST" action="submit_exam.php" id="examForm">
        <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
        <?php $qno = 1; while ($q = $questions->fetch_assoc()) { ?>
            <div class="question">
                <strong>Q<?= $qno++ ?>. <?= $q['question'] ?></strong><br>
                <?php foreach (['a', 'b', 'c', 'd'] as $opt) { ?>
                    <label>
                        <input type="radio" name="answers[<?= $q['question_id'] ?>]" value="<?= $opt ?>" required>
                        <?= strtoupper($opt) ?>. <?= $q["option_$opt"] ?>
                    </label><br>
                <?php } ?>
            </div>
        <?php } ?>
        <br>
        <button type="submit" style="padding: 10px 20px;">Submit Exam</button>
    </form>
</div>
</body>
</html>
