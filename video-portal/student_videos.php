<?php
session_start();
include '../database_connection/db_connect.php';

// Assume student_id comes from login session
$student_id = $_SESSION['student_id'] ?? 0;

// Fetch all videos assigned to this student directly or via batch
$query = "
    SELECT v.id, v.title, v.file_name, v.thumbnail 
    FROM videos v
    LEFT JOIN video_targets vt ON v.id = vt.video_id
    LEFT JOIN student_batches sb ON vt.batch_id = sb.batch_id
    WHERE vt.student_id = ? OR sb.student_id = ?
    GROUP BY v.id
    ORDER BY v.id DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Videos</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        h2 { text-align: center; margin-bottom: 20px; }
        .video-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .video-card { background: white; border-radius: 8px; padding: 10px; width: 200px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .video-card img { width: 100%; height: auto; border-radius: 4px; }
        .video-card a { text-decoration: none; color: #333; font-weight: bold; display: block; margin-top: 8px; }
    </style>
</head>
<body>
<div class="container">
    <h2>My Videos</h2>
    <div class="video-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($video = $result->fetch_assoc()): ?>
                <?php
                $thumbnail = isset($video['thumbnail']) && !empty($video['thumbnail']) 
                             ? 'uploads/video_thumbnails/' . $video['thumbnail'] 
                             : 'uploads/video_thumbnails/default.png';
                ?>
                <div class="video-card">
                    <a href="video_player.php?id=<?= $video['id'] ?>">
                        <img src="<?= $thumbnail ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                        <?= htmlspecialchars($video['title']) ?>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No videos assigned to you yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
