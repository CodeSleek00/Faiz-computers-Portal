<?php
include '../database_connection/db_connect.php';

// Fetch videos and assignments
$videos = $conn->query("SELECT * FROM videos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Videos</title>
</head>
<body>
<h2>All Videos & Assignments</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Assigned To</th>
        <th>Actions</th>
    </tr>
    <?php while($v = $videos->fetch_assoc()) { ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td><?= htmlspecialchars($v['title']) ?></td>
            <td>
                <?php
                $vid = $v['id'];
                $assigned = $conn->query("
                    SELECT vt.*, b.batch_name, s.name AS student_name 
                    FROM video_targets vt
                    LEFT JOIN batches b ON vt.batch_id=b.batch_id
                    LEFT JOIN students s ON vt.student_id=s.student_id
                    WHERE vt.video_id=$vid
                ");
                $list = [];
                while($a = $assigned->fetch_assoc()) {
                    if($a['batch_id']) $list[] = "Batch: ".$a['batch_name'];
                    if($a['student_id']) $list[] = "Student: ".$a['student_name'];
                }
                echo implode(', ', $list);
                ?>
            </td>
            <td>
                <a href="video_reassign.php">Reassign</a>
            </td>
        </tr>
    <?php } ?>
</table>
</body>
</html>
