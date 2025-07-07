<?php
include '../database_connection/db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid batch ID.");
}

$batch_id = intval($_GET['id']);

// First, delete students from student_batches
$delete_students = $conn->prepare("DELETE FROM student_batches WHERE batch_id = ?");
$delete_students->bind_param("i", $batch_id);
$delete_students->execute();

// Then, delete the batch
$delete_batch = $conn->prepare("DELETE FROM batches WHERE batch_id = ?");
$delete_batch->bind_param("i", $batch_id);
$delete_batch->execute();

header("Location: view_batch.php");
exit;
?>
