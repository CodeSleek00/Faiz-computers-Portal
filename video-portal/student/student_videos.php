<?php
include "../includes/db_connect.php";
include "../includes/session_check.php";

$student_id = $_SESSION['student_id'];
$student_table = $_SESSION['student_table']; // 'students' or 'students26'

// Fetch assigned videos
$sql = "SELECT v.*
FROM videos v
JOIN video_assignments va ON v.video_id = va.video_id
LEFT JOIN student_batches sb ON va.batch_id = sb.batch_id
    AND sb.student_id=? AND sb.student_table=?
WHERE (va.student_id=? AND va.student_table=?)
   OR (va.batch_id IS NOT NULL AND sb.id IS NOT NULL)
ORDER BY v.upload_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isis",$student_id,$student_table,$student_id,$student_table);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="video-grid">
<?php while($v = $result->fetch_assoc()){ ?>
<div class="video-card">
<h3><?= $v['title'] ?></h3>
<video width="100%" controls>
<source src="../videos/<?= $v['filename'] ?>" type="video/mp4">
</video>
<p><?= $v['description'] ?></p>
<p>Uploaded on: <?= $v['upload_date'] ?></p>
</div>
<?php } ?>
</div>
