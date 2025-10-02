<?php
include '../database_connection/db_connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $timing = $_POST['timing'];
    $students = $_POST['students'];

    // 1️⃣ Insert into batches table
    $stmt = $conn->prepare("INSERT INTO batches (batch_name, timing) VALUES (?, ?)");
    $stmt->bind_param("ss", $batch_name, $timing);
    $stmt->execute();
    $batch_id = $conn->insert_id;

    // 2️⃣ Assign students to batch (student_batches table)
    foreach ($students as $student_id) {
        $stmt2 = $conn->prepare("INSERT INTO student_batches (student_id, batch_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $student_id, $batch_id);
        $stmt2->execute();

        // 3️⃣ Update students table with batch_id
        $stmt3 = $conn->prepare("UPDATE students SET batch_id = ? WHERE student_id = ?");
        $stmt3->bind_param("ii", $batch_id, $student_id);
        $stmt3->execute();
    }

    header("Location: view_batch.php");
    exit;
}


$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$courses = $conn->query("SELECT DISTINCT course FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Batch</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text);
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background-color: var(--primary-light);
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
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
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .student-list-container {
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .student-list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: var(--primary-light);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .student-list-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .selected-count {
            background-color: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .student-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .student-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            transition: background-color 0.2s;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .student-item:hover {
            background-color: var(--primary-light);
        }

        .student-checkbox {
            margin-right: 1rem;
            width: 1.25rem;
            height: 1.25rem;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .student-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .student-id {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .student-course {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            width: 100%;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>

    <script>
        function filterStudents() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const course = document.getElementById('courseFilter').value.toLowerCase();
            const items = document.querySelectorAll('.student-item');
            let selectedCount = 0;

            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const enroll = item.dataset.enroll.toLowerCase();
                const courseVal = item.dataset.course.toLowerCase();

                const matchText = name.includes(search) || enroll.includes(search);
                const matchCourse = course === "" || courseVal === course;

                if (matchText && matchCourse) {
                    item.style.display = 'flex';
                    if (item.querySelector('.student-checkbox').checked) {
                        selectedCount++;
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            document.getElementById('selectedCount').textContent = selectedCount;
        }

        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkboxes.length;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
            
            // Initialize count
            updateSelectedCount();
        });
    </script>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Create New Batch</h1>
        <a href="view_batch.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Batches
        </a>
    </div>

    <form method="POST">
        <div class="card">
            <h2 class="card-title">Batch Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="batch_name">Batch Name</label>
                    <input type="text" id="batch_name" name="batch_name" class="form-control" placeholder="e.g. CS-2023-Batch-A" required>
                </div>
                <div class="form-group">
                    <label for="timing">Schedule</label>
                    <input type="text" id="timing" name="timing" class="form-control" placeholder="e.g. Mon-Fri 9:00 AM - 11:00 AM" required>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Add Students</h2>
            
            <div class="filters">
                <div class="filter-group">
                    <label for="searchInput">Search Students</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name or ID..." onkeyup="filterStudents()">
                </div>
                <div class="filter-group">
                    <label for="courseFilter">Filter by Course</label>
                    <select id="courseFilter" class="form-control" onchange="filterStudents()">
                        <option value="">All Courses</option>
                        <?php while ($course = $courses->fetch_assoc()) { ?>
                            <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="student-list-container">
                <div class="student-list-header">
                    <h3>Available Students</h3>
                    <span id="selectedCount" class="selected-count">0</span>
                </div>
                
                <div class="student-list">
                    <?php if ($students->num_rows > 0): ?>
                        <?php while ($row = $students->fetch_assoc()) { ?>
                            <div class="student-item" 
                                 data-name="<?= htmlspecialchars($row['name']) ?>" 
                                 data-enroll="<?= htmlspecialchars($row['enrollment_id']) ?>" 
                                 data-course="<?= htmlspecialchars($row['course']) ?>">
                                <input type="checkbox" 
                                       name="students[]" 
                                       value="<?= $row['student_id'] ?>" 
                                       class="student-checkbox">
                                <div class="student-info">
                                    <div class="student-name"><?= htmlspecialchars($row['name']) ?></div>
                                    <div class="student-meta">
                                        <span class="student-id">
                                            <i class="fas fa-id-card"></i>
                                            <?= htmlspecialchars($row['enrollment_id']) ?>
                                        </span>
                                        <span class="student-course">
                                            <i class="fas fa-book"></i>
                                            <?= htmlspecialchars($row['course']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <p>No students found. Please add students first.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Create Batch
        </button>
    </form>
</div>

</body>
</html>