<?php
include '../database_connection/db_connect.php';

// Fetch all batches, students, and videos
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");
$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
$videos = $conn->query("SELECT * FROM videos ORDER BY id DESC"); // video table has 'id'

// Handle form submission
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --text-color: #2b2d42;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        body { font-family: 'Poppins', sans-serif; background: var(--light-gray); padding:20px; }
        .container { max-width: 800px; background: white; margin: 0 auto; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--box-shadow); }
        h2 { text-align: center; margin-bottom: 25px; color: var(--text-color); font-size: 1.8rem; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        select, input[type="text"] { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius); }
        select[multiple] { min-height: 150px; }
        .btn { display:block; width:100%; padding:12px; background: var(--primary-color); color:white; font-weight:600; border:none; border-radius:var(--border-radius); cursor:pointer; }
        .btn:hover { background: var(--primary-hover); }
    </style>
</head>
<body>
<div class="container">
    <h2>Reassign Video</h2>
    <form method="POST">
        <!-- Select Video -->
        <div class="form-group">
            <label for="video_id">Select Video:</label>
            <select id="video_id" name="video_id" required>
                <?php while ($v = $videos->fetch_assoc()) { ?>
                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['title']) ?> (ID: <?= $v['id'] ?>)</option>
                <?php } ?>
            </select>
        </div>

        <!-- Assign to Batches / Students -->
        <div class="form-group">
            <label for="targets">Assign To (Batches / Students):</label>
            <select id="targets" name="targets[]" multiple required>
                <optgroup label="Batches">
                    <?php while ($b = $batches->fetch_assoc()) { ?>
                        <option value="batch_<?= $b['batch_id'] ?>">Batch: <?= htmlspecialchars($b['batch_name']) ?></option>
                    <?php } ?>
                </optgroup>
                <optgroup label="Students">
                    <?php while ($s = $students->fetch_assoc()) { ?>
                        <option value="student_<?= $s['student_id'] ?>">Student: <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['enrollment_id']) ?>)</option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>

        <button type="submit" class="btn">Reassign Video</button>
    </form>
</div>
</body>
</html>
