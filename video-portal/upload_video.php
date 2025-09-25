<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $batch_id = !empty($_POST['batch_id']) ? $_POST['batch_id'] : NULL;
    $student_id = !empty($_POST['student_id']) ? $_POST['student_id'] : NULL;

    $targetDir = "uploads/videos/";
    $filename = time() . "_" . basename($_FILES["video"]["name"]);
    $targetFile = $targetDir . $filename;

    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, assigned_to, batch_id, student_id) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssii", $title, $desc, $filename, $assigned_to, $batch_id, $student_id);
        $stmt->execute();
        header("Location: admin_videos.php");
    } else {
        echo "Error uploading video.";
    }
}
?>
