<?php
include 'db_connect.php';

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$filename = $_POST['filename'] ?? '';
$assigned_to = $_POST['assigned_to'] ?? '';
$batch_id = $_POST['batch_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;

// Thumbnail upload handling
$thumbName = null;
if (!empty($_FILES['thumbnail']['name'])) {
    $thumbFile = $_FILES['thumbnail'];
    $thumbName = time() . "_" . basename($thumbFile['name']);
    $thumbPath = "../uploads/thumbnails/" . $thumbName;

    if (!is_dir("../uploads/thumbnails/")) {
        mkdir("../uploads/thumbnails/", 0777, true);
    }

    move_uploaded_file($thumbFile['tmp_name'], $thumbPath);
}

// ✅ Function to insert video
function assignVideo($conn, $title, $description, $filename, $thumbName, $assigned_to, $batch_id, $student_id) {
    $stmt = $conn->prepare("INSERT INTO videos 
        (title, description, filename, thumbnail, assigned_to, batch_id, student_id, uploaded_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssis", $title, $description, $filename, $thumbName, $assigned_to, $batch_id, $student_id);
    $stmt->execute();
}

// ✅ Case 1: Assign to single student
if ($assigned_to === "student" && $student_id) {
    assignVideo($conn, $title, $description, $filename, $thumbName, $assigned_to, $batch_id, $student_id);

} 
// ✅ Case 2: Assign to whole batch
elseif ($assigned_to === "batch" && $batch_id) {
    // ⚡ Change this table name/column according to your DB
    $students = $conn->query("SELECT student_id FROM student_batches WHERE batch_id = '$batch_id'");

    while ($row = $students->fetch_assoc()) {
        $studentId = $row['student_id'];
        assignVideo($conn, $title, $description, $filename, $thumbName, $assigned_to, $batch_id, $studentId);
    }
}

header("Location: assign_existing_video.php?success=1");
exit;
?>
