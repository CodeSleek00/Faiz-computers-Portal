<?php
include '../database_connection/db_connect.php';

$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$videos = $conn->query("SELECT * FROM videos ORDER BY id DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_id = intval($_POST['video_id']);
    $targets = $_POST['targets'] ?? [];

    // Delete old assignments
    $conn->query("DELETE FROM video_targets WHERE video_id = $video_id");

    // Insert new assignments
    foreach ($targets as $t) {
        if (strpos($t, 'batch_') === 0) {
            $batch_id = intval(str_replace('batch_', '', $t));
            $conn->query("INSERT INTO video_targets (video_id, batch_id) VALUES ($video_id, $batch_id)");
        } elseif (strpos($t, 'student_') === 0) {
            $student_id = intval(str_replace('student_', '', $t));
            $conn->query("INSERT INTO video_targets (video_id, student_id) VALUES ($video_id, $student_id)");
        }
    }

    header("Location: view_videos_admin.php?msg=reassigned");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reassign Video</title>
</head>
<body>
<h2>Reassign Video</h2>
<form method="POST">
    <label>Select Video:</label>
    <select name="video_id" required>
        <?php while($v = $videos->fetch_assoc()) { ?>
            <option value="<?= $v['id'] ?>"><?= $v['title'] ?> (ID: <?= $v['id'] ?>)</option>
        <?php } ?>
    </select>

    <label>Assign to Batches/Students:</label>
    <select name="targets[]" multiple required>
        <optgroup label="Batches">
            <?php while($b = $batches->fetch_assoc()) { ?>
                <option value="batch_<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
            <?php } ?>
        </optgroup>
        <optgroup label="Students">
            <?php while($s = $students->fetch_assoc()) { ?>
                <option value="student_<?= $s['student_id'] ?>"><?= $s['name'] ?> (<?= $s['enrollment_id'] ?>)</option>
            <?php } ?>
        </optgroup>
    </select>

    <button type="submit">Assign Video</button>
</form>
</body>
</html>
