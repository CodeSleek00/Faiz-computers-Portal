<?php
include 'db_connect.php';

$title = $_POST['title'];
$description = $_POST['description'];
$assigned_to = $_POST['assigned_to'];
$batch_id = $_POST['batch_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;

// Video Upload
$videoFile = $_FILES['video'];
$videoName = time() . "_" . basename($videoFile['name']);
$videoPath = "uploads/videos/" . $videoName;
move_uploaded_file($videoFile['tmp_name'], $videoPath);

// Thumbnail Upload
$thumbFile = $_FILES['thumbnail'];
$thumbName = time() . "_" . basename($thumbFile['name']);
$thumbPath = "uploads/thumbnails/" . $thumbName;
move_uploaded_file($thumbFile['tmp_name'], $thumbPath);

// Insert into DB
$stmt = $conn->prepare("INSERT INTO videos (title, description, filename, thumbnail, assigned_to, batch_id, student_id, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssis", $title, $description, $videoName, $thumbName, $assigned_to, $batch_id, $student_id);
$stmt->execute();

header("Location: admin_video.php?success=1");
exit;
?>
