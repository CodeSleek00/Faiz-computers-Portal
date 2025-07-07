<?php
include '../database_connection/db_connect.php';

// Fetch all assignments, batches, and students
$assignments = $conn->query("SELECT * FROM assignments ORDER BY created_at DESC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Assignment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            position: relative;
            overflow: hidden;
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
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        select, input[type="text"], input[type="search"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: var(--transition);
            background-color: white;
        }

        select:focus, input[type="text"]:focus, input[type="search"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        select[multiple] {
            height: auto;
            min-height: 180px;
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

        .btn i {
            margin-right: 8px;
        }

        .search-container {
            position: relative;
            margin-bottom: 15px;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dark-gray);
        }

        .search-container input {
            padding-left: 40px !important;
        }

        .student-list-container {
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            max-height: 300px;
            overflow-y: auto;
            margin-top: 10px;
        }

        .student-item {
            padding: 12px 15px;
            border-bottom: 1px solid var(--medium-gray);
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .student-item:hover {
            background-color: var(--light-gray);
        }

        .student-item.selected {
            background-color: #e3f2fd;
        }

        .student-item input[type="checkbox"] {
            margin-right: 12px;
        }

        .student-info {
            flex-grow: 1;
        }

        .student-name {
            font-weight: 500;
        }

        .student-id {
            font-size: 13px;
            color: var(--dark-gray);
        }

        .assignment-type-selector {
            display: flex;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 1px solid var(--medium-gray);
        }

        .assignment-type-selector button {
            flex: 1;
            padding: 12px;
            background: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            margin: 0;
            border-radius: 0;
        }

        .assignment-type-selector button.active {
            background: var(--primary-color);
            color: white;
        }

        .assignment-type-selector button:first-child {
            border-right: 1px solid var(--medium-gray);
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            select, input[type="text"], input[type="search"] {
                padding: 12px 14px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px 15px;
            }
            
            .btn {
                padding: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-tasks"></i> Assign Assignment</h2>
    
    <form action="assignment_data.php" method="POST" id="assignmentForm">
        <div class="form-group">
            <label for="assignment_id"><i class="fas fa-book"></i> Select Assignment</label>
            <select name="assignment_id" id="assignment_id" required>
                <option value="">-- Select Assignment --</option>
                <?php while($a = $assignments->fetch_assoc()) { ?>
                    <option value="<?= $a['assignment_id'] ?>"><?= htmlspecialchars($a['title']) ?> (Due: <?= date('M d, Y', strtotime($a['due_date'])) ?>)</option>
                <?php } ?>
            </select>
        </div>
        
        <div class="assignment-type-selector">
            <button type="button" class="active" id="batchBtn"><i class="fas fa-users"></i> Assign to Batch</button>
            <button type="button" id="studentBtn"><i class="fas fa-user-graduate"></i> Assign to Students</button>
        </div>
        
        <div id="batchSection">
            <div class="form-group">
                <label for="batch_id"><i class="fas fa-layer-group"></i> Select Batch</label>
                <select name="batch_id" id="batch_id">
                    <option value="">-- Select Batch --</option>
                    <?php $batches->data_seek(0); while($b = $batches->fetch_assoc()) { ?>
                        <option value="<?= $b['batch_id'] ?>"><?= htmlspecialchars($b['batch_name']) ?> (<?= $b['timing'] ?>)</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        
        <div id="studentSection" style="display: none;">
            <div class="form-group">
                <label><i class="fas fa-search"></i> Search Students</label>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="search" id="studentSearch" placeholder="Search by name or enrollment ID...">
                </div>
                
                <div class="student-list-container" id="studentList">
                    <?php $students->data_seek(0); while($s = $students->fetch_assoc()) { ?>
                        <div class="student-item">
                            <input type="checkbox" name="students[]" value="<?= $s['student_id'] ?>" id="student_<?= $s['student_id'] ?>">
                            <div class="student-info">
                                <div class="student-name"><?= htmlspecialchars($s['name']) ?></div>
                                <div class="student-id"><?= $s['enrollment_id'] ?></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Assign Assignment</button>
    </form>
</div>

<script>
    // Toggle between batch and student assignment
    const batchBtn = document.getElementById('batchBtn');
    const studentBtn = document.getElementById('studentBtn');
    const batchSection = document.getElementById('batchSection');
    const studentSection = document.getElementById('studentSection');
    
    batchBtn.addEventListener('click', () => {
        batchBtn.classList.add('active');
        studentBtn.classList.remove('active');
        batchSection.style.display = 'block';
        studentSection.style.display = 'none';
        document.getElementById('batch_id').required = true;
    });
    
    studentBtn.addEventListener('click', () => {
        studentBtn.classList.add('active');
        batchBtn.classList.remove('active');
        studentSection.style.display = 'block';
        batchSection.style.display = 'none';
        document.getElementById('batch_id').required = false;
    });
    
    // Student search functionality
    const studentSearch = document.getElementById('studentSearch');
    const studentItems = document.querySelectorAll('.student-item');
    
    studentSearch.addEventListener('input', () => {
        const searchTerm = studentSearch.value.toLowerCase();
        
        studentItems.forEach(item => {
            const studentName = item.querySelector('.student-name').textContent.toLowerCase();
            const studentId = item.querySelector('.student-id').textContent.toLowerCase();
            
            if (studentName.includes(searchTerm) || studentId.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Make student items clickable
    studentItems.forEach(item => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        
        item.addEventListener('click', (e) => {
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            }
        });
    });
    
    // Form validation
    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
        const assignmentType = batchBtn.classList.contains('active') ? 'batch' : 'student';
        
        if (assignmentType === 'batch' && !document.getElementById('batch_id').value) {
            e.preventDefault();
            alert('Please select a batch to assign to');
            return;
        }
        
        if (assignmentType === 'student') {
            const checkedStudents = document.querySelectorAll('input[name="students[]"]:checked');
            if (checkedStudents.length === 0) {
                e.preventDefault();
                alert('Please select at least one student');
                return;
            }
        }
    });
</script>

</body>
</html>