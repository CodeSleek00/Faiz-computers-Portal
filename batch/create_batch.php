<?php
include '../database_connection/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $timing = $_POST['timing'];
    $students = $_POST['students'];

    // Insert batch
    $stmt = $conn->prepare("INSERT INTO batches (batch_name, timing) VALUES (?, ?)");
    $stmt->bind_param("ss", $batch_name, $timing);
    $stmt->execute();
    $batch_id = $conn->insert_id;

    // Insert students into batch
    if (!empty($students)) {
        foreach ($students as $student_value) {
            list($table, $student_id) = explode(":", $student_value);
            $stmt2 = $conn->prepare("INSERT INTO student_batches (student_id, batch_id, student_table) VALUES (?, ?, ?)");
            $stmt2->bind_param("iis", $student_id, $batch_id, $table);
            $stmt2->execute();
        }
    }

    header("Location: view_batch.php");
    exit;
}

// Fetch students from both tables
$students1 = $conn->query("SELECT student_id, name, enrollment_id, course, photo, 'students' AS student_table FROM students ORDER BY name ASC");
$students2 = $conn->query("SELECT id AS student_id, name, enrollment_id, course, photo, 'students26' AS student_table FROM students26 ORDER BY name ASC");

// Merge students into one array
$all_students = [];
while ($row = $students1->fetch_assoc()) $all_students[] = $row;
while ($row = $students2->fetch_assoc()) $all_students[] = $row;

// Fetch courses
$courses = $conn->query("SELECT DISTINCT course FROM students UNION SELECT DISTINCT course FROM students26");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Batch</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        label {
            font-weight: 600;
            margin: 15px 0 5px;
            display: block;
        }

        input[type="text"], select {
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-bar input {
            flex: 1;
            min-width: 200px;
        }

        .student-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fafafa;
            padding: 10px;
        }

        .student-item {
            display: flex;
            align-items: center;
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }

        .student-item:hover {
            background-color: #f1f6ff;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .student-item input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
            cursor: pointer;
        }

        .student-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            background: #007bff;
            color: white;
            font-weight: 600;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .selected-count {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
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
                    if (item.querySelector('.student-checkbox').checked) selectedCount++;
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
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.addEventListener('change', updateSelectedCount));
            updateSelectedCount();
        });
    </script>
</head>
<body>
<div class="container">
    <h1>📚 Create New Batch</h1>

    <form method="POST">
        <label>Batch Name:</label>
        <input type="text" name="batch_name" required>

        <label>Timing:</label>
        <input type="text" name="timing" required>

        <label>Filter Students:</label>
        <div class="filter-bar">
            <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Search by name or enrollment ID">
            <select id="courseFilter" onchange="filterStudents()">
                <option value="">All Courses</option>
                <?php while ($course = $courses->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="selected-count">Selected Students: <span id="selectedCount">0</span></div>

        <div class="student-list">
            <?php foreach ($all_students as $student) { ?>
                <div class="student-item" data-name="<?= htmlspecialchars($student['name']) ?>"
                     data-enroll="<?= htmlspecialchars($student['enrollment_id']) ?>"
                     data-course="<?= htmlspecialchars($student['course']) ?>">
                    <?php if(!empty($student['photo']) && file_exists("../uploads/{$student['photo']}")) { ?>
                        <img src="../uploads/<?= $student['photo'] ?>" alt="Photo" class="student-photo">
                    <?php } else { ?>
                        <img src="https://via.placeholder.com/40" alt="No Photo" class="student-photo">
                    <?php } ?>
                    <input type="checkbox" name="students[]" class="student-checkbox" 
                           value="<?= $student['student_table'] . ':' . $student['student_id'] ?>">
                    <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['enrollment_id']) ?> - <?= htmlspecialchars($student['course']) ?>)
                </div>
            <?php } ?>
        </div>

        <button type="submit">Create Batch</button>
    </form>

    <a class="back-link" href="view_batch.php">⬅ Back to Batch List</a>
</div>
</body>
</html>
