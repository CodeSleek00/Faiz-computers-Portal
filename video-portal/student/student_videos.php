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
    SELECT v.*
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
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; }
    .card { background: var(--card); border-radius: var(--radius); padding: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); border: 1px solid var(--border); }
    .card h3 { margin: 0 0 8px; font-size: 18px; }
    .card p { margin: 0 0 12px; color: var(--muted); font-size: 13px; }
    video { width: 100%; border-radius: 10px; background: #000; }
    .meta { font-size: 12px; color: var(--muted); margin-top: 10px; }
    .empty { background: var(--card); padding: 24px; border-radius: var(--radius); text-align: center; border: 1px dashed var(--border); color: var(--muted); }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Welcome, <?= htmlspecialchars($student_name) ?></h1>
        <p>Here are the videos assigned to you.</p>
    </div>

    <?php if ($videos && $videos->num_rows > 0) { ?>
        <div class="grid">
            <?php while ($v = $videos->fetch_assoc()) { ?>
                <div class="card">
                    <h3><?= htmlspecialchars($v['title']) ?></h3>
                    <?php if (!empty($v['description'])) { ?>
                        <p><?= nl2br(htmlspecialchars($v['description'])) ?></p>
                    <?php } ?>
                    <video controls preload="metadata">
                        <source src="../../uploads/videos/<?= htmlspecialchars($v['file_name']) ?>" type="<?= htmlspecialchars($v['mime_type']) ?>">
                        Your browser does not support the video tag.
                    </video>
                    <div class="meta">Uploaded on <?= date('d M Y, h:i A', strtotime($v['uploaded_at'])) ?></div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="empty">No videos assigned yet.</div>
    <?php } ?>
</div>
</body>
</html>
