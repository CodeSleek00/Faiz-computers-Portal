<?php
include '../database_connection/db_connect.php';

// Fetch all assignments, batches, and students
$assignments = $conn->query("SELECT * FROM assignments ORDER BY created_at DESC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Assignment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f2f4f8; padding: 40px; }
        .container {
            max-width: 700px; margin: auto; background: #fff;
            padding: 30px; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        label { font-weight: 600; display: block; margin-top: 15px; }
        select, button {
            width: 100%; padding: 12px; border-radius: 8px;
            border: 1px solid #ccc; margin-top: 8px;
        }
        select[multiple] { height: 150px; }
        button {
            margin-top: 25px; background: #007bff;
            color: white; border: none; font-size: 16px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Assign Assignment</h2>
    <form action="assignment_data.php" method="POST">
        <label for="assignment_id">Select Assignment:</label>
        <select name="assignment_id" required>
            <option value="">-- Select --</option>
            <?php while($a = $assignments->fetch_assoc()) { ?>
                <option value="<?= $a['assignment_id'] ?>"><?= htmlspecialchars($a['title']) ?></option>
            <?php } ?>
        </select>

        <label for="batch_id">Assign to Batch (optional):</label>
        <select name="batch_id">
            <option value="">-- None --</option>
            <?php $batches->data_seek(0); while($b = $batches->fetch_assoc()) { ?>
                <option value="<?= $b['batch_id'] ?>"><?= htmlspecialchars($b['batch_name']) ?> (<?= $b['timing'] ?>)</option>
            <?php } ?>
        </select>

        <label for="students[]">Or Select Specific Students (hold Ctrl to select multiple):</label>
        <select name="students[]" multiple>
            <?php $students->data_seek(0); while($s = $students->fetch_assoc()) { ?>
                <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= $s['enrollment_id'] ?>)</option>
            <?php } ?>
        </select>

        <button type="submit">✅ Assign</button>
    </form>
</div>

</body>
</html>
