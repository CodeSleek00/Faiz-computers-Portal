<?php
session_start();
require_once '../database_connection/db_connect.php';

/* =====================================================
   1. LOGIN CHECK
===================================================== */
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table']; // students OR students26
$student_id    = (int) $_SESSION['student_id'];

/* =====================================================
   2. GET ASSIGNMENT DETAILS (SAFE)
===================================================== */
$assignment_id = $_GET['assignment_id'] ?? null;
if (!$assignment_id || !is_numeric($assignment_id)) {
    die("Assignment ID missing.");
}

$assignment_id = (int) $assignment_id;

$stmt = $conn->prepare("SELECT * FROM assignments WHERE assignment_id = ? LIMIT 1");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment_result = $stmt->get_result();

if ($assignment_result->num_rows === 0) {
    die("Assignment not found.");
}

$assignment = $assignment_result->fetch_assoc();

/* =====================================================
   3. CHECK IF ALREADY SUBMITTED (SAFE)
===================================================== */
$stmt = $conn->prepare("SELECT submission_id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1");
$stmt->bind_param("ii", $assignment_id, $student_id);
$stmt->execute();
$submitted_result = $stmt->get_result();

if ($submitted_result->num_rows > 0) {
    die("You have already submitted this assignment.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Assignment</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #28a745;
            --success-dark: #218838;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #6c757d;
            --text-color: #2b2d42;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .header {
            padding: 25px;
            background: var(--primary);
            color: white;
            text-align: center;
        }

        h2 {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .subheader {
            font-size: 14px;
            opacity: 0.9;
        }

        .assignment-card {
            padding: 25px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .assignment-title {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .assignment-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: var(--dark-gray);
        }

        .assignment-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .assignment-question {
            background: var(--light-gray);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
        }

        .question-image {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #ddd;
        }

        .form-container {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .file-upload {
            position: relative;
            margin-bottom: 20px;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: white;
            border: 2px dashed var(--medium-gray);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .file-upload-label:hover {
            border-color: var(--primary);
        }

        .file-upload-text {
            color: var(--dark-gray);
        }

        .file-upload-icon {
            color: var(--primary);
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            resize: vertical;
            min-height: 150px;
            transition: var(--transition);
        }

        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 15px;
            background: var(--success);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background: var(--success-dark);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .or-divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .or-divider::before,
        .or-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--medium-gray);
            margin: 0 10px;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .header {
                padding: 20px 15px;
            }

            .assignment-card,
            .form-container {
                padding: 20px;
            }

            .assignment-meta {
                flex-direction: column;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 15px;
            }

            h2 {
                font-size: 20px;
            }

            .assignment-card,
            .form-container {
                padding: 15px;
            }

            .btn {
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Submit Assignment</h2>
        <div class="subheader">Complete and submit your work</div>
    </div>

    <div class="assignment-card">
        <div class="assignment-title"><?= htmlspecialchars($assignment['title']) ?></div>
        <div class="assignment-meta">
            <span><i class="far fa-star"></i> <?= $assignment['marks'] ?> Marks</span>
        </div>
        
        <?php if ($assignment['question_text']): ?>
            <div class="assignment-question">
                <?= nl2br(htmlspecialchars($assignment['question_text'])) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($assignment['question_image']): ?>
            <img src="../uploads/assignments/<?= $assignment['question_image'] ?>" class="question-image">
        <?php endif; ?>
    </div>

    <div class="form-container">
        <form method="POST" action="upload_submission.php" enctype="multipart/form-data">
            <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
            
            <div class="form-group">
                <label>Upload Answer (PDF, DOC, JPG, PNG - Max 10MB)</label>
                <div class="file-upload">
                    <input type="file" name="submitted_file" id="fileInput" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <label for="fileInput" class="file-upload-label">
                        <span class="file-upload-text" id="fileName">Choose a file or drag and drop</span>
                        <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                    </label>
                </div>
            </div>

            <div class="or-divider">OR</div>

            <div class="form-group">
                <label>Write Your Answer</label>
                <textarea name="submitted_text" placeholder="Type your answer here..."></textarea>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-paper-plane"></i> Submit Assignment
            </button>
        </form>
    </div>
</div>

<script>
    // Show selected filename
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose a file or drag and drop';
        document.getElementById('fileName').textContent = fileName;
    });

    // Drag and drop functionality
    const fileUploadLabel = document.querySelector('.file-upload-label');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadLabel.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadLabel.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadLabel.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        fileUploadLabel.style.borderColor = 'var(--primary)';
        fileUploadLabel.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
    }

    function unhighlight() {
        fileUploadLabel.style.borderColor = 'var(--medium-gray)';
        fileUploadLabel.style.backgroundColor = 'white';
    }

    fileUploadLabel.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('fileInput').files = files;
        document.getElementById('fileName').textContent = files[0].name;
    }
</script>

</body>
</html>