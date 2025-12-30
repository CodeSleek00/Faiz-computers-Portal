<?php
include '../../database_connection/db_connect.php';

// Fetch Sections
$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");

// Fetch independent videos
$videos = $conn->query("SELECT * FROM videos WHERE section_id IS NULL ORDER BY upload_date DESC");

// Fetch Batches
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Video Portal Admin Dashboard</title>
    <style>
        body{font-family: Arial; background:#f4f4f4; margin:0; padding:0;}
        .container{width:95%; margin:auto; overflow:hidden;}
        h1{background:#4361ee; color:white; padding:15px 0; text-align:center;}
        .box{background:white; padding:15px; margin:15px 0; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
        .box h2{margin-top:0;}
        .button{display:inline-block; padding:8px 12px; background:#3f37c9; color:white; text-decoration:none; border-radius:5px; margin:5px 0;}
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <div class="container">
        <!-- Section Management -->
        <div class="box">
            <h2>Sections</h2>
            <a href="admin_sections.php" class="button">Create New Section</a>
            <h3>Existing Sections</h3>
            <?php while($sec=$sections->fetch_assoc()): ?>
                <div style="border-bottom:1px solid #ccc; padding:5px 0;">
                    <strong><?= htmlspecialchars($sec['title']) ?></strong> 
                    <a href="upload_video_section.php?section_id=<?= $sec['section_id'] ?>" class="button">Add Videos</a>
                    <a href="assign_video.php?section_id=<?= $sec['section_id'] ?>" class="button">Assign</a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Independent Video Upload -->
        <div class="box">
            <h2>Independent Videos</h2>
            <a href="upload_video_section.php" class="button">Upload Independent Video</a>
            <h3>Existing Independent Videos</h3>
            <?php while($v=$videos->fetch_assoc()): ?>
                <div style="border-bottom:1px solid #ccc; padding:5px 0;">
                    <strong><?= htmlspecialchars($v['title']) ?></strong>
                    <a href="assign_video.php?video_id=<?= $v['video_id'] ?>" class="button">Assign</a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Batch Info -->
        <div class="box">
            <h2>Batches</h2>
            <a href="../batch/admin_batches.php" class="button">Manage Batches</a>
            <h3>Existing Batches</h3>
            <?php while($b=$batches->fetch_assoc()): ?>
                <div style="border-bottom:1px solid #ccc; padding:5px 0;">
                    <?= htmlspecialchars($b['batch_name']) ?> (<?= htmlspecialchars($b['timing']) ?>)
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>