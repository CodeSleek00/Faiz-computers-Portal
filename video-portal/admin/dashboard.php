<?php
include '../database_connection/db_connect.php';
$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");
$videos   = $conn->query("SELECT * FROM videos WHERE section_id IS NULL ORDER BY upload_date DESC");
?>

<h1>Admin Dashboard</h1>
<h2>Sections</h2>
<a href="admin_sections.php">Create Section</a>
<?php while($sec=$sections->fetch_assoc()): ?>
<p><?= htmlspecialchars($sec['title']) ?> 
<a href="upload_video.php?section_id=<?= $sec['section_id'] ?>">Add Videos</a>
<a href="assign_video.php?section_id=<?= $sec['section_id'] ?>">Assign</a></p>
<?php endwhile; ?>

<h2>Independent Videos</h2>
<a href="upload_video.php">Upload Video</a>
<?php while($v=$videos->fetch_assoc()): ?>
<p><?= htmlspecialchars($v['title']) ?> <a href="assign_video.php?video_id=<?= $v['video_id'] ?>">Assign</a></p>
<?php endwhile; ?>
