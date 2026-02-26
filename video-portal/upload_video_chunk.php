<?php
include '../database_connection/db_connect.php';

header('Content-Type: application/json');
set_time_limit(0);

$errors = [];

$upload_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['upload_id'] ?? '');
$chunk_index = isset($_POST['chunk_index']) ? (int) $_POST['chunk_index'] : -1;
$total_chunks = isset($_POST['total_chunks']) ? (int) $_POST['total_chunks'] : 0;

if ($upload_id === '' || $chunk_index < 0 || $total_chunks <= 0) {
    $errors[] = 'Invalid upload metadata.';
}

if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Chunk upload failed.';
}

if (!empty($errors)) {
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$chunk_dir = __DIR__ . '/../uploads/video_chunks/' . $upload_id;
if (!is_dir($chunk_dir)) {
    mkdir($chunk_dir, 0777, true);
}

$chunk_path = $chunk_dir . '/chunk_' . $chunk_index;

if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path)) {
    echo json_encode(['ok' => false, 'errors' => ['Failed to save chunk.']]);
    exit;
}

echo json_encode(['ok' => true]);
