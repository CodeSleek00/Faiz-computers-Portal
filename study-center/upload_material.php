<?php
include '../database_connection/db_connect.php';
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Study Material</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef2f5; padding: 40px; }
        .box {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        h2 { margin-bottom: 20px; }
        label { font-weight: 600; margin-top: 15px; display: block; }
        input, select {
            width: 100%; padding: 10px; margin-top: 5px;
            border-radius: 8px; border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="box">
    <h2>Upload Study Material</h2>
    <form action="study_material_data.php" method="POST" enctype="multipart/form-data">
        <label>Title</label>
        <input type="text" name="title" required>

        <label>Upload PDF</label>
        <input type="file" name="pdf" accept=".pdf" required>

        <label>Assign To:</label>
        <select name="assign_type" onchange="toggleTarget(this.value)" required>
            <option value="">Select</option>
            <option value="all">All Students</option>
            <option value="batch">Batch</option>
            <option value="student">Student</option>
        </select>

        <div id="batchBox" style="display:none;">
            <label>Select Batch</label>
            <select name="batch_id">
                <option value="">-- Select Batch --</option>
                <?php while ($row = $batches->fetch_assoc()) {
                    echo "<option value='{$row['batch_id']}'>{$row['batch_name']} ({$row['timing']})</option>";
                } ?>
            </select>
        </div>

        <div id="studentBox" style="display:none;">
            <label>Select Student</label>
            <select name="student_id">
                <option value="">-- Select Student --</option>
                <?php while ($row = $students->fetch_assoc()) {
                    echo "<option value='{$row['student_id']}'>{$row['name']} ({$row['enrollment_id']})</option>";
                } ?>
            </select>
        </div>

        <button type="submit" name="upload">Upload</button>
    </form>
</div>

<script>
function toggleTarget(val) {
    document.getElementById('batchBox').style.display = (val === 'batch') ? 'block' : 'none';
    document.getElementById('studentBox').style.display = (val === 'student') ? 'block' : 'none';
}
</script>

</body>
</html>
