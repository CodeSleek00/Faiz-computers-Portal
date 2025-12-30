<?php
include "../includes/db_connect.php";

$title = $_POST['title'];
$description = $_POST['description'];
$batch_id = $_POST['batch_id'] ?: NULL;
$student_id = $_POST['student_id'] ?: NULL;

// File upload
if(isset($_FILES['video_file'])){
    $file = $_FILES['video_file'];
    $filename = time().'_'.$file['name'];
    move_uploaded_file($file['tmp_name'], '../videos/'.$filename);

    $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, uploaded_by) VALUES (?, ?, ?, ?)");
    $uploaded_by = "Admin";  // Default, no login required
    $stmt->bind_param("ssss", $title, $description, $filename, $uploaded_by);
    $stmt->execute();
    $video_id = $stmt->insert_id;

    // Assign
    $stmt2 = $conn->prepare("INSERT INTO video_assignments (video_id, batch_id, student_id) VALUES (?, ?, ?)");
    $stmt2->bind_param("iii", $video_id, $batch_id, $student_id);
    $stmt2->execute();

    header("Location: admin_dashboard.php");
    exit;
}
?>
