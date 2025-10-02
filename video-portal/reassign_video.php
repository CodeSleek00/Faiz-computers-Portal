<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename    = $_POST['filename'];
    $title       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $assigned_to = $_POST['assigned_to'];

    $batch_id   = null;
    $student_id = null;

    if ($assigned_to === "batch") {
        $batch_id = $_POST['batch_id'] ?? null;
    } elseif ($assigned_to === "student") {
        $student_id = $_POST['student_id'] ?? null;
    }

    // Thumbnail upload
    $thumbnail = null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbName = time() . "_" . basename($_FILES['thumbnail']['name']);
        $target = "../uploads/thumbnails/" . $thumbName;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
            $thumbnail = $thumbName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO videos 
        (title, description, filename, thumbnail, assigned_to, batch_id, student_id, uploaded_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param("sssssss", $title, $description, $filename, $thumbnail, $assigned_to, $batch_id, $student_id);

    if ($stmt->execute()) {
        header("Location: admin_videos.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
