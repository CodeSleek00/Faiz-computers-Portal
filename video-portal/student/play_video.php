<?php
include '../../database_connection/db_connect.php';
session_start();

if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'])) {
    die('Please login.');
}

$enrollment   = $_SESSION['enrollment_id'];
$studentTable = $_SESSION['student_table'];
$idColumn = ($studentTable === 'students') ? 'student_id' : 'id';

$stmt = $conn->prepare("SELECT $idColumn AS student_id, name FROM $studentTable WHERE enrollment_id = ? LIMIT 1");
$stmt->bind_param('s', $enrollment);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) {
    die('Student not found.');
}

$student_id = (int) $student['student_id'];
$student_name = $student['name'];

$video_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($video_id <= 0) {
    die('Invalid video.');
}

$stmt = $conn->prepare("
    SELECT v.id, v.title, v.description, v.file_name, v.mime_type, v.uploaded_at
    FROM videos v
    INNER JOIN video_assignments a ON a.video_id = v.id
    WHERE v.id = ? AND a.student_id = ? AND a.student_table = ?
    LIMIT 1
");
$stmt->bind_param('iis', $video_id, $student_id, $studentTable);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();
if (!$video) {
    die('Video not assigned.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Play Video</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #1d4ed8;
        --bg: #0f172a;
        --card: #111827;
        --text: #f8fafc;
        --muted: #9ca3af;
        --accent: #38bdf8;
        --radius: 14px;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; padding: 24px; color: var(--text); min-height: 100vh; }
    .container { max-width: 980px; margin: 0 auto; }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .back-btn { color: var(--text); text-decoration: none; background: #1f2937; padding: 8px 14px; border-radius: 999px; font-size: 13px; border: 1px solid #374151; }
    .back-btn:hover { border-color: var(--accent); }
    .title { font-size: 22px; margin: 0 0 6px; }
    .desc { color: var(--muted); font-size: 13px; margin: 0 0 14px; }
    .player-card { background: var(--card); border-radius: var(--radius); padding: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.35); border: 1px solid #1f2937; }
    video { width: 100%; border-radius: 10px; background: #000; }
    .meta { margin-top: 10px; color: var(--muted); font-size: 12px; }
    @media (max-width: 640px) {
        body { padding: 16px; }
        .topbar { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <a class="back-btn" href="student_videos.php">Back to Videos</a>
        <div class="meta">Logged in as <?= htmlspecialchars($student_name) ?></div>
    </div>

    <div class="player-card">
        <h1 class="title"><?= htmlspecialchars($video['title']) ?></h1>
        <?php if (!empty($video['description'])) { ?>
            <p class="desc"><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        <?php } ?>

        <video controls preload="metadata">
            <source src="../../uploads/videos/<?= htmlspecialchars($video['file_name']) ?>" type="<?= htmlspecialchars($video['mime_type']) ?>">
            Your browser does not support the video tag.
        </video>
        <div class="meta">Uploaded on <?= date('d M Y, h:i A', strtotime($video['uploaded_at'])) ?></div>
    </div>
</div>
</body>
</html>
