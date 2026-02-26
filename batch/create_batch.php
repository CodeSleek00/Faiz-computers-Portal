<?php
include '../database_connection/db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $batch_name = trim($_POST['batch_name']);
    $timing     = trim($_POST['timing']);
    $students   = $_POST['students'] ?? [];

    if (empty($batch_name) || empty($timing)) {
        die("Batch name and timing are required.");
    }

    // Start Transaction
    $conn->begin_transaction();

    try {

        // Insert Batch
        $stmt = $conn->prepare("INSERT INTO batches (batch_name, timing) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $batch_name, $timing);
        if (!$stmt->execute()) {
            throw new Exception("Batch insert failed: " . $stmt->error);
        }

        $batch_id = $conn->insert_id;
        $stmt->close();

        // Insert Students
        if (!empty($students)) {

            $stmt2 = $conn->prepare("INSERT INTO student_batches (student_id, batch_id, student_table) VALUES (?, ?, ?)");
            if (!$stmt2) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            foreach ($students as $student_value) {

                if (strpos($student_value, ":") === false) continue;

                list($table, $student_id) = explode(":", $student_value);

                $student_id = intval($student_id);
                $table      = trim($table);

                if ($student_id > 0 && !empty($table)) {

                    $stmt2->bind_param("iis", $student_id, $batch_id, $table);

                    if (!$stmt2->execute()) {
                        throw new Exception("Student insert failed: " . $stmt2->error);
                    }
                }
            }

            $stmt2->close();
        }

        // Commit if all successful
        $conn->commit();

        header("Location: view_batch.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Transaction Failed: " . $e->getMessage());
    }
}

// Fetch students safely
$students1 = $conn->query("SELECT student_id, name, enrollment_id, course, photo, 'students' AS student_table FROM students ORDER BY name ASC");
$students2 = $conn->query("SELECT id AS student_id, name, enrollment_id, course, photo, 'students26' AS student_table FROM students26 ORDER BY name ASC");

// Merge
$all_students = [];

if ($students1) {
    while ($row = $students1->fetch_assoc()) {
        $all_students[] = $row;
    }
}

if ($students2) {
    while ($row = $students2->fetch_assoc()) {
        $all_students[] = $row;
    }
}

// Fetch Courses Safely
$courses = $conn->query("
    SELECT course FROM students
    UNION
    SELECT course FROM students26
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Batch</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

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
h1 { text-align: center; margin-bottom: 30px; }
input, select {
    padding: 12px;
    border-radius: 10px;
    width: 100%;
    border: 1px solid #ccc;
    margin-bottom: 15px;
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
}
.student-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
}
button {
    width: 100%;
    padding: 14px;
    background: #007bff;
    color: white;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
}
button:hover { background: #0056b3; }
</style>

<script>
function filterStudents() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const course = document.getElementById('courseFilter').value.toLowerCase();
    const items = document.querySelectorAll('.student-item');

    items.forEach(item => {
        const name = item.dataset.name.toLowerCase();
        const enroll = item.dataset.enroll.toLowerCase();
        const courseVal = item.dataset.course.toLowerCase();

        const matchText = name.includes(search) || enroll.includes(search);
        const matchCourse = course === "" || courseVal === course;

        item.style.display = (matchText && matchCourse) ? 'flex' : 'none';
    });

    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.student-checkbox:checked');
    document.getElementById('selectedCount').textContent = checked.length;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.student-checkbox').forEach(cb =>
        cb.addEventListener('change', updateSelectedCount)
    );
});
</script>

</head>
<body>

<div class="container">
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
<?php if($courses): ?>
<?php while ($course = $courses->fetch_assoc()): ?>
<option value="<?= htmlspecialchars($course['course']) ?>">
<?= htmlspecialchars($course['course']) ?>
</option>
<?php endwhile; ?>
<?php endif; ?>
</select>

<div>Selected Students: <span id="selectedCount">0</span></div>

<div class="student-list">
<?php foreach ($all_students as $student): ?>

<div class="student-item"
     data-name="<?= htmlspecialchars($student['name']) ?>"
     data-enroll="<?= htmlspecialchars($student['enrollment_id']) ?>"
     data-course="<?= htmlspecialchars($student['course']) ?>">

<?php
$photoPath = __DIR__ . "/../uploads/" . $student['photo'];
if (!empty($student['photo']) && file_exists($photoPath)):
?>
<img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" class="student-photo">
<?php else: ?>
<img src="https://via.placeholder.com/40" class="student-photo">
<?php endif; ?>

<input type="checkbox"
       name="students[]"
       class="student-checkbox"
       value="<?= $student['student_table'] . ':' . $student['student_id'] ?>">

<?= htmlspecialchars($student['name']) ?>
(<?= htmlspecialchars($student['enrollment_id']) ?> -
<?= htmlspecialchars($student['course']) ?>)

</div>

<?php endforeach; ?>
</div>

<button type="submit">Create Batch</button>

</form>
</div>

</body>
</html>