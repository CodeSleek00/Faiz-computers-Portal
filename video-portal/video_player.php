<?php
session_start();
include '../database_connection/db_connect.php';

// Get video ID from URL
$video_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT title, file_name FROM videos WHERE id = ?");
$stmt->bind_param("i", $video_id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();

if (!$video) {
    die("Video not found!");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($video['title']) ?></title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; text-align: center; }
        h2 { margin-bottom: 20px; }
        video { width: 80%; max-width: 800px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        a { display:block; margin-top: 20px; text-decoration: none; color: #4361ee; font-weight: bold; }
    </style>
</head>
<body>
    <h2><?= htmlspecialchars($video['title']) ?></h2>
    <video controls>
        <source src="uploads/videos/<?= $video['file_name'] ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <a href="student_videos.php">← Back to My Videos</a>
</body>
</html>
