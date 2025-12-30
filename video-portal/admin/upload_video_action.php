<?php
include "../includes/db_connect.php";

$title = $_POST['title'];
$description = $_POST['description'];
$batch_id = $_POST['batch_id'] ?: NULL;

$student_raw = $_POST['student_id'] ?? NULL;
$student_id = NULL;
$student_table = NULL;

if($student_raw){
    list($student_id,$student_table) = explode("|",$student_raw);
}

// File upload
if(isset($_FILES['video_file'])){
    $file = $_FILES['video_file'];
    $filename = time().'_'.$file['name'];
    move_uploaded_file($file['tmp_name'], '../videos/'.$filename);

    $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, uploaded_by) VALUES (?,?,?,?)");
    $uploaded_by = "Admin";
    $stmt->bind_param("ssss",$title,$description,$filename,$uploaded_by);
    $stmt->execute();
    $video_id = $stmt->insert_id;

    // Insert into video_assignments
    $stmt2 = $conn->prepare("INSERT INTO video_assignments (video_id, batch_id, student_id, student_table) VALUES (?,?,?,?)");
    $stmt2->bind_param("iiis",$video_id,$batch_id,$student_id,$student_table);
    $stmt2->execute();

    header("Location: admin_dashboard.php");
    exit;
}
?>
