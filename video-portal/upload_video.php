<?php
include '../database_connection/db_connect.php';

$errors = [];
$success = '';

$manual_dir = __DIR__ . '/../uploads/videos/hostinger_uploads';
if (!is_dir($manual_dir)) {
    mkdir($manual_dir, 0777, true);
}

$allowed_ext = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

// Fetch students safely
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

// List files from manual upload folder
$files = [];
if (is_dir($manual_dir)) {
    $dir_items = scandir($manual_dir);
    foreach ($dir_items as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext, true)) {
            $files[] = $file;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $students = $_POST['students'] ?? [];
    $selected_file = basename($_POST['existing_file'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if ($selected_file === '') {
        $errors[] = 'Please select a video file from the folder.';
    }

    $file_path = $manual_dir . '/' . $selected_file;
    if ($selected_file !== '' && !file_exists($file_path)) {
        $errors[] = 'Selected file not found. Please upload to hostinger folder first.';
    }

    $ext = strtolower(pathinfo($selected_file, PATHINFO_EXTENSION));
    if ($selected_file !== '' && !in_array($ext, $allowed_ext, true)) {
        $errors[] = 'Invalid file type.';
    }

    if (empty($errors)) {
        $mime_type = mime_content_type($file_path);
        $file_size = filesize($file_path);

        $stmt = $conn->prepare("INSERT INTO videos (title, description, file_name, mime_type, file_size, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssssi', $title, $description, $selected_file, $mime_type, $file_size);
        if (!$stmt->execute()) {
            $errors[] = 'Failed to save video details: ' . $stmt->error;
        } else {
            $video_id = $stmt->insert_id;
            $stmt->close();

            if (!empty($students)) {
                $stmt2 = $conn->prepare("INSERT IGNORE INTO video_assignments (video_id, student_id, student_table) VALUES (?, ?, ?)");
                foreach ($students as $student_value) {
                    if (strpos($student_value, ':') === false) continue;
                    list($table, $student_id) = explode(':', $student_value);
                    $student_id = (int) $student_id;
                    $table = trim($table);
                    if ($student_id > 0 && $table !== '') {
                        $stmt2->bind_param('iis', $video_id, $student_id, $table);
                        $stmt2->execute();
                    }
                }
                $stmt2->close();
            }

            header('Location: view_videos_admin.php?msg=uploaded');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Existing Video</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1e40af;
        --bg: #f4f7fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #6b7280;
        --border: #e5e7eb;
        --success: #10b981;
        --danger: #ef4444;
        --radius: 12px;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; padding: 24px; color: var(--text); }
    .container { max-width: 1000px; margin: 0 auto; background: var(--card); padding: 28px; border-radius: var(--radius); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }
    h1 { margin: 0 0 12px; font-size: 24px; }
    .note { color: var(--muted); font-size: 13px; margin-bottom: 18px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    label { font-weight: 600; margin-bottom: 8px; display: block; }
    input[type="text"], textarea, select { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); }
    textarea { min-height: 90px; resize: vertical; }
    .student-list { max-height: 360px; overflow-y: auto; border: 1px solid var(--border); border-radius: 10px; background: #fafafa; padding: 10px; }
    .student-item { display: flex; align-items: center; padding: 8px 6px; border-bottom: 1px solid #eee; gap: 10px; }
    .student-photo { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    .actions { margin-top: 20px; }
    button { width: 100%; padding: 14px; background: var(--primary); color: white; font-weight: 600; border: none; border-radius: 10px; cursor: pointer; }
    button:hover { background: var(--primary-dark); }
    .message { padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; }
    .message.error { background: #fee2e2; color: #991b1b; }
    .filters { display: grid; gap: 10px; }
    .selected-count { font-size: 13px; color: var(--muted); margin-top: 8px; }
    @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
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
    <h1>Add Existing Video</h1>
    <div class="note">Upload videos directly in Hostinger File Manager to: <strong>/uploads/videos/hostinger_uploads</strong></div>

    <?php if (!empty($errors)) { ?>
        <div class="message error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php } ?>

    <form method="POST">
        <div class="grid">
            <div>
                <label>Video Title</label>
                <input type="text" name="title" required>

                <label>Description</label>
                <textarea name="description" placeholder="Optional"></textarea>

                <label>Select File from Folder</label>
                <select name="existing_file" required>
                    <option value="">-- Select File --</option>
                    <?php foreach ($files as $file) { ?>
                        <option value="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($file) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label>Assign Students</label>
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
            </div>
        </div>

        <div class="actions">
            <button type="submit">Save & Assign Video</button>
        </div>
    </form>
</div>
</body>
</html>
