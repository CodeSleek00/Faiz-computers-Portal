<?php
include 'db_connect.php';
// TODO: student session check, set $student_id and $student_batch_id
$student_id = $_SESSION['student_id'] ?? 1; // replace with real
$student_batch_id = $_SESSION['batch_id'] ?? null;


// Fetch videos assigned to this student OR their batch OR global (no assignment)
$sql = "SELECT v.* FROM videos v
LEFT JOIN video_assignments va ON va.video_id = v.id
WHERE (va.student_id IS NULL AND va.batch_id IS NULL)
OR va.student_id = :sid
OR va.batch_id = :bid
GROUP BY v.id
ORDER BY v.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':sid'=>$student_id, ':bid'=>$student_batch_id]);
$videos = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Your Videos</title>
<link rel="stylesheet" href="styles.css">
</head>
<body class="page">
<div class="container">
<h1>Your Videos</h1>
<div class="grid">
<div class="left">
<ul id="playlist">
<?php foreach($videos as $v): ?>
<li class="playlist-item" data-id="<?=intval($v['id'])?>"><?=htmlspecialchars($v['title'])?></li>
<?php endforeach; ?>
</ul>
</div>
<div class="right">
<div class="player-card">
<h2 id="video-title">Select a video</h2>
<video id="video-player" controls style="width:100%;max-height:480px"></video>


<div id="quality-list"></div>
<div class="player-actions">
<button id="btn-forward">Forward 10s</button>
<a id="download-link" href="#" download>Download</a>
</div>
<p id="video-desc"></p>
</div>
</div>
</div>
</div>
<script src="video_player.js"></script>
</body>
</html>