<?php
include '../database_connection/db_connect.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
    $title = $_POST['title'];
    $desc  = $_POST['description'] ?? '';
    $uploaded_by = "Admin";

    $file_name = $_FILES['video']['name'];
    $tmp_name = $_FILES['video']['tmp_name'];
    $dest = "videos/".$file_name;
    move_uploaded_file($tmp_name,$dest);

    $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, uploaded_by) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss",$title,$desc,$file_name,$uploaded_by);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}
?>

<h1>Upload Video</h1>
<form method="POST" enctype="multipart/form-data">
<label>Title</label>
<input type="text" name="title" required><br>
<label>Description</label>
<textarea name="description"></textarea><br>
<label>Video File</label>
<input type="file" name="video" accept="video/*" required><br>
<button type="submit">Upload</button>
</form>
