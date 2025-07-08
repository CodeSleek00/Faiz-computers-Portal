
<?php
include '../database_connection/db_connect.php';

$exam_id = intval($_GET['exam_id']);
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("SELECT * FROM students");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targets = $_POST['targets'] ?? [];

    foreach ($targets as $target) {
        if (strpos($target, 'batch_') === 0) {
            $batch_id = intval(str_replace('batch_', '', $target));
            $conn->query("INSERT INTO exam_assignments (exam_id, batch_id) VALUES ($exam_id, $batch_id)");
        } elseif (strpos($target, 'student_') === 0) {
            $student_id = intval(str_replace('student_', '', $target));
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
        body { font-family: Arial; background: #f5f5f5; padding: 40px; }
        .container {
            max-width: 700px; margin: auto; background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        .section-title { margin-top: 20px; font-weight: bold; }
        .checkbox-group { margin: 10px 0; }
        label { display: block; margin-bottom: 6px; }
        button {
            background: #007bff; color: white; padding: 10px 20px; border: none;
            border-radius: 8px; cursor: pointer; margin-top: 20px; width: 100%;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Assign Exam to Students or Batches</h2>
        <form method="POST">
            <div class="section-title">Select Batches:</div>
            <div class="checkbox-group">
                <?php while ($row = $batches->fetch_assoc()) { ?>
                    <label><input type="checkbox" name="targets[]" value="batch_<?= $row['batch_id'] ?>"> <?= htmlspecialchars($row['batch_name']) ?></label>
                <?php } ?>
            </div>

            <div class="section-title">Select Individual Students:</div>
            <div class="checkbox-group">
                <?php while ($row = $students->fetch_assoc()) { ?>
                    <label><input type="checkbox" name="targets[]" value="student_<?= $row['student_id'] ?>"> <?= htmlspecialchars($row['name']) ?> (<?= $row['enrollment_id'] ?>)</label>
                <?php } ?>
            </div>

            <button type="submit">Assign Exam</button>
        </form>
    </div>
</body>
</html>
