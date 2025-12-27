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
            // Split table and ID
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
$students1 = $conn->query("SELECT student_id, name, enrollment_id, course, 'students' AS student_table FROM students ORDER BY name ASC");
$students2 = $conn->query("SELECT id AS student_id, name, enrollment_id, course, 'students26' AS student_table FROM students26 ORDER BY name ASC");

// Merge students into one array for display
$all_students = [];
while ($row = $students1->fetch_assoc()) $all_students[] = $row;
while ($row = $students2->fetch_assoc()) $all_students[] = $row;

// Fetch courses (for filtering)
$courses = $conn->query("SELECT DISTINCT course FROM students UNION SELECT DISTINCT course FROM students26");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Batch</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Your previous styling goes here */
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
    <h1>Create New Batch</h1>
    <form method="POST">
        <label>Batch Name:</label>
        <input type="text" name="batch_name" required>
        <label>Timing:</label>
        <input type="text" name="timing" required>

        <label>Filter Students:</label>
        <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Search by name or enrollment ID">
        <select id="courseFilter" onchange="filterStudents()">
            <option value="">All Courses</option>
            <?php while ($course = $courses->fetch_assoc()) { ?>
                <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
            <?php } ?>
        </select>

        <div>
            <strong>Available Students (<span id="selectedCount">0</span> selected)</strong>
            <div style="max-height:400px; overflow-y:auto;">
                <?php foreach ($all_students as $student) { ?>
                    <div class="student-item" data-name="<?= htmlspecialchars($student['name']) ?>"
                         data-enroll="<?= htmlspecialchars($student['enrollment_id']) ?>"
                         data-course="<?= htmlspecialchars($student['course']) ?>">
                        <input type="checkbox" name="students[]" class="student-checkbox" 
                               value="<?= $student['student_table'] . ':' . $student['student_id'] ?>">
                        <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['enrollment_id']) ?> - <?= htmlspecialchars($student['course']) ?>)
                    </div>
                <?php } ?>
            </div>
        </div>

        <button type="submit">Create Batch</button>
    </form>
</body>
</html>
