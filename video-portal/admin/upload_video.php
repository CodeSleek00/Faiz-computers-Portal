<?php
include "../includes/db_connect.php";

// Fetch batches & students
$batches = $conn->query("SELECT * FROM batches");
$students1 = $conn->query("SELECT student_id, name FROM students");
$students2 = $conn->query("SELECT id AS student_id, name FROM students26");
?>
<form action="upload_video_action.php" method="POST" enctype="multipart/form-data">
<input type="text" name="title" placeholder="Title" required>
<textarea name="description" placeholder="Description"></textarea>
<input type="file" name="video_file" accept="video/*" required>

<label>Assign to Batch:</label>
<select name="batch_id"><option value="">None</option>
<?php while($b = $batches->fetch_assoc()){ ?>
<option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
<?php } ?>
</select>

<label>Assign to Student:</label>
<select name="student_id"><option value="">None</option>
<optgroup label="Students">
<?php while($s = $students1->fetch_assoc()){ ?>
<option value="<?= $s['student_id'] ?>|students"><?= $s['name'] ?></option>
<?php } ?>
</optgroup>
<optgroup label="Students26">
<?php while($s = $students2->fetch_assoc()){ ?>
<option value="<?= $s['student_id'] ?>|students26"><?= $s['name'] ?></option>
<?php } ?>
</optgroup>
</select>

<button type="submit">Upload Video</button>
</form>
