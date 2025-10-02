<?php
include '../database_connection/db_connect.php';

// Fetch all batches and students
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");

// Fetch all videos
$videos = $conn->query("SELECT * FROM videos ORDER BY title ASC");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video_id = intval($_POST['video_id']);
    $targets = $_POST['targets'] ?? [];

    // First delete existing targets for this video
    $conn->query("DELETE FROM video_targets WHERE video_id = $video_id");

    // Insert new targets
    foreach ($targets as $t) {
        if (strpos($t, 'batch_') === 0) {
            $bid = intval(str_replace('batch_', '', $t));
            $conn->query("INSERT INTO video_targets (video_id, batch_id) VALUES ($video_id, $bid)");
        } elseif (strpos($t, 'student_') === 0) {
            $sid = intval(str_replace('student_', '', $t));
            $conn->query("INSERT INTO video_targets (video_id, student_id) VALUES ($video_id, $sid)");
        }
    }

    header("Location: reassign_video.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reassign Video</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 700px; background: #fff; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
        h2 { text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        select, input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #dee2e6; }
        select[multiple] { min-height: 150px; }
        .btn { background: #4361ee; color: #fff; padding: 12px; border: none; border-radius: 8px; width: 100%; cursor: pointer; font-weight: 600; }
        .btn:hover { background: #3a56d4; }
    </style>
</head>
<body>
<div class="container">
    <h2>Reassign Video</h2>
    <form method="POST">
        <div class="form-group">
            <label for="video_id">Select Video:</label>
            <select name="video_id" id="video_id" required>
                <option value="">-- Select Video --</option>
                <?php while ($v = $videos->fetch_assoc()) { ?>
                    <option value="<?= $v['video_id'] ?>"><?= $v['title'] ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="targets">Assign To:</label>
            <select id="targets" name="targets[]" multiple required>
                <optgroup label="Batches">
                    <?php while ($b = $batches->fetch_assoc()) { ?>
                        <option value="batch_<?= $b['batch_id'] ?>">Batch: <?= $b['batch_name'] ?></option>
                    <?php } ?>
                </optgroup>
                <optgroup label="Students">
                    <?php while ($s = $students->fetch_assoc()) { ?>
                        <option value="student_<?= $s['student_id'] ?>">Student: <?= $s['name'] ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>

        <button type="submit" class="btn">Reassign Video</button>
    </form>
</div>
</body>
</html>
