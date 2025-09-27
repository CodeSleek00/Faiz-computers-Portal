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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Existing Video - Admin Portal</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* --- Existing Styles kept same --- */

        /* New styles for search */
        .search-box {
            margin-bottom: 10px;
        }
        .search-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
        }
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

        // Student search filter
        function filterStudents() {
            let input = document.getElementById("studentSearch").value.toLowerCase();
            let options = document.getElementById("student_id").options;

            for (let i = 0; i < options.length; i++) {
                let text = options[i].text.toLowerCase();
                options[i].style.display = text.includes(input) ? "" : "none";
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();

            const fileCount = document.querySelector('select[name="filename"]').length - 1;
            if (fileCount > 0) {
                const label = document.querySelector('label[for="filename"]');
                label.innerHTML += `<span class="file-count">${fileCount} videos available</span>`;
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <a href="admin_videos.php" class="back-btn">Back to Admin Portal</a>
        
        <h1>Assign Existing Video</h1>
        
        <div class="form-container">
            <form action="reassign_video.php" method="post" enctype="multipart/form-data">
                <!-- Video selection -->
                <div class="form-group">
                    <label for="filename">Choose Video from Server</label>
                    <select name="filename" required>
                        <option value="">-- Select Existing Video --</option>
                        <?php foreach($existing_videos as $file) { ?>
                            <option value="<?= $file ?>"><?= $file ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Video Title <span class="optional">(optional)</span></label>
                    <input type="text" name="title" placeholder="Enter video title">
                </div>

                <div class="form-group">
                    <label for="description">Video Description <span class="optional">(optional)</span></label>
                    <textarea name="description" placeholder="Enter video description"></textarea>
                </div>

                <div class="form-group">
                    <label for="thumbnail">Upload Thumbnail <span class="optional">(optional)</span></label>
                    <input type="file" name="thumbnail" accept="image/*">
                </div>

                <!-- Assign to -->
                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select name="assigned_to" id="assigned_to" onchange="toggleFields()" required>
                        <option value="all">All Students</option>
                        <option value="batch">Specific Batch(es)</option>
                        <option value="student">Specific Student</option>
                    </select>
                </div>

                <!-- Multi Batch Dropdown -->
                <div id="batch_select" class="form-group hidden">
                    <label for="batch_id">Select Batch(es)</label>
                    <select name="batch_id[]" id="batch_id" multiple size="5">
                        <?php while($b = $batches->fetch_assoc()) { ?>
                            <option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
                        <?php } ?>
                    </select>
                    <small style="color: var(--dark-gray);">Hold CTRL (Windows) or CMD (Mac) to select multiple</small>
                </div>

                <!-- Student Dropdown with Search -->
                <div id="student_select" class="form-group hidden">
                    <label for="student_id">Search & Select Student</label>
                    <div class="search-box">
                        <input type="text" id="studentSearch" onkeyup="filterStudents()" placeholder="Search student by name or ID...">
                    </div>
                    <select name="student_id" id="student_id" size="7">
                        <?php while($s = $students->fetch_assoc()) { ?>
                            <option value="<?= $s['student_id'] ?>"><?= $s['name'] ?> (ID: <?= $s['student_id'] ?>)</option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit">Assign Video</button>
            </form>
        </div>
    </div>
</body>
</html>
