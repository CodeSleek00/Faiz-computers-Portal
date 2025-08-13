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
    <title>Add Exam Question</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #28a745;
            --light-bg: #f3f4f6;
            --white: #ffffff;
            --gray: #6b7280;
            --radius: 10px;
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            margin: 0;
            padding: 40px 20px;
            color: #333;
        }

        .form-box {
            background: var(--white);
            max-width: 750px;
            margin: auto;
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--primary);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: var(--radius);
            font-size: 15px;
            resize: vertical;
        }

        textarea {
            min-height: 100px;
        }

        input:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        button {
            background: var(--success);
            color: var(--white);
            border: none;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #218838;
        }

        @media (max-width: 480px) {
            body {
                padding: 20px;
            }

            .form-box {
                padding: 20px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>🧾 Question <?= $q_num ?> of <?= $total ?></h2>
    <form method="POST">
        <label for="question">Question:</label>
        <textarea name="question" id="question" required></textarea>

        <label for="option_a">Option A:</label>
        <input type="text" name="option_a" id="option_a" required>

        <label for="option_b">Option B:</label>
        <input type="text" name="option_b" id="option_b" required>

        <label for="option_c">Option C:</label>
        <input type="text" name="option_c" id="option_c" required>

        <label for="option_d">Option D:</label>
        <input type="text" name="option_d" id="option_d" required>

        <label for="correct_option">Correct Option (a / b / c / d):</label>
        <input type="text" name="correct_option" id="correct_option" pattern="[abcd]" required>

        <button type="submit">💾 Save & Next</button>
    </form>
</div>

</body>
</html>
