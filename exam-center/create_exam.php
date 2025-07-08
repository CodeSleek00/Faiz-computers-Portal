
<?php
include '../database_connection/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = $_POST['exam_name'];
    $total_questions = intval($_POST['total_questions']);
    $duration = intval($_POST['duration']);

    $stmt = $conn->prepare("INSERT INTO exams (exam_name, total_questions, duration, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sii", $exam_name, $total_questions, $duration);
    $stmt->execute();

    $exam_id = $stmt->insert_id;
    header("Location: add_question.php?exam_id=$exam_id&total=$total_questions");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Exam</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f2f2f2; }
        .form-container {
            max-width: 600px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        label { font-weight: bold; margin-top: 15px; display: block; }
        input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; margin-top: 5px; }
        button {
            background: #007bff; color: white; border: none; padding: 12px;
            width: 100%; border-radius: 8px; margin-top: 25px; cursor: pointer;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create New Exam</h2>
        <form method="POST">
            <label>Exam Name</label>
            <input type="text" name="exam_name" required>

            <label>Number of Questions</label>
            <input type="number" name="total_questions" min="1" required>

            <label>Duration (minutes)</label>
            <input type="number" name="duration" min="1" required>

            <button type="submit">Create Exam</button>
        </form>
    </div>
</body>
</html>

