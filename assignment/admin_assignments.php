<?php 
include '../database_connection/db_connect.php';

// Fetch batches and students (from both students and students26 tables)
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("
    SELECT student_id, name, enrollment_id 
    FROM students
    UNION
    SELECT student_id, name, enrollment_id 
    FROM students26
    ORDER BY name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Assignment</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #4cc9f0;
            --text-color: #2b2d42;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --success-color: #4bb543;
            --error-color: #f44336;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-gray);
            padding: 20px;
        }

        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-box {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            margin-bottom: 30px;
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: var(--transition);
            background-color: white;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        select[multiple] {
            height: auto;
            min-height: 120px;
            padding: 10px;
        }

        option {
            padding: 10px;
            border-bottom: 1px solid var(--medium-gray);
        }

        option:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background-color: white;
            border: 2px solid var(--medium-gray);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
        }

        .file-input-text {
            color: var(--dark-gray);
        }

        .file-input-icon {
            color: var(--primary-color);
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .conditional-field {
            margin-top: 20px;
            padding: 20px;
            background-color: rgba(248, 249, 250, 0.5);
            border-radius: var(--border-radius);
            border: 1px dashed var(--medium-gray);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .text-muted {
            color: var(--dark-gray);
            font-size: 14px;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .form-box {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            input[type="text"],
            input[type="number"],
            textarea,
            select {
                padding: 12px 14px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 0 10px;
            }
            
            .form-box {
                padding: 25px 15px;
            }
            
            .btn {
                padding: 14px;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <div class="form-box">
        <h2>Create Assignment</h2>
        <form action="upload_assignment.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Assignment Title</label>
                <input type="text" id="title" name="title" required placeholder="Enter assignment title">
            </div>
            
            <div class="form-group">
                <label for="question_text">Assignment Question (Text)</label>
                <textarea id="question_text" name="question_text" rows="4" placeholder="Enter question text"></textarea>
            </div>
            
            <div class="form-group">
                <label>Assignment Question (Image - Optional)</label>
                <div class="file-input-wrapper">
                    <label class="file-input-label">
                        <span class="file-input-text">Choose an image file...</span>
                        <i class="fas fa-paperclip file-input-icon"></i>
                    </label>
                    <input type="file" name="question_image" accept="image/*">
                </div>
                <p class="text-muted">Supports JPG, PNG (Max 5MB)</p>
            </div>
            
            <div class="form-group">
                <label for="marks">Total Marks</label>
                <input type="number" id="marks" name="marks" required placeholder="Enter total marks">
            </div>
            
            <div class="form-group">
                <label for="target_type">Assign To</label>
                <select id="target_type" name="target_type" onchange="toggleTarget(this.value)" required>
                    <option value="all">All Students</option>
                    <option value="batch">Specific Batch</option>
                    <option value="student">Specific Students</option>
                </select>
            </div>
            
            <div id="batchSelect" class="conditional-field" style="display: none;">
                <div class="form-group">
                    <label for="batch_id">Select Batch</label>
                    <select id="batch_id" name="batch_id">
                        <option value="">-- Select Batch --</option>
                        <?php while ($row = $batches->fetch_assoc()) { ?>
                            <option value="<?= $row['batch_id'] ?>"><?= $row['batch_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            
            <div id="studentSelect" class="conditional-field" style="display: none;">
                <div class="form-group">
                    <label for="student_ids">Select Students</label>
                    <select id="student_ids" name="student_ids[]" multiple>
                        <?php while ($s = $students->fetch_assoc()) { ?>
                            <option value="<?= $s['student_id'] ?>"><?= $s['name'] ?> (<?= $s['enrollment_id'] ?>)</option>
                        <?php } ?>
                    </select>
                    <p class="text-muted">Hold Ctrl/Cmd to select multiple students</p>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-plus-circle"></i> Create Assignment
            </button>
        </form>
    </div>
</div>

<script>
function toggleTarget(value) {
    document.getElementById('batchSelect').style.display = (value === 'batch') ? 'block' : 'none';
    document.getElementById('studentSelect').style.display = (value === 'student') ? 'block' : 'none';
    
    // Reset values when hiding
    if (value !== 'batch') document.getElementById('batch_id').value = '';
    if (value !== 'student') {
        const studentSelect = document.getElementById('student_ids');
        Array.from(studentSelect.options).forEach(option => {
            option.selected = false;
        });
    }
}

// Enhance file input to show selected filename
document.querySelector('input[type="file"]').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose an image file...';
    document.querySelector('.file-input-text').textContent = fileName;
});
</script>

</body>
</html>