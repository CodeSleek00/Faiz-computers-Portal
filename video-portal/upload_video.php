<?php
include '../database_connection/db_connect.php';

// Fetch all batches and students
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $file = $_FILES['video_file'];

    if ($file['error'] == 0) {
        $filename = time() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], '../uploads/videos/' . $filename);

        // Save video to DB
        $stmt = $conn->prepare("INSERT INTO videos (title, file_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $filename);
        $stmt->execute();
        $video_id = $stmt->insert_id;

        header("Location: view_videos_admin.php?msg=uploaded");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Video</title>
    <style>
        body { font-family: Arial; padding: 20px; background:#f4f4f4; }
        .container { max-width: 600px; margin:auto; background:white; padding:20px; border-radius:8px; }
        input, button { width:100%; padding:10px; margin:5px 0; }
    </style>
</head>
<body>
<div class="container">
<h2>Upload Video</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Video Title" required>
    <input type="file" name="video_file" accept="video/*" required>
    <button type="submit">Upload</button>
</form>
</div>
</body>
</html>
