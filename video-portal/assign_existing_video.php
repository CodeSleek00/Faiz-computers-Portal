<?php
include 'db_connect.php';

// Fetch all batches
$batches = $conn->query("SELECT batch_id, batch_name FROM batches");

// Fetch all students
$students = $conn->query("SELECT student_id, name FROM students");

// Fetch files already in uploads/videos/
$existing_videos = array_diff(scandir("../uploads/videos/"), array('.', '..'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Existing Video</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-box { border: 1px solid #ccc; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; }
        input, textarea, select { width: 100%; padding: 8px; margin: 6px 0; }
        button { padding: 10px 15px; cursor: pointer; margin-top: 10px; }
        .hidden { display: none; }
    </style>
    <script>
        function toggleFields() {
            let assignType = document.getElementById("assigned_to").value;
            document.getElementById("batch_select").classList.add("hidden");
            document.getElementById("student_select").classList.add("hidden");

            if(assignType === "batch") {
                document.getElementById("batch_select").classList.remove("hidden");
            } else if(assignType === "student") {
                document.getElementById("student_select").classList.remove("hidden");
            }
        }
    </script>
</head>
<body>
    <h2>Assign Existing Video</h2>
    <div class="form-box">
        <form action="reassign_video.php" method="post" enctype="multipart/form-data">
            <label>Choose Video from Server:</label>
            <select name="filename" required>
                <option value="">-- Select Existing Video --</option>
                <?php foreach($existing_videos as $file) { ?>
                    <option value="<?= $file ?>"><?= $file ?></option>
                <?php } ?>
            </select>

            <input type="text" name="title" placeholder="Video Title (optional)">
            <textarea name="description" placeholder="Video Description (optional)"></textarea>

            <!-- Thumbnail Upload -->
            <label>Upload Thumbnail (optional):</label>
            <input type="file" name="thumbnail" accept="image/*">

            <label>Assign To:</label>
            <select name="assigned_to" id="assigned_to" onchange="toggleFields()" required>
                <option value="all">All Students</option>
                <option value="batch">Specific Batch</option>
                <option value="student">Specific Student</option>
            </select>

            <!-- Batch Dropdown -->
            <div id="batch_select" class="hidden">
                <label>Select Batch:</label>
                <select name="batch_id">
                    <option value="">-- Select Batch --</option>
                    <?php while($b = $batches->fetch_assoc()) { ?>
                        <option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- Student Dropdown -->
            <div id="student_select" class="hidden">
                <label>Select Student:</label>
                <select name="student_id">
                    <option value="">-- Select Student --</option>
                    <?php while($s = $students->fetch_assoc()) { ?>
                        <option value="<?= $s['student_id'] ?>"><?= $s['name'] ?> (ID: <?= $s['student_id'] ?>)</option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit">Assign Video</button>
        </form>
    </div>
</body>
</html>
