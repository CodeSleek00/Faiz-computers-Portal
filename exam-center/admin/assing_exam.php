<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];
$students = $conn->query("SELECT student_id, name, enrollment_id FROM students");
$batches = $conn->query("SELECT * FROM batches");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targets = $_POST['targets'];

    foreach ($targets as $target) {
        if (strpos($target, 'batch_') !== false) {
            $batch_id = str_replace('batch_', '', $target);
            $conn->query("INSERT INTO exam_assignments (exam_id, batch_id) VALUES ($exam_id, $batch_id)");
        } else {
            $student_id = str_replace('student_', '', $target);
            $conn->query("INSERT INTO exam_assignments (exam_id, student_id) VALUES ($exam_id, $student_id)");
        }
    }

    header("Location: exam_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Exam</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f7f7f7; }
        .form-box {
            max-width: 700px; margin: auto; background: #fff; padding: 30px;
            border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        label { display: block; margin: 10px 0; }
        input[type=checkbox] { margin-right: 10px; }
        button {
            background: #007bff; color: white; padding: 12px;
            border: none; width: 100%; border-radius: 6px; margin-top: 20px;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Assign Exam</h2>
    <form method="POST">
        <h4>📚 Batches</h4>
        <?php while ($b = $batches->fetch_assoc()) { ?>
            <label><input type="checkbox" name="targets[]" value="batch_<?= $b['batch_id'] ?>"> <?= $b['batch_name'] ?></label>
        <?php } ?>

        <h4>👤 Individual Students</h4>
        <?php while ($s = $students->fetch_assoc()) { ?>
            <label><input type="checkbox" name="targets[]" value="student_<?= $s['student_id'] ?>"> <?= $s['name'] ?> (<?= $s['enrollment_id'] ?>)</label>
        <?php } ?>

        <button type="submit">✅ Assign</button>
    </form>
</div>
</body>
</html>
