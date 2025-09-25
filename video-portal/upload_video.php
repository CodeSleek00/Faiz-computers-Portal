<?php
include 'db_connect.php';

// Get form data
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$assigned_to = $_POST['assigned_to'] ?? '';
$batch_id = $_POST['batch_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;

// Check if video is uploaded
if(!isset($_FILES['video']) || $_FILES['video']['error'] != 0){
    http_response_code(400);
    echo "Video upload failed!";
    exit;
}

// Video upload
$videoFile = $_FILES['video'];
$videoName = time().'_'.basename($videoFile['name']);
$videoPath = "../uploads/videos/".$videoName;

// Ensure directory exists
if(!is_dir("../uploads/videos/")){
    mkdir("../uploads/videos/", 0777, true);
}

if(!move_uploaded_file($videoFile['tmp_name'], $videoPath)){
    http_response_code(500);
    echo "Video upload failed!";
    exit;
}

// Thumbnail upload (optional)
$thumbName = null;
if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0){
    $thumbFile = $_FILES['thumbnail'];
    $thumbName = time().'_'.basename($thumbFile['name']);
    $thumbPath = "../uploads/thumbnails/".$thumbName;

    if(!is_dir("../uploads/thumbnails/")){
        mkdir("../uploads/thumbnails/", 0777, true);
    }

    move_uploaded_file($thumbFile['tmp_name'], $thumbPath);
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO videos (title, description, filename, thumbnail, assigned_to, batch_id, student_id, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssis", $title, $description, $videoName, $thumbName, $assigned_to, $batch_id, $student_id);
if($stmt->execute()){
    echo "Upload successful";
} else {
    http_response_code(500);
    echo "Database insert failed!";
}
?>
