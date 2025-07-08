<?php
include '../database_connection/db_connect.php';
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Study Material</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a56d4;
            --text: #2b2d42;
            --light: #f8f9fa;
            --border: #dee2e6;
            --radius: 10px;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .upload-container {
            max-width: 700px;
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--text);
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .required:after {
            content: " *";
            color: #e63946;
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .file-upload {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-upload-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            background: var(--light);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            border-color: var(--primary);
        }
        
        .file-upload-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .file-upload-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--primary);
            font-weight: 500;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            margin-top: 25px;
        }
        
        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .target-option {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--light);
            display: none;
        }
        
        .target-option.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .upload-container {
                padding: 25px;
            }
            
            h2 {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
                background: white;
            }
            
            .upload-container {
                padding: 20px;
                box-shadow: none;
                border-radius: 0;
            }
            
            h2 {
                font-size: 1.4rem;
            }
            
            .file-upload-label {
                padding: 20px;
            }
            
            .btn {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h2>Upload Study Material</h2>
        <form action="study_material_data.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title" class="required">Title</label>
                <input type="text" id="title" name="title" required placeholder="Enter material title">
            </div>
            
            <div class="form-group file-upload">
                <label class="required">Upload PDF</label>
                <div class="file-upload-label">
                    <span class="file-upload-icon">📁</span>
                    <span class="file-upload-text">Click to browse or drag & drop your PDF file</span>
                    <span class="file-name" id="file-name-display">No file selected</span>
                    <input type="file" class="file-upload-input" name="pdf" accept=".pdf" required id="pdf-upload" onchange="displayFileName(this)">
                </div>
            </div>
            
            <div class="form-group">
                <label for="assign_type" class="required">Assign To</label>
                <select id="assign_type" name="assign_type" onchange="toggleTarget(this.value)" required>
                    <option value="">-- Select Assignment Type --</option>
                    <option value="all">All Students</option>
                    <option value="batch">Specific Batch</option>
                    <option value="student">Specific Student</option>
                </select>
            </div>
            
            <div id="batchOption" class="target-option">
                <label for="batch_id">Select Batch</label>
                <select id="batch_id" name="batch_id">
                    <option value="">-- Select Batch --</option>
                    <?php while ($row = $batches->fetch_assoc()): ?>
                        <option value="<?= $row['batch_id'] ?>">
                            <?= htmlspecialchars($row['batch_name']) ?> (<?= htmlspecialchars($row['timing']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div id="studentOption" class="target-option">
                <label for="student_id">Select Student</label>
                <select id="student_id" name="student_id">
                    <option value="">-- Select Student --</option>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <option value="<?= $row['student_id'] ?>">
                            <?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['enrollment_id']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" name="upload" class="btn">Upload Material</button>
        </form>
    </div>

    <script>
        function toggleTarget(val) {
            document.getElementById('batchOption').classList.remove('active');
            document.getElementById('studentOption').classList.remove('active');
            
            if (val === 'batch') {
                document.getElementById('batchOption').classList.add('active');
            } else if (val === 'student') {
                document.getElementById('studentOption').classList.add('active');
            }
        }
        
        function displayFileName(input) {
            const fileNameDisplay = document.getElementById('file-name-display');
            if (input.files.length > 0) {
                fileNameDisplay.textContent = input.files[0].name;
                fileNameDisplay.style.color = '#4361ee';
            } else {
                fileNameDisplay.textContent = 'No file selected';
                fileNameDisplay.style.color = '#6c757d';
            }
        }
        
        // Handle drag and drop
        const fileUploadLabel = document.querySelector('.file-upload-label');
        
        fileUploadLabel.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadLabel.style.borderColor = '#4361ee';
            fileUploadLabel.style.backgroundColor = '#f0f2ff';
        });
        
        fileUploadLabel.addEventListener('dragleave', () => {
            fileUploadLabel.style.borderColor = '#dee2e6';
            fileUploadLabel.style.backgroundColor = '#f8f9fa';
        });
        
        fileUploadLabel.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadLabel.style.borderColor = '#dee2e6';
            fileUploadLabel.style.backgroundColor = '#f8f9fa';
            
            const fileInput = document.getElementById('pdf-upload');
            fileInput.files = e.dataTransfer.files;
            displayFileName(fileInput);
        });
    </script>
</body>
</html>