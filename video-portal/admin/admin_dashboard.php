<?php
include "../includes/db_connect.php";

$result = $conn->query("SELECT * FROM videos ORDER BY upload_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h1>Admin Video Dashboard</h1>
<a href="upload_video.php">Upload New Video</a>
<table border="1">
<tr><th>ID</th><th>Title</th><th>Uploaded On</th><th>Actions</th></tr>
<?php while($row = $result->fetch_assoc()) { ?>
<tr>
<td><?= $row['video_id'] ?></td>
<td><?= $row['title'] ?></td>
<td><?= $row['upload_date'] ?></td>
<td>
    <a href="edit_video.php?id=<?= $row['video_id'] ?>">Edit</a> |
    <a href="manage_videos.php?action=delete&id=<?= $row['video_id'] ?>" onclick="return confirm('Delete video?')">Delete</a>
</td>
</tr>
<?php } ?>
</table>
</body>
</html>
