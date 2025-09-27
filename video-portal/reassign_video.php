<?php
include 'db_connect.php';

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$filename = $_POST['filename'];
$assigned_to = $_POST['assigned_to'];
$batch_id = $_POST['batch_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;

// Thumbnail upload handling
$thumbName = null;
if (!empty($_FILES['thumbnail']['name'])) {
    $thumbFile = $_FILES['thumbnail'];
    $thumbName = time() . "_" . basename($thumbFile['name']);
    $thumbPath = "../uploads/thumbnails/" . $thumbName;
    
    // Ensure directory exists
    if(!is_dir("../uploads/thumbnails/")) {
        mkdir("../uploads/thumbnails/", 0777, true);
    }

    move_uploaded_file($thumbFile['tmp_name'], $thumbPath);
}

if ($assigned_to === "student" && $student_id) {
    // ✅ Assign video to single student
    $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, thumbnail, assigned_to, batch_id, student_id, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssis", $title, $description, $filename, $thumbName, $assigned_to, $batch_id, $student_id);
    $stmt->execute();

} elseif ($assigned_to === "batch" && $batch_id) {
    // ✅ Assign video to all students in that batch
    $students = $conn->query("SELECT student_id FROM student_enrolled WHERE batch_id = '$batch_id'");
    
    while ($row = $students->fetch_assoc()) {
        $studentId = $row['student_id'];
        $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, thumbnail, assigned_to, batch_id, student_id, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssis", $title, $description, $filename, $thumbName, $assigned_to, $batch_id, $studentId);
        $stmt->execute();
    }
}

header("Location: assign_existing_video.php?success=1");
exit;
?>
