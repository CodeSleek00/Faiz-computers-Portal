<?php
include '../database_connection/db_connect.php';

$errors = [];
$success = '';

$videos = $conn->query("SELECT id, title, uploaded_at FROM videos ORDER BY uploaded_at DESC");

// Fetch students
$students1 = $conn->query("SELECT student_id, name, enrollment_id, course, photo, 'students' AS student_table FROM students ORDER BY name ASC");
$students2 = $conn->query("SELECT id AS student_id, name, enrollment_id, course, photo, 'students26' AS student_table FROM students26 ORDER BY name ASC");

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

$courses = $conn->query("
    SELECT course FROM students
    UNION
    SELECT course FROM students26
");

$prefill_video_id = isset($_GET['video_id']) ? (int) $_GET['video_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_id = (int) ($_POST['video_id'] ?? 0);
    $students = $_POST['students'] ?? [];

    if ($video_id <= 0) {
        $errors[] = 'Please select a video.';
    }

    if (empty($students)) {
        $errors[] = 'Please select at least one student.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO video_assignments (video_id, student_id, student_table) VALUES (?, ?, ?)");
        foreach ($students as $student_value) {
            if (strpos($student_value, ':') === false) continue;
            list($table, $student_id) = explode(':', $student_value);
            $student_id = (int) $student_id;
            $table = trim($table);
            if ($student_id > 0 && $table !== '') {
                $stmt->bind_param('iis', $video_id, $student_id, $table);
                $stmt->execute();
            }
        }
        $stmt->close();

        $success = 'Video assigned to selected students.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Video</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #0ea5e9;
        --primary-dark: #0369a1;
        --bg: #f4f7fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #6b7280;
        --border: #e5e7eb;
        --success: #16a34a;
        --danger: #dc2626;
        --radius: 12px;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; padding: 24px; color: var(--text); }
    .container { max-width: 1000px; margin: 0 auto; background: var(--card); padding: 28px; border-radius: var(--radius); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }
    h1 { margin: 0 0 20px; font-size: 24px; }
    label { font-weight: 600; margin-bottom: 8px; display: block; }
    select, input[type="text"] { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); }
    .student-list { max-height: 360px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; background: #fafafa; padding: 10px; }
    .student-item { display: flex; align-items: center; padding: 8px 6px; border-bottom: 1px solid #eee; gap: 10px; }
    .student-photo { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    button { width: 100%; padding: 14px; background: var(--primary); color: white; font-weight: 600; border: none; border-radius: 10px; cursor: pointer; }
    button:hover { background: var(--primary-dark); }
    .message { padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; }
    .message.error { background: #fee2e2; color: #991b1b; }
    .message.success { background: #dcfce7; color: #166534; }
    .filters { display: grid; gap: 10px; margin-top: 16px; }
    .selected-count { font-size: 13px; color: var(--muted); margin-top: 8px; }
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
    <h1>Assign Video to Students</h1>

    <?php if (!empty($errors)) { ?>
        <div class="message error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php } ?>

    <?php if ($success) { ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <form method="POST">
        <label>Select Video</label>
        <select name="video_id" required>
            <option value="">-- Choose Video --</option>
            <?php if ($videos) { while ($v = $videos->fetch_assoc()) { ?>
                <option value="<?= $v['id'] ?>" <?= $prefill_video_id === (int) $v['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v['title']) ?> (<?= date('d M Y', strtotime($v['uploaded_at'])) ?>)
                </option>
            <?php } } ?>
        </select>

        <div class="filters">
            <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Search by name or enrollment ID">
            <select id="courseFilter" onchange="filterStudents()">
                <option value="">All Courses</option>
                <?php if ($courses) { while ($course = $courses->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($course['course']) ?>"><?= htmlspecialchars($course['course']) ?></option>
                <?php } } ?>
            </select>
        </div>

        <div class="selected-count">Selected Students: <span id="selectedCount">0</span></div>

        <div class="student-list">
            <?php foreach ($all_students as $student) { ?>
                <div class="student-item"
                     data-name="<?= htmlspecialchars($student['name']) ?>"
                     data-enroll="<?= htmlspecialchars($student['enrollment_id']) ?>"
                     data-course="<?= htmlspecialchars($student['course']) ?>">

                    <?php
                    $photoPath = __DIR__ . "/../uploads/" . $student['photo'];
                    if (!empty($student['photo']) && file_exists($photoPath)) { ?>
                        <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" class="student-photo">
                    <?php } else { ?>
                        <img src="https://via.placeholder.com/36" class="student-photo">
                    <?php } ?>

                    <input type="checkbox"
                           name="students[]"
                           class="student-checkbox"
                           value="<?= $student['student_table'] . ':' . $student['student_id'] ?>">

                    <?= htmlspecialchars($student['name']) ?>
                    (<?= htmlspecialchars($student['enrollment_id']) ?> - <?= htmlspecialchars($student['course']) ?>)
                </div>
            <?php } ?>
        </div>

        <div style="margin-top:16px;">
            <button type="submit">Assign Video</button>
        </div>
    </form>
</div>
</body>
</html>
