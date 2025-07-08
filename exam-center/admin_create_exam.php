<?php
include '../database_connection/db_connect.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create new exam
    $exam_name = $_POST['exam_name'];
    $duration = $_POST['duration'];
    $passing_marks = $_POST['passing_marks'];
    $show_info = isset($_POST['show_student_info']) ? 1 : 0;
    
    $sql = "INSERT INTO exams (exam_name, duration_minutes, passing_marks, show_student_info) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siii", $exam_name, $duration, $passing_marks, $show_info);
    $stmt->execute();
    
    $exam_id = $conn->insert_id;
    
    // Add questions
    foreach($_POST['questions'] as $q) {
        $sql = "INSERT INTO exam_questions 
                (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $exam_id, $q['text'], $q['a'], $q['b'], $q['c'], $q['d'], $q['correct']);
        $stmt->execute();
    }
    
    // Assign to batches/students
    if(!empty($_POST['batches'])) {
        foreach($_POST['batches'] as $batch_id) {
            $sql = "INSERT INTO exam_targets (exam_id, batch_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $exam_id, $batch_id);
            $stmt->execute();
        }
    }
    
    if(!empty($_POST['students'])) {
        foreach($_POST['students'] as $student_id) {
            $sql = "INSERT INTO exam_targets (exam_id, student_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $exam_id, $student_id);
            $stmt->execute();
        }
    }
    
    header("Location: admin_exams.php?success=Exam created successfully");
    exit();
}

// Get batches and students for assignment
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Exam</title>
    <style>
        .question-block { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; }
        .option-row { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>Create New Exam</h1>
    
    <form method="post">
        <div>
            <label>Exam Name: <input type="text" name="exam_name" required></label>
        </div>
        <div>
            <label>Duration (minutes): <input type="number" name="duration" required></label>
        </div>
        <div>
            <label>Passing Marks: <input type="number" name="passing_marks" required></label>
        </div>
        <div>
            <label>
                <input type="checkbox" name="show_student_info" checked>
                Show student photo/name during exam
            </label>
        </div>
        
        <h2>Questions</h2>
        <div id="questionsContainer">
            <div class="question-block">
                <div>
                    <label>Question Text: <textarea name="questions[0][text]" required></textarea></label>
                </div>
                <div class="option-row">
                    <label>Option A: <input type="text" name="questions[0][a]" required></label>
                    <label><input type="radio" name="questions[0][correct]" value="A" required> Correct</label>
                </div>
                <div class="option-row">
                    <label>Option B: <input type="text" name="questions[0][b]" required></label>
                    <label><input type="radio" name="questions[0][correct]" value="B"> Correct</label>
                </div>
                <div class="option-row">
                    <label>Option C: <input type="text" name="questions[0][c]" required></label>
                    <label><input type="radio" name="questions[0][correct]" value="C"> Correct</label>
                </div>
                <div class="option-row">
                    <label>Option D: <input type="text" name="questions[0][d]" required></label>
                    <label><input type="radio" name="questions[0][correct]" value="D"> Correct</label>
                </div>
            </div>
        </div>
        
        <button type="button" onclick="addQuestion()">Add Another Question</button>
        
        <h2>Assign To</h2>
        <h3>Batches</h3>
        <?php while($batch = $batches->fetch_assoc()): ?>
            <label>
                <input type="checkbox" name="batches[]" value="<?= $batch['batch_id'] ?>">
                <?= $batch['batch_name'] ?> (<?= $batch['timing'] ?>)
            </label><br>
        <?php endwhile; ?>
        
        <h3>Individual Students</h3>
        <?php while($student = $students->fetch_assoc()): ?>
            <label>
                <input type="checkbox" name="students[]" value="<?= $student['student_id'] ?>">
                <?= $student['name'] ?> (<?= $student['enrollment_id'] ?>)
            </label><br>
        <?php endwhile; ?>
        
        <button type="submit">Create Exam</button>
    </form>

    <script>
        let questionCount = 1;
        
        function addQuestion() {
            const container = document.getElementById('questionsContainer');
            const newQuestion = document.createElement('div');
            newQuestion.className = 'question-block';
            newQuestion.innerHTML = `
                <div>
                    <label>Question Text: <textarea name="questions[${questionCount}][text]" required></textarea></label>
                </div>
                <div class="option-row">
                    <label>Option A: <input type="text" name="questions[${questionCount}][a]" required></label>
                    <label><input type="radio" name="questions[${questionCount}][correct]" value="A" required> Correct</label>
                </div>
                <div class="option-row">
                    <label>Option B: <input type="text" name="questions[${questionCount}][b]" required></label>
                    <label><input type="radio" name="questions[${questionCount}][correct]" value="B"> Correct</label>
                </div>
                <div class="option-row">
                    <label>Option C: <input type="text" name="questions[${questionCount}][c]" required></label>
                    <label><input type="radio" name="questions[${questionCount}][correct]" value="C"> Correct</label>
                </div>
                <div class="option-row">
                    <label>Option D: <input type="text" name="questions[${questionCount}][d]" required></label>
                    <label><input type="radio" name="questions[${questionCount}][correct]" value="D"> Correct</label>
                </div>
            `;
            container.appendChild(newQuestion);
            questionCount++;
        }
    </script>
</body>
</html>