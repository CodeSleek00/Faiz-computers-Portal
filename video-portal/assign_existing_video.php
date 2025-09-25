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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .container {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
        }
        
        h1 {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 2rem;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        
        h1:before {
            content: "📁";
            font-size: 1.8rem;
        }
        
        /* Form Styles */
        .form-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
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
            min-height: 100px;
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
            width: 100%;
            justify-content: center;
            margin-top: 10px;
        }
        
        button:before {
            content: "✅";
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
        
        /* File count indicator */
        .file-count {
            background: var(--light-blue);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        /* Back button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.2s;
            align-self: flex-start;
        }
        
        .back-btn:hover {
            background-color: var(--light-gray);
        }
        
        .back-btn:before {
            content: "←";
        }
        
        /* Optional label */
        .optional {
            color: var(--dark-gray);
            font-size: 0.85rem;
            font-weight: normal;
            margin-left: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.6rem;
            }
        }
        
        @media (max-width: 480px) {
            .form-container {
                padding: 15px;
            }
            
            input, textarea, select {
                padding: 12px;
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
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleFields();
            
            // Add file count to the label
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
        <a href="admin.php" class="back-btn">Back to Admin Portal</a>
        
        <h1>Assign Existing Video</h1>
        
        <div class="form-container">
            <form action="reassign_video.php" method="post" enctype="multipart/form-data">
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

                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select name="assigned_to" id="assigned_to" onchange="toggleFields()" required>
                        <option value="all">All Students</option>
                        <option value="batch">Specific Batch</option>
                        <option value="student">Specific Student</option>
                    </select>
                </div>

                <!-- Batch Dropdown -->
                <div id="batch_select" class="form-group hidden">
                    <label for="batch_id">Select Batch</label>
                    <select name="batch_id">
                        <option value="">-- Select Batch --</option>
                        <?php while($b = $batches->fetch_assoc()) { ?>
                            <option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Student Dropdown -->
                <div id="student_select" class="form-group hidden">
                    <label for="student_id">Select Student</label>
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
    </div>
</body>
</html>