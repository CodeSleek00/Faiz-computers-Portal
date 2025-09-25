<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Video Portal</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .video-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 8px; }
        input, textarea, select { width: 100%; padding: 8px; margin: 6px 0; }
        button { padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Upload Video</h2>
    <form action="upload_video.php" method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Video Title" required>
        <textarea name="description" placeholder="Video Description"></textarea>
        <input type="file" name="video" accept="video/*" required>
        <select name="assigned_to" required>
            <option value="all">All Students</option>
            <option value="batch">Specific Batch</option>
            <option value="student">Specific Student</option>
        </select>
        <input type="text" name="batch_id" placeholder="Batch ID (if batch selected)">
        <input type="text" name="student_id" placeholder="Student ID (if student selected)">
        <button type="submit">Upload</button>
    </form>

    <h2>Uploaded Videos</h2>
    <?php
    $result = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");
    while($row = $result->fetch_assoc()) {
        echo "<div class='video-box'>
            <h3>{$row['title']}</h3>
            <p>{$row['description']}</p>
            <video width='300' controls>
                <source src='uploads/videos/{$row['filename']}' type='video/mp4'>
            </video><br>
            <a href='uploads/videos/{$row['filename']}' download>Download</a> |
            <a href='delete_video.php?id={$row['id']}'>Delete</a>
        </div>";
    }
    ?>
</body>
</html>
