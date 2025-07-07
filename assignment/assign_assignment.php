<?php
include '../database_connection/db_connect.php';

// Fetch all assignments, batches, and students
$assignments = $conn->query("SELECT * FROM assignments ORDER BY created_at DESC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Assignment</title>
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
            --bg: #f9fafb;
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

        select.form-control[multiple] {
            height: auto;
            min-height: 150px;
            padding: 0.5rem;
        }

        .student-option {
            padding: 0.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .student-option:last-child {
            border-bottom: none;
        }

        .student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 0.75rem;
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
                <i class="fas fa-share-square"></i> Distribute Assignment
            </h1>
        </div>

        <div class="form-container">
            <form action="assignment_data.php" method="POST">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-file-alt"></i> Assignment Details
                    </div>
                    
                    <div class="form-group">
                        <label for="assignment_id">Select Assignment</label>
                        <select name="assignment_id" class="form-control" required>
                            <option value="">-- Select an assignment --</option>
                            <?php while($a = $assignments->fetch_assoc()) { ?>
                                <option value="<?= $a['assignment_id'] ?>">
                                    <?= htmlspecialchars($a['title']) ?> (Due: <?= date('M d, Y', strtotime($a['due_date'])) ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-users"></i> Distribution Method
                    </div>
                    
                    <div class="selection-method">
                        <div class="method-tab active" onclick="showMethod('batch')">
                            <i class="fas fa-layer-group"></i> By Batch
                        </div>
                        <div class="method-tab" onclick="showMethod('individual')">
                            <i class="fas fa-user-graduate"></i> Individual Students
                        </div>
                    </div>
                    
                    <div id="batch-method" class="method-content active">
                        <div class="form-group">
                            <label for="batch_id">Select Batch</label>
                            <select name="batch_id" class="form-control">
                                <option value="">-- Select a batch --</option>
                                <?php $batches->data_seek(0); while($b = $batches->fetch_assoc()) { ?>
                                    <option value="<?= $b['batch_id'] ?>">
                                        <?= htmlspecialchars($b['batch_name']) ?> • <?= $b['timing'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="info-text">All students in the selected batch will receive this assignment</p>
                        </div>
                    </div>
                    
                    <div id="individual-method" class="method-content">
                        <div class="form-group">
                            <label for="students[]">Select Students</label>
                            <select name="students[]" multiple class="form-control">
                                <?php $students->data_seek(0); while($s = $students->fetch_assoc()) { ?>
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
                            <p class="info-text">Hold Ctrl/Cmd to select multiple students</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Assign to Selected Students
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

        // Prevent form submission if neither batch nor students are selected
        document.querySelector('form').addEventListener('submit', function(e) {
            const batchSelected = document.querySelector('select[name="batch_id"]').value;
            const studentsSelected = document.querySelector('select[name="students[]"]').selectedOptions.length > 0;
            
            if (!batchSelected && !studentsSelected) {
                alert('Please select either a batch or individual students');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>