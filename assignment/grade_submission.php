<?php
include '../database_connection/db_connect.php';

$submission_id = $_GET['id'] ?? null;
if (!$submission_id || !is_numeric($submission_id)) die("Invalid submission ID.");

// Get submission with assignment & student details
$query = "
    SELECT s.*, st.name AS student_name, st.enrollment_id, a.title AS assignment_title, a.marks AS total_marks
    FROM assignment_submissions s
    JOIN students st ON s.student_id = st.student_id
    JOIN assignments a ON s.assignment_id = a.assignment_id
    WHERE s.submission_id = $submission_id
";
$result = $conn->query($query);
if ($result->num_rows == 0) die("Submission not found.");

$submission = $result->fetch_assoc();

// Handle grading submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $marks_awarded = intval($_POST['marks_awarded']);
    $feedback = trim($_POST['feedback']);

    $stmt = $conn->prepare("UPDATE assignment_submissions SET marks_awarded = ?, feedback = ? WHERE submission_id = ?");
    $stmt->bind_param("isi", $marks_awarded, $feedback, $submission_id);
    $stmt->execute();

    header("Location: view_submissions.php?graded=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grade Submission</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f6f8; padding: 40px; }
        .container {
            max-width: 700px; margin: auto; background: white;
            padding: 30px; border-radius: 16px; box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        h2, h3 { margin-bottom: 10px; color: #333; }
        .section { margin-top: 20px; }
        textarea, input[type=number] {
            width: 100%; padding: 10px; margin-top: 10px;
            border: 1px solid #ccc; border-radius: 8px;
        }
        button {
            background: #007bff; color: white;
            padding: 12px 20px; border: none;
            border-radius: 10px; font-size: 16px;
            margin-top: 20px; width: 100%;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
        .file-link {
            display: inline-block; margin-top: 10px;
        }
        .back-link {
            display: block; text-align: center; margin-top: 25px;
            color: #007bff; text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Grade Assignment</h2>

    <div><strong>Assignment:</strong> <?= htmlspecialchars($submission['assignment_title']) ?> (<?= $submission['total_marks'] ?> marks)</div>
    <div><strong>Student:</strong> <?= htmlspecialchars($submission['student_name']) ?> (<?= $submission['enrollment_id'] ?>)</div>

    <div class="section">
        <h3>Answer (Written):</h3>
        <p><?= nl2br(htmlspecialchars($submission['submitted_text'] ?: "No text submitted.")) ?></p>
    </div>

    <?php if ($submission['submitted_file']) { ?>
        <div class="section">
            <h3>Attached File:</h3>
            <a class="file-link" href="../uploads/submissions/<?= $submission['submitted_file'] ?>" target="_blank">📎 View Uploaded File</a>
        </div>
    <?php } ?>

    <form method="POST">
        <div class="section">
            <label for="marks_awarded">Marks Awarded (out of <?= $submission['total_marks'] ?>):</label>
            <input type="number" name="marks_awarded" min="0" max="<?= $submission['total_marks'] ?>" required>
        </div>

        <div class="section">
            <label for="feedback">Feedback (optional):</label>
            <textarea name="feedback" rows="4" placeholder="Write feedback if any..."></textarea>
        </div>

        <button type="submit">✅ Save Grade</button>
    </form>

    <a class="back-link" href="view_submissions.php">⬅ Back to Submissions</a>
</div>

</body>
</html>
