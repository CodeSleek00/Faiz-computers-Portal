<?php
include '../database_connection/db_connect.php';

$errors = [];
$success = '';

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

// NOTE: Chunked upload is handled by upload_video_chunk.php and upload_video_finalize.php
// This page is now primarily for rendering the UI.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Video</title>
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
    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg);
        margin: 0;
        padding: 24px;
        color: var(--text);
    }
    .container {
        max-width: 1000px;
        margin: 0 auto;
        background: var(--card);
        padding: 28px;
        border-radius: var(--radius);
        box-shadow: 0 12px 24px rgba(0,0,0,0.06);
    }
    h1 { margin: 0 0 20px; font-size: 24px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    label { font-weight: 600; margin-bottom: 8px; display: block; }
    input[type="text"], textarea, select {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 1px solid var(--border);
    }
    textarea { min-height: 90px; resize: vertical; }
    .student-list {
        max-height: 360px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fafafa;
        padding: 10px;
    }
    .student-item {
        display: flex;
        align-items: center;
        padding: 8px 6px;
        border-bottom: 1px solid #eee;
        gap: 10px;
    }
    .student-photo {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }
    .actions { margin-top: 20px; }
    button {
        width: 100%;
        padding: 14px;
        background: var(--primary);
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        cursor: pointer;
    }
    button:hover { background: var(--primary-dark); }
    .progress-wrap {
        margin-top: 12px;
        background: #eef2ff;
        border-radius: 10px;
        overflow: hidden;
        height: 14px;
        display: none;
    }
    .progress-bar {
        height: 100%;
        width: 0%;
        background: var(--success);
        transition: width 0.2s ease;
    }
    .progress-text { font-size: 13px; color: var(--muted); margin-top: 6px; }
    .message { padding: 10px 12px; border-radius: 8px; margin-bottom: 16px; }
    .message.error { background: #fee2e2; color: #991b1b; }
    .message.success { background: #dcfce7; color: #166534; }
    .filters { display: grid; gap: 10px; }
    .selected-count { font-size: 13px; color: var(--muted); margin-top: 8px; }

    @media (max-width: 900px) {
        .grid { grid-template-columns: 1fr; }
    }
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

    const form = document.getElementById('uploadForm');
    const progressWrap = document.getElementById('progressWrap');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const errorBox = document.getElementById('errorBox');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        errorBox.innerHTML = '';
        errorBox.style.display = 'none';

        const fileInput = form.querySelector('input[name="video_file"]');
        const titleInput = form.querySelector('input[name="title"]');

        if (!titleInput.value.trim()) {
            errorBox.innerHTML = 'Title is required.';
            errorBox.style.display = 'block';
            return;
        }
        if (!fileInput.files || !fileInput.files.length) {
            errorBox.innerHTML = 'Video file is required.';
            errorBox.style.display = 'block';
            return;
        }

        const file = fileInput.files[0];
        const chunkSize = 8 * 1024 * 1024; // 8MB
        const totalChunks = Math.ceil(file.size / chunkSize);
        const uploadId = Date.now().toString(36) + Math.random().toString(36).slice(2);

        progressWrap.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = 'Starting upload...';

        let uploadedBytes = 0;

        const uploadChunk = (chunk, chunkIndex) => {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'upload_video_chunk.php', true);

                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        const currentLoaded = uploadedBytes + event.loaded;
                        const percent = Math.min(100, Math.round((currentLoaded / file.size) * 100));
                        progressBar.style.width = percent + '%';
                        progressText.textContent = 'Uploading: ' + percent + '%';
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.ok) {
                                uploadedBytes += chunk.size;
                                resolve();
                            } else {
                                reject(res.errors ? res.errors.join('<br>') : 'Chunk upload failed.');
                            }
                        } catch (err) {
                            reject('Unexpected response while uploading.');
                        }
                    } else {
                        reject('Chunk upload failed. Try again.');
                    }
                };

                xhr.onerror = function() {
                    reject('Network error during upload.');
                };

                const data = new FormData();
                data.append('upload_id', uploadId);
                data.append('chunk_index', chunkIndex);
                data.append('total_chunks', totalChunks);
                data.append('original_name', file.name);
                data.append('chunk', chunk);

                xhr.send(data);
            });
        };

        try {
            for (let i = 0; i < totalChunks; i++) {
                const start = i * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);
                await uploadChunk(chunk, i);
            }

            progressText.textContent = 'Finalizing upload...';

            const finalizeData = new FormData(form);
            finalizeData.append('upload_id', uploadId);
            finalizeData.append('original_name', file.name);
            finalizeData.append('total_chunks', totalChunks);
            finalizeData.append('file_size', file.size);
            finalizeData.append('mime_type', file.type || 'application/octet-stream');

            const finalizeXhr = new XMLHttpRequest();
            finalizeXhr.open('POST', 'upload_video_finalize.php', true);

            finalizeXhr.onload = function() {
                if (finalizeXhr.status === 200) {
                    try {
                        const res = JSON.parse(finalizeXhr.responseText);
                        if (res.ok) {
                            progressBar.style.width = '100%';
                            progressText.textContent = 'Upload complete! Redirecting...';
                            window.location.href = res.redirect;
                        } else {
                            progressText.textContent = 'Finalize failed.';
                            if (res.errors && res.errors.length) {
                                errorBox.innerHTML = res.errors.join('<br>');
                                errorBox.style.display = 'block';
                            }
                        }
                    } catch (err) {
                        progressText.textContent = 'Unexpected response.';
                    }
                } else {
                    progressText.textContent = 'Finalize failed. Try again.';
                }
            };

            finalizeXhr.onerror = function() {
                progressText.textContent = 'Network error on finalize.';
            };

            finalizeXhr.send(finalizeData);
        } catch (err) {
            progressText.textContent = 'Upload failed.';
            errorBox.innerHTML = err;
            errorBox.style.display = 'block';
        }
    });
});
</script>
</head>
<body>
<div class="container">
    <h1>Upload Video</h1>

    <?php if (!empty($errors)) { ?>
        <div class="message error" id="errorBox"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
    <?php } else { ?>
        <div class="message error" id="errorBox" style="display:none"></div>
    <?php } ?>

    <?php if ($success) { ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <form id="uploadForm" method="POST" enctype="multipart/form-data">
        <div class="grid">
            <div>
                <label>Video Title</label>
                <input type="text" name="title" required>

                <label>Description</label>
                <textarea name="description" placeholder="Optional"></textarea>

                <label>Choose Video File</label>
                <input type="file" name="video_file" accept="video/*" required>

                <div class="progress-wrap" id="progressWrap">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
                <div class="progress-text" id="progressText">Upload progress will appear here.</div>
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
            <button type="submit">Upload Video</button>
        </div>
    </form>
</div>
</body>
</html>
