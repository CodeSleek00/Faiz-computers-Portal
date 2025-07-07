<?php
include '../database_connection/db_connect.php';
session_start();

// Check login
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment_id) die("Please login to submit an assignment.");

// Get student ID
$student_query = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment_id'");
if ($student_query->num_rows == 0) die("Student not found.");
$student_id = $student_query->fetch_assoc()['student_id'];

// Get assignment details
$assignment_id = $_GET['assignment_id'] ?? null;
if (!$assignment_id) die("Assignment ID missing.");

$assignment = $conn->query("SELECT * FROM assignments WHERE assignment_id = $assignment_id")->fetch_assoc();
if (!$assignment) die("Assignment not found.");

// Check if already submitted
$submitted = $conn->query("SELECT * FROM assignment_submissions WHERE assignment_id = $assignment_id AND student_id = $student_id");
if ($submitted->num_rows > 0) die("You have already submitted this assignment.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Assignment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; padding: 40px; }
        .container {
            background: white; max-width: 700px; margin: auto;
            padding: 30px; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        textarea, input[type=file] {
            width: 100%; padding: 10px; margin: 15px 0;
            border-radius: 8px; border: 1px solid #ccc;
        }
        .question {
            background: #f9f9f9; padding: 15px; border-radius: 10px;
            margin-bottom: 20px;
        }
        .img-preview { max-width: 100%; margin-top: 10px; }
        button {
            background: #28a745; color: white;
            padding: 12px 20px; border: none;
            border-radius: 8px; font-size: 16px;
            cursor: pointer; width: 100%;
        }
        button:hover { background: #218838; }
    </style>
</head>
<body>

<div class="container">
    <h2>Submit Assignment</h2>

    <div class="question">
        <strong><?= htmlspecialchars($assignment['title']) ?> (<?= $assignment['marks'] ?> Marks)</strong><br><br>
        <?php if ($assignment['question_text']) echo nl2br(htmlspecialchars($assignment['question_text'])); ?>
        <?php if ($assignment['question_image']) { ?>
            <img class="img-preview" src="../uploads/assignments/<?= $assignment['question_image'] ?>">
        <?php } ?>
    </div>

    <form method="POST" action="upload_submission.php" enctype="multipart/form-data">
        <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
        <label>Upload Answer (PDF/Image - optional):</label>
        <input type="file" name="submitted_file">

        <label>OR Write Answer:</label>
        <textarea name="submitted_text" rows="6" placeholder="Type your answer here..."></textarea>

        <button type="submit">Submit Assignment</button>
    </form>
</div>

</body>
</html>
