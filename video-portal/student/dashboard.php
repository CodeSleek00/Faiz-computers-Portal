<?php
session_start();
include '../database_connection/db_connect.php';
if(!isset($_SESSION['enrollment_id'])){ header("Location: ../login/login.php"); exit; }

$student_id = $_SESSION['student_id'];
$student_table = $_SESSION['student_table'];

$stmt = $conn->prepare("SELECT DISTINCT v.* 
FROM videos v
JOIN video_assignments va ON va.video_id=v.video_id
LEFT JOIN student_batches sb ON va.batch_id=sb.batch_id AND sb.student_id=? AND sb.student_table=?
WHERE (va.student_id=? AND va.student_table=?) OR (va.batch_id IS NOT NULL AND sb.id IS NOT NULL)
ORDER BY v.upload_date DESC");
$stmt->bind_param("isis",$student_id,$student_table,$student_id,$student_table);
$stmt->execute();
$videos = $stmt->get_result();
?>

<h1>Welcome <?= htmlspecialchars($_SESSION['name']) ?></h1>

<?php while($v=$videos->fetch_assoc()): ?>
<div style="border:1px solid #ccc;padding:10px;margin:10px 0;">
<h2><?= htmlspecialchars($v['title']) ?></h2>
<p><?= htmlspecialchars($v['description']) ?></p>
<video controls width="600" src="../admin/videos/<?= htmlspecialchars($v['filename']) ?>"></video>
</div>
<?php endwhile; ?>
