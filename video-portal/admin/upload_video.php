<?php
include "../includes/db_connect.php";
include "../includes/session_check.php";

// Fetch batches & students
$batches = $conn->query("SELECT * FROM batches");
$students = $conn->query("SELECT student_id, name FROM students");
?>
<!DOCTYPE html>
<html>
<head>
<title>Upload Video</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h2>Upload New Video</h2>
<form action="upload_video_action.php" method="POST" enctype="multipart/form-data">
<label>Title:</label>
<input type="text" name="title" required><br>
<label>Description:</label>
<textarea name="description"></textarea><br>
<label>Video File:</label>
<input type="file" name="video_file" accept="video/*" required><br>

<label>Assign to Batch:</label>
<select name="batch_id">
<option value="">None</option>
<?php while($b = $batches->fetch_assoc()) { ?>
<option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
<?php } ?>
</select><br>

<label>Assign to Student:</label>
<select name="student_id">
<option value="">None</option>
<?php while($s = $students->fetch_assoc()) { ?>
<option value="<?= $s['student_id'] ?>"><?= $s['name'] ?></option>
<?php } ?>
</select><br>

<button type="submit">Upload</button>
</form>
</body>
</html>
