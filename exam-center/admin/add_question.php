<?php
include '../../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id']);
$total = intval($_GET['total']);
$q_num = isset($_GET['q_num']) ? intval($_GET['q_num']) : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $a = trim($_POST['option_a']);
    $b = trim($_POST['option_b']);
    $c = trim($_POST['option_c']);
    $d = trim($_POST['option_d']);
    $correct = trim($_POST['correct_option']);

    $stmt = $conn->prepare("INSERT INTO exam_questions (exam_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
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
    <title>Add Exam Question</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #28a745;
            --bg: #f4f7fa;
            --white: #fff;
            --radius: 12px;
            --shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: var(--bg);
            padding: 40px 20px;
        }

        .container {
            max-width: 750px;
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

        form { display: flex; flex-direction: column; gap: 15px; }

        label { font-weight: 500; margin-bottom: 5px; }
        textarea, input[type="text"] {
            padding: 12px;
            border-radius: var(--radius);
            border: 1px solid #ccc;
            font-size: 15px;
            resize: vertical;
        }
        textarea { min-height: 100px; }

        input:focus, textarea:focus { border-color: var(--primary); outline: none; }

        button {
            padding: 14px;
            border: none;
            border-radius: var(--radius);
            font-size: 16px;
            font-weight: 600;
            background: var(--success);
            color: var(--white);
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover { background: #218838; }

        @media(max-width:480px){
            .container{padding:20px;}
            h2{font-size:20px;}
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🧾 Question <?= $q_num ?> of <?= $total ?></h2>
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

        <label>Correct Option (a / b / c / d):</label>
        <input type="text" name="correct_option" pattern="[abcd]" required>

        <button type="submit">💾 Save & Next</button>
    </form>
</div>

</body>
</html>
