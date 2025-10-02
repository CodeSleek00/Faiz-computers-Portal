<?php
session_start();
include '../database_connection/db_connect.php';

$student_id = $_SESSION['student_id'] ?? 0;
if (!$student_id) {
    header("Location: login.php");
    exit;
}

$video_id = intval($_GET['id'] ?? 0);
if (!$video_id) {
    die("Invalid video ID.");
}

// Check if student has access
$batch = $conn->query("SELECT batch_id FROM student_batches WHERE student_id = $student_id LIMIT 1")->fetch_assoc();
$batch_id = $batch['batch_id'] ?? 0;

$sql = "SELECT v.* FROM videos v
        LEFT JOIN video_targets vt ON v.id = vt.video_id
        WHERE v.id = $video_id AND (vt.student_id = $student_id OR vt.batch_id = $batch_id)
        LIMIT 1";
$video = $conn->query($sql)->fetch_assoc();

if (!$video) {
    die("You do not have access to this video.");
}

$video_path = '../uploads/videos/' . $video['file_name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($video['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Poppins',sans-serif; background:#f0f2f5; margin:0; padding:20px; display:flex; flex-direction:column; align-items:center; }
        h2 { margin-bottom:20px; }
        video { width:100%; max-width:800px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
        a.back { margin-top:20px; text-decoration:none; color:#4361ee; font-weight:600; }
    </style>
</head>
<body>
<h2><?= htmlspecialchars($video['title']) ?></h2>
<video controls>
    <source src="<?= htmlspecialchars($video_path) ?>" type="video/mp4">
    Your browser does not support the video tag.
</video>
<a class="back" href="student_videos.php">&larr; Back to Videos</a>
</body>
</html>
