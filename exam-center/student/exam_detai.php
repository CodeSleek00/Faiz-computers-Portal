x<?php
include '../../database_connection/db_connect.php';
session_start();

$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Login required");

// Fetch student
$student = $conn->query("SELECT student_id FROM students WHERE enrollment_id='$enrollment_id'")->fetch_assoc();
if (!$student) {
    $student = $conn->query("SELECT id AS student_id FROM students26 WHERE enrollment_id='$enrollment_id'")->fetch_assoc();
}
$student_id = $student['student_id'];

$exam_id = intval($_GET['exam_id']);

// Fetch answers
$result = $conn->query("
SELECT 
    eq.question,
    eq.option_a,
    eq.option_b,
    eq.option_c,
    eq.option_d,
    eq.correct_option,
    sa.selected_option,
    sa.is_correct
FROM student_answers sa
JOIN exam_questions eq ON sa.question_id = eq.question_id
WHERE sa.exam_id = $exam_id 
AND sa.student_id = $student_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Exam Detail</title>
<style>
body { font-family:Poppins; background:#f4f6f9; }
.card { max-width:800px; margin:20px auto; background:#fff; padding:20px; border-radius:10px; }
.correct { color:green; }
.wrong { color:red; }
</style>
</head>
<body>

<div class="card">
<h2>Detailed Result</h2>

<?php while($row = $result->fetch_assoc()): ?>
    <div style="margin-bottom:20px;">
        <p><b><?= $row['question'] ?></b></p>

        <p>A. <?= $row['option_a'] ?></p>
        <p>B. <?= $row['option_b'] ?></p>
        <p>C. <?= $row['option_c'] ?></p>
        <p>D. <?= $row['option_d'] ?></p>

        <p><b>Your Answer:</b> <?= strtoupper($row['selected_option']) ?></p>
        <p><b>Correct Answer:</b> <?= strtoupper($row['correct_option']) ?></p>

        <?php if($row['is_correct']): ?>
            <p class="correct">✔ Correct</p>
        <?php else: ?>
            <p class="wrong">✘ Wrong</p>
        <?php endif; ?>
    </div>
    <hr>
<?php endwhile; ?>

</div>

</body>
</html>