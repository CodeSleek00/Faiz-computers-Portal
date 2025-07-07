<?php include '../database_connection/db_connect.php'; ?>

<?php
// Fetch batches and students
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --primary-dark: #3730a3;
            --text: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.5;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            padding: 1.5rem 2rem;
            background-color: var(--primary);
            color: white;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-container {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.9375rem;
            transition: border-color 0.2s;
            background-color: var(--card-bg);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            margin-bottom: 1rem;
        }

        .file-upload-input {
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            z-index: -1;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border: 2px dashed var(--border);
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            justify-content: center;
            flex-direction: column;
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .file-upload-icon {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .file-upload-text {
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .file-upload-text strong {
            color: var(--primary);
            font-weight: 500;
        }

        .selection-method {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .method-tab {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .method-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .method-content {
            display: none;
        }

        .method-content.active {
            display: block;
        }

        .student-option {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .student-option:last-child {
            border-bottom: none;
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 500;
            margin-bottom: 0.125rem;
        }

        .student-id {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            width: 100%;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .info-text {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .selection-method {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i> Create New Assignment
            </h1>
        </div>

        <div class="form-container">
            <form action="upload_assignment.php" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Assignment Title</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Enter assignment title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="question_text">Assignment Description</label>
                        <textarea id="question_text" name="question_text" class="form-control" placeholder="Provide detailed instructions for the assignment"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Supporting Files (Optional)</label>
                        <div class="file-upload">
                            <input type="file" id="question_image" name="question_image" class="file-upload-input" accept="image/*,.pdf,.doc,.docx">
                            <label for="question_image" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <span class="file-upload-text">
                                    <strong>Click to upload</strong> or drag and drop<br>
                                    Supports JPG, PNG, PDF, DOC (Max 5MB)
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="marks">Total Marks</label>
                        <input type="number" id="marks" name="marks" class="form-control" placeholder="Enter total marks" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-users"></i> Distribution Settings
                    </div>
                    
                    <div class="selection-method">
                        <div class="method-tab active" onclick="showMethod('all')">
                            <i class="fas fa-globe"></i> All Students
                        </div>
                        <div class="method-tab" onclick="showMethod('batch')">
                            <i class="fas fa-layer-group"></i> By Batch
                        </div>
                        <div class="method-tab" onclick="showMethod('student')">
                            <i class="fas fa-user-graduate"></i> Individual
                        </div>
                    </div>
                    
                    <div id="all-method" class="method-content active">
                        <div class="info-text">
                            This assignment will be visible to all students in the system.
                        </div>
                    </div>
                    
                    <div id="batch-method" class="method-content">
                        <div class="form-group">
                            <label for="batch_id">Select Batch</label>
                            <select id="batch_id" name="batch_id" class="form-control">
                                <option value="">-- Select a batch --</option>
                                <?php while ($row = $batches->fetch_assoc()) { ?>
                                    <option value="<?= $row['batch_id'] ?>">
                                        <?= htmlspecialchars($row['batch_name']) ?> • <?= $row['timing'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="info-text">
                                All students in the selected batch will receive this assignment
                            </div>
                        </div>
                    </div>
                    
                    <div id="student-method" class="method-content">
                        <div class="form-group">
                            <label for="student_ids">Select Students</label>
                            <select id="student_ids" name="student_ids[]" multiple class="form-control" style="padding: 0;">
                                <?php while ($s = $students->fetch_assoc()) { ?>
                                    <option value="<?= $s['student_id'] ?>">
                                        <div class="student-option">
                                            <div class="student-avatar"><?= strtoupper(substr($s['name'], 0, 2)) ?></div>
                                            <div class="student-info">
                                                <div class="student-name"><?= htmlspecialchars($s['name']) ?></div>
                                                <div class="student-id"><?= $s['enrollment_id'] ?></div>
                                            </div>
                                        </div>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="info-text">
                                Hold Ctrl/Cmd to select multiple students
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Assignment
                </button>
            </form>
        </div>
    </div>

    <script>
        function showMethod(method) {
            // Update tabs
            document.querySelectorAll('.method-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.method-tab[onclick="showMethod('${method}')"]`).classList.add('active');
            
            // Update content
            document.querySelectorAll('.method-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${method}-method`).classList.add('active');
        }

        // Handle file upload label change
        document.getElementById('question_image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file selected';
            document.querySelector('.file-upload-text').innerHTML = 
                `<strong>${fileName}</strong><br>Click to change file`;
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const method = document.querySelector('.method-tab.active').getAttribute('onclick').replace("showMethod('", "").replace("')", "");
            
            if (method === 'batch' && !document.getElementById('batch_id').value) {
                alert('Please select a batch');
                e.preventDefault();
            }
            
            if (method === 'student' && !document.getElementById('student_ids').selectedOptions.length) {
                alert('Please select at least one student');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>