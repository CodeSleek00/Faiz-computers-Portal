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

$stmt = $conn->prepare("
    SELECT v.id, v.title, v.description, v.file_name, v.mime_type, v.uploaded_at
    FROM videos v
    INNER JOIN video_assignments a ON a.video_id = v.id
    WHERE a.student_id = ? AND a.student_table = ?
    ORDER BY v.uploaded_at DESC
");
$stmt->bind_param('is', $student_id, $studentTable);
$stmt->execute();
$videos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Videos</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #1d4ed8;
        --bg: #f4f6fb;
        --card: #ffffff;
        --text: #1f2937;
        --muted: #6b7280;
        --border: #e5e7eb;
        --radius: 12px;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; padding: 24px; color: var(--text); }
    .container { max-width: 1100px; margin: 0 auto; }
    .header { margin-bottom: 20px; }
    .header h1 { margin: 0 0 6px; font-size: 24px; }
    .header p { color: var(--muted); margin: 0; }
    .list { display: grid; gap: 14px; }
    .video-card {
        background: var(--card);
        border-radius: var(--radius);
        padding: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border: 1px solid var(--border);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 16px;
        align-items: center;
    }
    .video-title { margin: 0 0 8px; font-size: 18px; font-weight: 600; }
    .video-desc { margin: 0; color: var(--muted); font-size: 13px; }
    .meta { font-size: 12px; color: var(--muted); margin-top: 10px; }
    .play-btn {
        background: var(--primary);
        color: #fff;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 13px;
        display: inline-block;
        white-space: nowrap;
    }
    .play-btn:hover { background: #1e40af; }
    .empty { background: var(--card); padding: 24px; border-radius: var(--radius); text-align: center; border: 1px dashed var(--border); color: var(--muted); }
    @media (max-width: 640px) {
        .video-card { grid-template-columns: 1fr; }
        .play-btn { width: 100%; text-align: center; }
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Welcome, <?= htmlspecialchars($student_name) ?></h1>
        <p>Here are the videos assigned to you.</p>
    </div>

    <?php if ($videos && $videos->num_rows > 0) { ?>
        <div class="list">
            <?php while ($v = $videos->fetch_assoc()) { ?>
                <div class="video-card">
                    <div>
                        <div class="video-title"><?= htmlspecialchars($v['title']) ?></div>
                        <?php if (!empty($v['description'])) { ?>
                            <div class="video-desc"><?= nl2br(htmlspecialchars($v['description'])) ?></div>
                        <?php } else { ?>
                            <div class="video-desc">No description provided.</div>
                        <?php } ?>
                        <div class="meta">Uploaded on <?= date('d M Y', strtotime($v['uploaded_at'])) ?></div>
                    </div>
                    <div>
                        <a class="play-btn" href="play_video.php?id=<?= (int) $v['id'] ?>">Play Video</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="empty">No videos assigned yet.</div>
    <?php } ?>
</div>
</body>
</html>
