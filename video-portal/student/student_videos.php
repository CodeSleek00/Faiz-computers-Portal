<?php
include "../includes/db_connect.php";
include "../includes/session_check.php";

$student_id = $_SESSION['student_id'];

// Fetch assigned videos
$sql = "SELECT v.*
FROM videos v
JOIN video_assignments va ON v.video_id = va.video_id
LEFT JOIN batches b ON va.batch_id = b.batch_id
WHERE va.student_id = ? OR b.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = ?)
ORDER BY v.upload_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$student_id,$student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>My Videos</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h1>Assigned Videos</h1>
<div class="video-grid">
<?php while($v = $result->fetch_assoc()){ ?>
<div class="video-card">
<h3><?= $v['title'] ?></h3>
<video width="100%" controls>
<source src="../videos/<?= $v['filename'] ?>" type="video/mp4">
Your browser does not support HTML5 video.
</video>
<p><?= $v['description'] ?></p>
<p>Uploaded on: <?= $v['upload_date'] ?></p>
</div>
<?php } ?>
</div>
</body>
</html>
