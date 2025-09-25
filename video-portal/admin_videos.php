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
            --border-radius: 10px;
            --shadow: 0 4px 6px rgba(0,0,0,0.05);
            --shadow-hover: 0 10px 15px rgba(0,0,0,0.08);
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
            line-height: 1.6;
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
            font-size: 2rem;
            padding-bottom: 15px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h1:before {
            content: "🎬";
            font-size: 1.8rem;
        }
        
        h2 {
            font-size: 1.5rem;
            margin-top: 40px;
            padding-left: 10px;
            border-left: 4px solid var(--primary-blue);
        }
        
        /* Form Styles */
        .form-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
            transition: box-shadow 0.3s ease;
        }
        
        .form-container:hover {
            box-shadow: var(--shadow-hover);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 0.95rem;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }
        
        button {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        button:before {
            content: "📤";
        }
        
        button:hover {
            background-color: var(--light-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
        }
        
        /* Hidden elements */
        .hidden {
            display: none;
        }
        
        /* Searchable dropdowns */
        .search-box {
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
        }
        
        .dropdown-container {
            position: relative;
        }
        
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }
        
        /* Video list */
        .video-list {
            display: grid;
            gap: 24px;
        }
        
        .video-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            gap: 18px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--medium-gray);
        }
        
        .video-card h3 {
            color: var(--primary-blue);
            font-size: 1.3rem;
            font-weight: 600;
            line-height: 1.4;
        }
        
        .video-card p {
            color: var(--dark-gray);
            line-height: 1.6;
        }
        
        .video-card video {
            max-width: 100%;
            border-radius: 6px;
            background: #000;
        }
        
        .video-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .video-actions a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s ease;
            border: 1px solid var(--medium-gray);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .video-actions a:first-child:before {
            content: "📥";
        }
        
        .video-actions a:last-child:before {
            content: "🗑️";
        }
        
        .video-actions a:hover {
            background-color: var(--light-gray);
            border-color: var(--primary-blue);
        }
        
        /* Preview elements */
        #videoPreview, #thumbPreview {
            border-radius: 6px;
            border: 1px solid var(--medium-gray);
        }
        
        #thumbPreview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
        }
        
        /* Progress bar */
        .progress-container {
            background: var(--medium-gray);
            border-radius: 10px;
            height: 12px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        #progressBar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-blue), var(--light-blue));
            border-radius: 10px;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--dark-gray);
        }
        
        .empty-state:before {
            content: "📹";
            font-size: 4rem;
            display: block;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Responsive */
        @media (min-width: 768px) {
            body {
                padding: 30px;
            }
            
            .form-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 24px;
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
                font-size: 1.6rem;
            }
            
            h2 {
                font-size: 1.3rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .video-card {
                padding: 20px;
            }
            
            .video-actions {
                flex-direction: column;
            }
            
            .video-actions a {
                text-align: center;
                justify-content: center;
            }
        }
    </style>
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
                <img id="thumbPreview" src="" alt="Thumbnail Preview" class="hidden">
            </div>

            <!-- Upload Progress -->
            <div class="form-group full-width">
                <label>Upload Progress</label>
                <div class="progress-container">
                    <div id="progressBar"></div>
                </div>
            </div>
            
            <div class="form-group full-width">
                <button type="submit">Upload Video</button>
            </div>
        </form>
    </div>

    <h2>Uploaded Videos</h2>
    <div class="video-list">
        <?php
        $result = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='video-card'>
                    <h3>{$row['title']}</h3>
                    <p>{$row['description']}</p>
                    <video width='100%' controls>
                        <source src='../uploads/videos/{$row['filename']}' type='video/mp4'>
                        Your browser does not support the video tag.
                    </video>
                    <div class='video-actions'>
                        <a href='../uploads/videos/{$row['filename']}' download>Download</a>
                        <a href='delete_video.php?id={$row['id']}'>Delete</a>
                    </div>
                </div>";
            }
        } else {
            echo "<div class='empty-state'>
                <p>No videos uploaded yet.</p>
                <p>Upload your first video using the form above.</p>
            </div>";
        }
        ?>
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
                thumbPreview.classList.remove('hidden');
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
        });
    </script>
</body>
</html>