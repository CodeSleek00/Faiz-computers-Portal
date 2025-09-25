<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = !empty($_POST['title']) ? $_POST['title'] : $_POST['filename'];
    $desc = $_POST['description'];
    $filename = $_POST['filename'];
    $assigned_to = $_POST['assigned_to'];
    $batch_id = !empty($_POST['batch_id']) ? $_POST['batch_id'] : NULL;
    $student_id = !empty($_POST['student_id']) ? $_POST['student_id'] : NULL;

    $stmt = $conn->prepare("INSERT INTO videos (title, description, filename, assigned_to, batch_id, student_id) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssii", $title, $desc, $filename, $assigned_to, $batch_id, $student_id);
    $stmt->execute();

    header("Location: assign_existing_video.php");
}
?>
