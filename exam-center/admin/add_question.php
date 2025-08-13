<?php
include '../../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
    $a = $_POST['option_a'];
    $b = $_POST['option_b'];
    $c = $_POST['option_c'];
    $d = $_POST['option_d'];
    $correct = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO exam_questions 
        (exam_id, question, option_a, option_b, option_c, option_d, correct_option)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $exam_id, $question, $a, $b, $c, $d, $correct);
    $stmt->execute();

    header("Location: add_question.php?exam_id=$exam_id&success=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Question</title>
</head>
<body>
<h2>Add Question to Exam</h2>
<?php if (isset($_GET['success'])) echo "<p style='color:green;'>Question added successfully!</p>"; ?>
<form method="POST">
    <textarea name="question" required></textarea><br>
    Option A: <input type="text" name="option_a" required><br>
    Option B: <input type="text" name="option_b" required><br>
    Option C: <input type="text" name="option_c" required><br>
    Option D: <input type="text" name="option_d" required><br>
    Correct (a/b/c/d): <input type="text" name="correct_option" pattern="[abcd]" required><br>
    <button type="submit">Save Question</button>
</form>

<h3>Existing Questions</h3>
<ul>
<?php
$result = $conn->query("SELECT id, question FROM exam_questions WHERE exam_id = $exam_id");
while ($row = $result->fetch_assoc()) {
    echo "<li>" . htmlspecialchars($row['question']) . "</li>";
}
?>
</ul>
</body>
</html>
