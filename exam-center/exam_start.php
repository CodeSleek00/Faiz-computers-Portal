<?php
include '../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'] ?? 0;
$student_id = $_SESSION['student_id'] ?? 0;

// Get exam details
$exam = $conn->query("SELECT * FROM exams WHERE exam_id=$exam_id")->fetch_assoc();
$student = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();

// Check if student is allowed to take this exam
$allowed = $conn->query("SELECT * FROM exam_targets WHERE exam_id=$exam_id AND 
                        (student_id=$student_id OR batch_id IN 
                        (SELECT batch_id FROM student_batches WHERE student_id=$student_id))")->num_rows;

if(!$allowed || $exam['status'] != 'active') {
    die("You are not authorized to take this exam.");
}

// Check if already submitted
$submitted = $conn->query("SELECT * FROM exam_submissions WHERE exam_id=$exam_id AND student_id=$student_id")->num_rows;
if($submitted) {
    die("You have already submitted this exam.");
}

// Start new exam session
$_SESSION['exam_start_time'] = date('Y-m-d H:i:s');
$_SESSION['exam_id'] = $exam_id;
$_SESSION['exam_duration'] = $exam['duration_minutes'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam: <?= $exam['exam_name'] ?></title>
    <style>
        .student-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .student-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .timer {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #333;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 18px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <?php if($exam['show_student_info']): ?>
    <div class="student-info">
        <img src="<?= $student['photo'] ?>" class="student-photo" alt="Student Photo">
        <div>
            <h3><?= $student['name'] ?></h3>
            <p>Enrollment ID: <?= $student['enrollment_id'] ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="timer" id="examTimer"></div>
    
    <h1><?= $exam['exam_name'] ?></h1>
    <p>Duration: <?= $exam['duration_minutes'] ?> minutes</p>
    
    <form id="examForm" action="submit_exam.php" method="post">
        <?php
        $questions = $conn->query("SELECT * FROM exam_questions WHERE exam_id=$exam_id");
        while($q = $questions->fetch_assoc()):
        ?>
        <div class="question">
            <h3>Question <?= $q['question_id'] ?></h3>
            <p><?= $q['question_text'] ?></p>
            
            <div class="options">
                <label><input type="radio" name="q<?= $q['question_id'] ?>" value="A"> <?= $q['option_a'] ?></label><br>
                <label><input type="radio" name="q<?= $q['question_id'] ?>" value="B"> <?= $q['option_b'] ?></label><br>
                <label><input type="radio" name="q<?= $q['question_id'] ?>" value="C"> <?= $q['option_c'] ?></label><br>
                <label><input type="radio" name="q<?= $q['question_id'] ?>" value="D"> <?= $q['option_d'] ?></label>
            </div>
        </div>
        <hr>
        <?php endwhile; ?>
        
        <button type="submit" id="submitBtn">Submit Exam</button>
    </form>

    <script>
        // Timer functionality
        const duration = <?= $exam['duration_minutes'] * 60 ?>;
        let timeLeft = duration;
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            document.getElementById('examTimer').textContent = `${minutes}:${seconds}`;
            
            if(timeLeft <= 0) {
                document.getElementById('examForm').submit();
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        }
        
        updateTimer();
        
        // Prevent accidental navigation
        window.onbeforeunload = function() {
            return "Are you sure you want to leave? Your exam progress may be lost.";
        };
    </script>
</body>
</html>