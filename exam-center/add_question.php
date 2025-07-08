
<?php
include '../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id']);
$total = intval($_GET['total']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $options = $_POST['options'];
    $correct = $_POST['correct'];
    $q_num = intval($_POST['q_num']);

    $stmt = $conn->prepare("INSERT INTO exam_questions (exam_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $exam_id, $question, $options['a'], $options['b'], $options['c'], $options['d'], $correct);
    $stmt->execute();

    if ($q_num < $total) {
        header("Location: add_question.php?exam_id=$exam_id&total=$total&q_num=" . ($q_num + 1));
    } else {
        header("Location: assign_exam.php?exam_id=$exam_id");
    }
    exit;
}

$q_num = isset($_GET['q_num']) ? intval($_GET['q_num']) : 1;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f9f9f9; }
        .form-container {
            max-width: 700px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        label { font-weight: bold; display: block; margin-top: 15px; }
        input[type=text] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        .options input { margin-top: 5px; }
        button {
            background: #28a745; color: white; border: none; padding: 12px;
            width: 100%; border-radius: 8px; margin-top: 25px; cursor: pointer;
        }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add Question <?= $q_num ?> of <?= $total ?></h2>
        <form method="POST">
            <input type="hidden" name="q_num" value="<?= $q_num ?>">
            <label>Question:</label>
            <input type="text" name="question" required>

            <div class="options">
                <label>Option A:</label>
                <input type="text" name="options[a]" required>

                <label>Option B:</label>
                <input type="text" name="options[b]" required>

                <label>Option C:</label>
                <input type="text" name="options[c]" required>

                <label>Option D:</label>
                <input type="text" name="options[d]" required>
            </div>

            <label>Correct Option (a/b/c/d):</label>
            <input type="text" name="correct" pattern="[abcd]" required>

            <button type="submit">Save & Next</button>
        </form>
    </div>
</body>
</html>

