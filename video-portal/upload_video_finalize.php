<?php
include '../database_connection/db_connect.php';

header('Content-Type: application/json');
set_time_limit(0);

$errors = [];

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$students = $_POST['students'] ?? [];

$upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['upload_id'] ?? '');
$original_name = $_POST['original_name'] ?? '';
$total_chunks = isset($_POST['total_chunks']) ? (int) $_POST['total_chunks'] : 0;
$file_size = isset($_POST['file_size']) ? (int) $_POST['file_size'] : 0;
$mime_type = trim($_POST['mime_type'] ?? 'application/octet-stream');

if ($title === '') {
    $errors[] = 'Title is required.';
}

if ($upload_id === '' || $total_chunks <= 0) {
    $errors[] = 'Invalid upload metadata.';
}

$allowed_ext = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
if ($ext === '' || !in_array($ext, $allowed_ext, true)) {
    $errors[] = 'Only MP4, WEBM, OGG, MOV, or M4V files are allowed.';
}

$chunk_dir = __DIR__ . '/../uploads/video_chunks/' . $upload_id;
if (!is_dir($chunk_dir)) {
    $errors[] = 'Upload chunks not found.';
}

if (!empty($errors)) {
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$upload_dir = __DIR__ . '/../uploads/videos';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$safe_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$final_path = $upload_dir . '/' . $safe_name;

$final = fopen($final_path, 'ab');
if (!$final) {
    echo json_encode(['ok' => false, 'errors' => ['Failed to write final file.']]);
    exit;
}

for ($i = 0; $i < $total_chunks; $i++) {
    $chunk_path = $chunk_dir . '/chunk_' . $i;
    if (!file_exists($chunk_path)) {
        fclose($final);
        echo json_encode(['ok' => false, 'errors' => ["Missing chunk $i."]]);
        exit;
    }

    $in = fopen($chunk_path, 'rb');
    if ($in === false) {
        fclose($final);
        echo json_encode(['ok' => false, 'errors' => ["Failed to read chunk $i."]]);
        exit;
    }

    while (!feof($in)) {
        $buffer = fread($in, 1048576); // 1MB
        if ($buffer === false) break;
        fwrite($final, $buffer);
    }
    fclose($in);
}

fclose($final);

// Cleanup chunks
for ($i = 0; $i < $total_chunks; $i++) {
    $chunk_path = $chunk_dir . '/chunk_' . $i;
    if (file_exists($chunk_path)) {
        unlink($chunk_path);
    }
}
@rmdir($chunk_dir);

$real_mime = mime_content_type($final_path);
$real_size = filesize($final_path);

$stmt = $conn->prepare("INSERT INTO videos (title, description, file_name, mime_type, file_size, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param('ssssi', $title, $description, $safe_name, $real_mime, $real_size);
if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'errors' => ['Failed to save video details: ' . $stmt->error]]);
    exit;
}
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

echo json_encode(['ok' => true, 'redirect' => 'view_videos_admin.php?msg=uploaded']);
