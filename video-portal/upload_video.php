<?php
include '../database_connection/db_connect.php';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $video_file = $_FILES['video_file'];
    $thumbnail_file = $_FILES['thumbnail_file'] ?? null;

    if ($video_file['error'] == 0) {
        $video_name = time() . '_' . basename($video_file['name']);
        move_uploaded_file($video_file['tmp_name'], '../uploads/videos/' . $video_name);

        // Handle optional thumbnail
        $thumbnail_name = null;
        if ($thumbnail_file && $thumbnail_file['error'] == 0) {
            $thumbnail_name = time() . '_thumb_' . basename($thumbnail_file['name']);
            move_uploaded_file($thumbnail_file['tmp_name'], '../uploads/video_thumbnails/' . $thumbnail_name);
        }

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO videos (title, file_name, thumbnail) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $video_name, $thumbnail_name);
        $stmt->execute();

        header("Location: view_videos_admin.php?msg=uploaded");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Video</title>
     <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input[type="text"], input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        button { width: 100%; padding: 12px; background: #4361ee; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 1rem; transition: 0.3s; }
        button:hover { background: #3a56d4; }
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Video</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Video Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter video title" required>
        </div>
        <div class="form-group">
            <label for="video_file">Select Video:</label>
            <input type="file" id="video_file" name="video_file" accept="video/*" required>
        </div>
        <div class="form-group">
            <label for="thumbnail_file">Thumbnail (optional):</label>
            <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
        </div>
        <button type="submit">Upload Video</button>
    </form>
</div>
</body>
</html>
