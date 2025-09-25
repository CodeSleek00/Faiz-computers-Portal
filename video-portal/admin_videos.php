<?php
include 'db_connect.php';

// Fetch all batches
$batches = $conn->query("SELECT batch_id, batch_name FROM batches");

// Fetch all students
$students = $conn->query("SELECT student_id, name FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Video Portal</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .video-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 8px; }
        input, textarea, select { width: 100%; padding: 8px; margin: 6px 0; }
        button { padding: 10px 15px; cursor: pointer; }
        .hidden { display: none; }
        .search-box { margin-bottom: 10px; }
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

        function filterOptions(inputId, selectId) {
            let input = document.getElementById(inputId).value.toLowerCase();
            let select = document.getElementById(selectId);
            let options = select.getElementsByTagName("option");

            for (let i = 0; i < options.length; i++) {
                let txt = options[i].textContent.toLowerCase();
                if (txt.indexOf(input) > -1 || options[i].value === "") {
                    options[i].style.display = "";
                } else {
                    options[i].style.display = "none";
                }
            }
        }
    </script>
</head>
<body>
    <h2>Upload Video</h2>
    <form action="upload_video.php" method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Video Title" required>
        <textarea name="description" placeholder="Video Description"></textarea>
        <input type="file" name="video" accept="video/*" required>

        <select name="assigned_to" id="assigned_to" onchange="toggleFields()" required>
            <option value="all">All Students</option>
            <option value="batch">Specific Batch</option>
            <option value="student">Specific Student</option>
        </select>

        <!-- Batch Dropdown with Search -->
        <div id="batch_select" class="hidden">
            <label>Select Batch:</label>
            <input type="text" id="batchSearch" class="search-box" onkeyup="filterOptions('batchSearch','batchDropdown')" placeholder="Search batch...">
            <select name="batch_id" id="batchDropdown">
                <option value="">-- Select Batch --</option>
                <?php while($b = $batches->fetch_assoc()) { ?>
                    <option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- Student Dropdown with Search -->
        <div id="student_select" class="hidden">
            <label>Select Student:</label>
            <input type="text" id="studentSearch" class="search-box" onkeyup="filterOptions('studentSearch','studentDropdown')" placeholder="Search student...">
            <select name="student_id" id="studentDropdown">
                <option value="">-- Select Student --</option>
                <?php while($s = $students->fetch_assoc()) { ?>
                    <option value="<?= $s['student_id'] ?>"><?= $s['name'] ?> (ID: <?= $s['student_id'] ?>)</option>
                <?php } ?>
            </select>
        </div>

        <button type="submit">Upload</button>
    </form>

    <h2>Uploaded Videos</h2>
    <?php
    $result = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");
    while($row = $result->fetch_assoc()) {
        echo "<div class='video-box'>
            <h3>{$row['title']}</h3>
            <p>{$row['description']}</p>
            <video width='300' controls>
                <source src='uploads/videos/{$row['filename']}' type='video/mp4'>
            </video><br>
            <a href='uploads/videos/{$row['filename']}' download>Download</a> |
            <a href='delete_video.php?id={$row['id']}'>Delete</a>
        </div>";
    }
    ?>
</body>
</html>
