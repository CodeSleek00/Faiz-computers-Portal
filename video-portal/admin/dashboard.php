<?php
include '../database_connection/db_connect.php';
$videos = $conn->query("SELECT * FROM videos ORDER BY upload_date DESC");
?>

<h1>Admin Dashboard</h1>
<a href="upload_video.php">Upload Video</a>
<a href="assign_video.php">Assign Video</a>

<h2>Uploaded Videos</h2>
<ul>
<?php while($v=$videos->fetch_assoc()): ?>
<li><?= htmlspecialchars($v['title']) ?> - <a href="assign_video.php?video_id=<?= $v['video_id'] ?>">Assign</a></li>
<?php endwhile; ?>
</ul>
