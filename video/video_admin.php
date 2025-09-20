<?php
include 'db_connect.php';
// TODO: add admin session check


// fetch batches and students for assignment
$batches = $pdo->query('SELECT batch_id,batch_name FROM batches')->fetchAll();
$students = $pdo->query('SELECT student_id, name FROM students')->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Video Dashboard - Admin</title>
<link rel="stylesheet" href="styles.css">
</head>
<body class="page">
<div class="container">
<h1>Admin Video Dashboard</h1>


<section class="card">
<h2>Upload Video (multiple qualities)</h2>
<form action="admin_upload_handler.php" method="post" enctype="multipart/form-data">
<label>Title<br><input type="text" name="title" required></label>
<label>Description<br><textarea name="description"></textarea></label>


<div class="upload-row">
<div>
<label>File (720p)<br><input type="file" name="files[720p]" accept="video/*"></label>
</div>
<div>
<label>File (480p)<br><input type="file" name="files[480p]" accept="video/*"></label>
</div>
<div>
<label>File (360p)<br><input type="file" name="files[360p]" accept="video/*"></label>
</div>
</div>


<h3>Assign To</h3>
<div class="assign-grid">
<div>
<label>Batch (optional)
<select name="batch_id">
<option value="">-- All / None --</option>
<?php foreach($batches as $b): ?>
<option value="<?=htmlspecialchars($b['batch_id'])?>"></option>
<?php endforeach; ?>
</select>
</label>
</div>


<div>
<label>Individual Students (hold ctrl to select multiple)
<select name="student_ids[]" multiple size="5">
<?php foreach($students as $s): ?>
<option value="<?=htmlspecialchars($s['student_id'])?>"><?=htmlspecialchars($s['name'])?></option>
<?php endforeach; ?>
</select>
</label>
</div>
</div>
</html>