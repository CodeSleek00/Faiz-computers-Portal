<?php
include '../../database_connection/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = trim($_POST['exam_name']);
    $total_questions = intval($_POST['total_questions']);
    $duration = intval($_POST['duration']);
    $marks = intval($_POST['marks']);

    $stmt = $conn->prepare("INSERT INTO exams (exam_name, total_questions, duration, marks_per_question) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $exam_name, $total_questions, $duration, $marks);
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --bg: #f4f7fa;
            --white: #fff;
            --gray: #6b7280;
            --radius: 12px;
            --shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: var(--bg);
            padding: 40px 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"], input[type="number"] {
            padding: 12px;
            border-radius: var(--radius);
            border: 1px solid #ccc;
            font-size: 15px;
        }

        input:focus {
            border-color: var(--primary);
            outline: none;
        }

        button {
            padding: 14px;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 600;
            background: var(--primary);
            color: var(--white);
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover { background: #4338ca; }

        @media(max-width:480px){
            .container{padding:20px;}
            h2{font-size:20px;}
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📝 Create New Exam</h2>
    <form method="POST">
        <input type="text" name="exam_name" placeholder="Exam Name" required>
        <input type="number" name="total_questions" placeholder="Total Questions" required min="1">
        <input type="number" name="marks" placeholder="Marks per Question" required min="1">
        <input type="number" name="duration" placeholder="Duration (minutes)" required min="1">
        <button type="submit">➕ Add Questions</button>
    </form>
</div>

</body>
</html>
