<?php
session_start();
include 'db_connect.php';

$student_id = $_SESSION['student_id'] ?? 1;
$batch_id   = $_SESSION['batch_id'] ?? 1;

$video_id = $_GET['id'] ?? 0;

// Fetch video by ID (ensure it's assigned to this student)
$query = "
    SELECT * FROM videos 
    WHERE id = ? AND (
        assigned_to = 'all' 
        OR (assigned_to = 'batch' AND batch_id = ?) 
        OR (assigned_to = 'student' AND student_id = ?)
    )
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $video_id, $batch_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Watch Video</title>
    <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <style>
        body { font-family: Arial, sans-serif; background:#f7f7f7; margin:0; padding:0; }
        .container { width:90%; max-width:800px; margin:30px auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        h2 { margin-bottom:10px; }
        video { width:100%; border-radius:8px; margin-top:15px; }
        .desc { color:#555; margin-top:10px; }
        .download-btn { display:inline-block; padding:10px 15px; background:#007BFF; color:#fff; text-decoration:none; border-radius:5px; margin-top:15px; }
        .download-btn:hover { background:#0056b3; }
        .back-link { display:inline-block; margin-top:15px; text-decoration:none; color:#333; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($video) { ?>
            <h2><?= htmlspecialchars($video['title']) ?></h2>
            <div class="desc"><?= nl2br(htmlspecialchars($video['description'])) ?></div>
            <video controls>
                <source src="../uploads/videos/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
                Your browser does not support video playback.
            </video>
            <br>
            <a class="download-btn" href="../uploads/videos/<?= htmlspecialchars($video['filename']) ?>" download>⬇ Download</a>
            <br><a class="back-link" href="student_videos.php">⬅ Back to Videos</a>
        <?php } else { ?>
            <p style="text-align:center; color:red;">Video not found or not assigned to you.</p>
            <a class="back-link" href="student_videos.php">⬅ Back to Videos</a>
        <?php } ?>
    </div>
</body>
</html>
