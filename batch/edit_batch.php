<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid batch ID.");
}

$batch_id = intval($_GET['id']);

// Fetch batch details
$batch_result = $conn->query("SELECT * FROM batches WHERE batch_id = $batch_id");
if ($batch_result->num_rows == 0) {
    die("❌ Batch not found.");
}
$batch = $batch_result->fetch_assoc();

// Fetch all students from both tables
$all_students = [];
$result1 = $conn->query("SELECT student_id AS id, name, enrollment_id, 'students' AS table_name FROM students");
$result2 = $conn->query("SELECT id, name, enrollment_id, 'students26' AS table_name FROM students26");

while ($row = $result1->fetch_assoc()) $all_students[] = $row;
while ($row = $result2->fetch_assoc()) $all_students[] = $row;

// Fetch assigned students
$assigned_students = [];
$assigned_result = $conn->query("SELECT student_id, student_table FROM student_batches WHERE batch_id = $batch_id");
while ($row = $assigned_result->fetch_assoc()) {
    $assigned_students[$row['student_table'] . '_' . $row['student_id']] = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = $_POST['batch_name'];
    $new_timing = $_POST['timing'];
    $selected_students = $_POST['students'] ?? []; // format: table_name_id

    // Update batch info
    $stmt = $conn->prepare("UPDATE batches SET batch_name = ?, timing = ? WHERE batch_id = ?");
    $stmt->bind_param("ssi", $new_name, $new_timing, $batch_id);
    $stmt->execute();

    // Delete existing student-batch links
    $conn->query("DELETE FROM student_batches WHERE batch_id = $batch_id");

    // Insert selected students with correct table
    $stmt = $conn->prepare("INSERT INTO student_batches (student_id, batch_id, student_table) VALUES (?, ?, ?)");
    foreach ($selected_students as $item) {
        list($table_name, $student_id) = explode('_', $item);
        $stmt->bind_param("iis", $student_id, $batch_id, $table_name);
        $stmt->execute();
    }

    header("Location: view_batch.php?id=$batch_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Batch</title>
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef1f5; margin: 0; padding: 40px 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 35px; border-radius: 16px; box-shadow: 0 12px 30px rgba(0,0,0,0.08);}
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        label { font-weight: 600; margin: 15px 0 5px; display: block; }
        input { padding: 12px; border-radius: 10px; width: 100%; border: 1px solid #ccc; margin-bottom: 15px; }
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-bar input { flex: 1; min-width: 200px; }
        .student-list { max-height: 320px; overflow-y: auto; border: 1px solid #ddd; border-radius: 10px; background: #fafafa; padding: 10px; }
        .student-item { display: flex; align-items: center; padding: 8px 5px; border-bottom: 1px solid #eee; transition: background 0.2s; }
        .student-item:hover { background-color: #f1f6ff; }
        .student-item:last-child { border-bottom: none; }
        .student-item input[type="checkbox"] { margin-right: 10px; transform: scale(1.2); cursor: pointer; }
        button { width: 100%; margin-top: 25px; padding: 14px; background: #007bff; color: white; font-weight: 600; font-size: 16px; border: none; border-radius: 10px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #0056b3; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
    <script>
        function filterStudents() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const students = document.querySelectorAll('.student-item');
            students.forEach(student => {
                const name = student.dataset.name.toLowerCase();
                const enroll = student.dataset.enroll.toLowerCase();
                student.style.display = (name.includes(searchInput) || enroll.includes(searchInput)) ? 'flex' : 'none';
            });
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Edit Batch: <?= htmlspecialchars($batch['batch_name']) ?></h2>

    <form method="POST">
        <label>Batch Name</label>
        <input type="text" name="batch_name" value="<?= htmlspecialchars($batch['batch_name']) ?>" required>

        <label>Timing</label>
        <input type="text" name="timing" value="<?= htmlspecialchars($batch['timing']) ?>" required>

        <label>Search Students</label>
        <div class="filter-bar">
            <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Search by name or enrollment ID...">
        </div>

        <div class="student-list">
            <?php foreach ($all_students as $student) {
                $key = $student['table_name'].'_'.$student['id'];
                $checked = isset($assigned_students[$key]) ? 'checked' : '';
            ?>
                <div class="student-item" data-name="<?= htmlspecialchars($student['name']) ?>" data-enroll="<?= htmlspecialchars($student['enrollment_id']) ?>">
                    <input type="checkbox" name="students[]" value="<?= $student['table_name'].'_'.$student['id'] ?>" <?= $checked ?>>
                    <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['enrollment_id']) ?>) - <?= $student['table_name'] ?>
                </div>
            <?php } ?>
        </div>

        <button type="submit">Update Batch</button>
    </form>

    <a class="back-link" href="view_batch.php?id=<?= $batch_id ?>">⬅ Back to Batch</a>
</div>

</body>
</html>
