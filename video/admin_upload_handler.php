<?php
include 'db_connect.php';
// TODO: admin session check


if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit('Invalid');


$title = trim($_POST['title'] ?? 'Untitled');
$desc = trim($_POST['description'] ?? '');
$batch_id = !empty($_POST['batch_id']) ? intval($_POST['batch_id']) : null;
$student_ids = $_POST['student_ids'] ?? [];


// insert video
$stmt = $pdo->prepare('INSERT INTO videos (title, description, created_by) VALUES (?, ?, ?)');
$stmt->execute([$title, $desc, 1]); // change created_by to admin id from session
$video_id = $pdo->lastInsertId();


$upload_dir = __DIR__ . '/uploads/videos/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);


if (!empty($_FILES['files'])){
foreach($_FILES['files']['name'] as $quality => $name){
if (empty($name)) continue;
$tmp = $_FILES['files']['tmp_name'][$quality];
$error = $_FILES['files']['error'][$quality];
if ($error !== UPLOAD_ERR_OK) continue;


// sanitize file name
$ext = pathinfo($name, PATHINFO_EXTENSION);
$fname = 'video_'.time().'_'.bin2hex(random_bytes(4))."_".$quality.'.'. $ext;
$target = $upload_dir.$fname;
if (move_uploaded_file($tmp, $target)){
$mime = mime_content_type($target);
$relative = 'uploads/videos/'.$fname;
$ins = $pdo->prepare('INSERT INTO video_files (video_id, quality, file_path, mime) VALUES (?, ?, ?, ?)');
$ins->execute([$video_id, $quality, $relative, $mime]);
}
}
}


// assignments
if ($batch_id){
$ins = $pdo->prepare('INSERT INTO video_assignments (video_id, batch_id) VALUES (?, ?)');
$ins->execute([$video_id, $batch_id]);
}
if (!empty($student_ids)){
$ins = $pdo->prepare('INSERT INTO video_assignments (video_id, student_id) VALUES (?, ?)');
foreach($student_ids as $sid){
$ins->execute([$video_id, intval($sid)]);
}
}


header('Location: video_admin.php?ok=1');
exit;