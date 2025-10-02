<?php
session_start();
include '../database_connection/db_connect.php';

$student_id = $_SESSION['student_id'] ?? 0;
if (!$student_id) {
    header("Location: login.php");
    exit;
}

// Get student's batch
$batch = $conn->query("SELECT batch_id FROM student_batches WHERE student_id = $student_id LIMIT 1")->fetch_assoc();
$batch_id = $batch['batch_id'] ?? 0;

// Fetch assigned videos
$sql = "SELECT DISTINCT v.id, v.title, v.file_name 
        FROM videos v
        LEFT JOIN video_targets vt ON v.id = vt.video_id
        WHERE vt.student_id = $student_id OR vt.batch_id = $batch_id
        ORDER BY v.id DESC";

$videos = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Videos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background:#f0f2f5; margin:0; padding:20px; }
        h2 { text-align:center; margin-bottom:20px; }
        .video-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:15px; }
        .video-card { background:white; padding:15px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); text-align:center; }
        .video-card a { text-decoration:none; color:#4361ee; font-weight:600; display:block; margin-top:10px; }
    </style>
</head>
<body>
<h2>My Videos</h2>
<div class="video-grid">
    <?php if ($videos->num_rows > 0): ?>
        <?php while($v = $videos->fetch_assoc()): ?>
            <div class="video-card">
    <?php if($v['thumbnail'] && file_exists('../uploads/thumbnails/'.$v['thumbnail'])): ?>
        <img src="../uploads/thumbnails/<?= $v['thumbnail'] ?>" alt="<?= htmlspecialchars($v['title']) ?>" style="width:100%; height:150px; object-fit:cover; border-radius:6px;">
    <?php else: ?>
        <div style="width:100%; height:150px; background:#ddd; display:flex; align-items:center; justify-content:center; border-radius:6px;">
            No Thumbnail
        </div>
    <?php endif; ?>
    <strong><?= htmlspecialchars($v['title']) ?></strong>
    <a href="play_video.php?id=<?= $v['id'] ?>">Play Video</a>
</div>

        <?php endwhile; ?>
    <?php else: ?>
        <p style="grid-column:1/-1; text-align:center;">No videos assigned yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
