<?php
include 'db_connect.php';

// Fetch all batches
$batches = $conn->query("SELECT batch_id, batch_name FROM batches");

// Fetch all students
$students = $conn->query("SELECT student_id, name FROM students");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Video Portal</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --light-blue: #3b82f6;
            --accent-blue: #60a5fa;
            --white: #ffffff;
            --light-gray: #f8fafc;
            --medium-gray: #e2e8f0;
            --dark-gray: #64748b;
            --text-dark: #1e293b;
            --border-radius: 8px;
            --shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-dark);
            line-height: 1.5;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1, h2 {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 1.8rem;
            border-bottom: 1px solid var(--medium-gray);
            padding-bottom: 10px;
        }
        
        h2 {
            font-size: 1.4rem;
            margin-top: 30px;
        }
        
        /* Form Styles */
        .form-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        button:hover {
            background-color: var(--light-blue);
        }
        
        /* Hidden elements */
        .hidden {
            display: none;
        }
        
        /* Searchable dropdowns */
        .search-box {
            margin-bottom: 10px;
        }
        
        .dropdown-container {
            position: relative;
        }
        
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }
        
        /* Video list */
        .video-list {
            display: grid;
            gap: 20px;
        }
        
        .video-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .video-card h3 {
            color: var(--primary-blue);
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .video-card p {
            color: var(--dark-gray);
            line-height: 1.5;
        }
        
        .video-card video {
            max-width: 100%;
            border-radius: 4px;
        }
        
        .video-actions {
            display: flex;
            gap: 15px;
        }
        
        .video-actions a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .video-actions a:hover {
            background-color: var(--light-gray);
        }
        
        /* Responsive */
        @media (min-width: 768px) {
            body {
                padding: 30px;
            }
            
            .form-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            
            .full-width {
                grid-column: 1 / -1;
            }
            
            .video-list {
                grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            }
        }
        
        @media (max-width: 767px) {
            h1 {
                font-size: 1.5rem;
            }
            
            h2 {
                font-size: 1.2rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .video-card {
                padding: 15px;
            }
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
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
        });
    </script>
</head>
<body>
    <h1>Admin Video Portal</h1>
    
    <div class="form-container">
    <h2>Upload New Video</h2>
    <form action="upload_video.php" method="post" enctype="multipart/form-data">
        <div class="form-group full-width">
            <label for="title">Video Title</label>
            <input type="text" name="title" placeholder="Enter video title" required>
        </div>

        <div class="form-group full-width">
            <label for="description">Video Description</label>
            <textarea name="description" placeholder="Enter video description"></textarea>
        </div>

        <div class="form-group">
            <label for="video">Video File</label>
            <input type="file" name="video" accept="video/*" required>
        </div>

        <div class="form-group">
            <label for="thumbnail">Thumbnail Image</label>
            <input type="file" name="thumbnail" accept="image/*" required>
        </div>

        <div class="form-group">
            <label for="assigned_to">Assign To</label>
            <select name="assigned_to" id="assigned_to" onchange="toggleFields()" required>
                <option value="all">All Students</option>
                <option value="batch">Specific Batch</option>
                <option value="student">Specific Student</option>
            </select>
        </div>

        <!-- Batch Dropdown with Search -->
        <div id="batch_select" class="form-group hidden">
            <label>Select Batch</label>
            <input type="text" id="batchSearch" class="search-box" onkeyup="filterOptions('batchSearch','batchDropdown')" placeholder="Search batch...">
            <div class="dropdown-container">
                <select name="batch_id" id="batchDropdown">
                    <option value="">-- Select Batch --</option>
                    <?php while($b = $batches->fetch_assoc()) { ?>
                        <option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <!-- Student Dropdown with Search -->
        <div id="student_select" class="form-group hidden">
            <label>Select Student</label>
            <input type="text" id="studentSearch" class="search-box" onkeyup="filterOptions('studentSearch','studentDropdown')" placeholder="Search student...">
            <div class="dropdown-container">
                <select name="student_id" id="studentDropdown">
                    <option value="">-- Select Student --</option>
                    <?php while($s = $students->fetch_assoc()) { ?>
                        <option value="<?= $s['student_id'] ?>"><?= $s['name'] ?> (ID: <?= $s['student_id'] ?>)</option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <!-- Video Preview -->
        <div class="form-group full-width">
            <label>Video Preview</label>
            <video id="videoPreview" width="100%" controls class="hidden"></video>
        </div>

        <!-- Thumbnail Preview -->
        <div class="form-group full-width">
            <label>Thumbnail Preview</label>
            <img id="thumbPreview" src="" alt="Thumbnail Preview" style="max-width: 200px; display:none; border-radius:6px;">
        </div>

        <!-- Upload Progress -->
        <div class="form-group full-width">
            <label>Upload Progress</label>
            <div style="background:#e2e8f0; border-radius:4px; height:20px;">
                <div id="progressBar" style="width:0%; height:100%; background-color:#2563eb; border-radius:4px;"></div>
            </div>
        </div>

        <div class="form-group full-width">
            <button type="submit">Upload Video</button>
        </div>
    </form>
</div>

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

    // Video preview
    document.querySelector('input[name="video"]').addEventListener('change', function(e){
        const file = e.target.files[0];
        const videoPreview = document.getElementById('videoPreview');
        if(file){
            videoPreview.src = URL.createObjectURL(file);
            videoPreview.classList.remove('hidden');
        }
    });

    // Thumbnail preview
    document.querySelector('input[name="thumbnail"]').addEventListener('change', function(e){
        const file = e.target.files[0];
        const thumbPreview = document.getElementById('thumbPreview');
        if(file){
            thumbPreview.src = URL.createObjectURL(file);
            thumbPreview.style.display = 'block';
        }
    });

    // AJAX upload with progress
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e){
        e.preventDefault();

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        xhr.open('POST', form.action, true);

        xhr.upload.onprogress = function(event){
            if(event.lengthComputable){
                const percent = Math.round((event.loaded / event.total) * 100);
                document.getElementById('progressBar').style.width = percent + '%';
            }
        };

        xhr.onload = function(){
            if(xhr.status === 200){
                alert('Upload complete!');
                location.reload(); // reload to show uploaded video
            } else {
                alert('Upload failed.');
            }
        };

        xhr.send(formData);
    });

    document.addEventListener('DOMContentLoaded', toggleFields);
</script>
