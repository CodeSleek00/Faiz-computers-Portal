<?php
include '../../database_connection/db_connect.php';

$exam_id = $_GET['exam_id'];
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['assign_type'];

    if ($type == 'student') {
        foreach ($_POST['student_ids'] as $student_id) {
            $conn->query("INSERT INTO exam_assignments (exam_id, student_id) VALUES ('$exam_id', '$student_id')");
        }
    } elseif ($type == 'batch') {
        foreach ($_POST['batch_ids'] as $batch_id) {
            $conn->query("INSERT INTO exam_assignments (exam_id, batch_id) VALUES ('$exam_id', '$batch_id')");
        }
    } elseif ($type == 'all') {
        $all_students = $conn->query("SELECT student_id FROM students");
        while ($s = $all_students->fetch_assoc()) {
            $sid = $s['student_id'];
            $conn->query("INSERT INTO exam_assignments (exam_id, student_id) VALUES ('$exam_id', '$sid')");
        }
    }

    echo "<script>alert('Exam reassigned successfully'); location.href='exam_dashboard.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Re-Assign Exam</title>
    <style>
        body { font-family: 'Poppins', sans-serif; padding: 20px; background: #f3f4f6; }
        .form-box { background: white; padding: 25px; border-radius: 10px; max-width: 700px; margin: auto; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; color: #4f46e5; }
        label { font-weight: 600; display: block; margin-top: 15px; }
        select, input[type=checkbox] { width: 100%; padding: 8px; margin-top: 5px; }
        .btn { margin-top: 20px; padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer; }
    </style>
    <script>
        function toggleSection(type) {
            document.getElementById('students').style.display = (type == 'student') ? 'block' : 'none';
            document.getElementById('batches').style.display = (type == 'batch') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="form-box">
        <h2>Re-Assign Exam</h2>
        <form method="POST">
            <label>Select Assignment Type:</label>
            <select name="assign_type" onchange="toggleSection(this.value)" required>
                <option value="">--Select--</option>
                <option value="student">Specific Students</option>
                <option value="batch">Batch</option>
                <option value="all">All Students</option>
            </select>

            <div id="students" style="display:none;">
                <label>Select Students:</label>
                <?php while ($s = $students->fetch_assoc()) { ?>
                    <input type="checkbox" name="student_ids[]" value="<?= $s['student_id'] ?>"> <?= $s['name'] ?><br>
                <?php } ?>
            </div>

            <div id="batches" style="display:none;">
                <label>Select Batches:</label>
                <?php while ($b = $batches->fetch_assoc()) { ?>
                    <input type="checkbox" name="batch_ids[]" value="<?= $b['batch_id'] ?>"> <?= $b['batch_name'] ?><br>
                <?php } ?>
            </div>

            <button type="submit" class="btn">✅ Re-Assign</button>
        </form>
    </div>
</body>
</html>
