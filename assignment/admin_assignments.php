<?php include '../database_connection/db_connect.php'; ?>

<?php
// Fetch batches and students
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Assignment</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 30px;
            background: #f4f7fa;
        }
        .form-box {
            background: #fff;
            max-width: 700px;
            margin: auto;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        label {
            font-weight: 600;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Create Assignment</h2>
    <form action="upload_assignment.php" method="POST" enctype="multipart/form-data">
        <label>Assignment Title:</label>
        <input type="text" name="title" required>

        <label>Assignment Question (Text):</label>
        <textarea name="question_text" rows="4"></textarea>

        <label>Assignment Question (Image - Optional):</label>
        <input type="file" name="question_image">

        <label>Total Marks:</label>
        <input type="number" name="marks" required>

        <label>Assign To:</label>
        <select name="target_type" onchange="toggleTarget(this.value)" required>
            <option value="all">All Students</option>
            <option value="batch">Specific Batch</option>
            <option value="student">Specific Students</option>
        </select>

        <div id="batchSelect" style="display: none;">
            <label>Select Batch:</label>
            <select name="batch_id">
                <option value="">--Select Batch--</option>
                <?php while ($row = $batches->fetch_assoc()) { ?>
                    <option value="<?= $row['batch_id'] ?>"><?= $row['batch_name'] ?></option>
                <?php } ?>
            </select>
        </div>

        <div id="studentSelect" style="display: none;">
            <label>Select Students:</label>
            <select name="student_ids[]" multiple size="6">
                <?php while ($s = $students->fetch_assoc()) { ?>
                    <option value="<?= $s['student_id'] ?>"><?= $s['name'] ?> (<?= $s['enrollment_id'] ?>)</option>
                <?php } ?>
            </select>
        </div>

        <button type="submit">Create Assignment</button>
    </form>
</div>

<script>
function toggleTarget(value) {
    document.getElementById('batchSelect').style.display = (value === 'batch') ? 'block' : 'none';
    document.getElementById('studentSelect').style.display = (value === 'student') ? 'block' : 'none';
}
</script>

</body>
</html>
