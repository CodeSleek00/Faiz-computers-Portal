<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];
$total = $_GET['total'];
$q_num = isset($_GET['q_num']) ? $_GET['q_num'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $a = $_POST['option_a'];
    $b = $_POST['option_b'];
    $c = $_POST['option_c'];
    $d = $_POST['option_d'];
    $correct = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO exam_questions (exam_id, question, option_a, option_b, option_c, option_d, correct_option)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $exam_id, $question, $a, $b, $c, $d, $correct);
    $stmt->execute();

    if ($q_num < $total) {
        header("Location: add_question.php?exam_id=$exam_id&total=$total&q_num=" . ($q_num + 1));
    } else {
        header("Location: assign_exam.php?exam_id=$exam_id");
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 40px; }
        .form-box {
            max-width: 700px; margin: auto; background: #fff; padding: 30px;
            border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; }
        button { background: #28a745; color: white; padding: 12px; border: none; width: 100%; border-radius: 6px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Question <?= $q_num ?> of <?= $total ?></h2>
    <form method="POST">
        <label>Question:</label>
        <textarea name="question" required></textarea>
        
        <label>Option A:</label>
        <input type="text" name="option_a" required>

        <label>Option B:</label>
        <input type="text" name="option_b" required>

        <label>Option C:</label>
        <input type="text" name="option_c" required>

        <label>Option D:</label>
        <input type="text" name="option_d" required>

        <label>Correct Option (a/b/c/d):</label>
        <input type="text" name="correct_option" pattern="[abcd]" required>

        <button type="submit">Save & Next</button>
    </form>
</div>
</body>
</html>
