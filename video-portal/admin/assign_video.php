<?php
include '../database_connection/db_connect.php';

// Fetch students and batches
$students = $conn->query("SELECT student_id, name, 'students' as student_table FROM students
                         UNION
                         SELECT id, name, 'students26' as student_table FROM students26
                         ORDER BY name ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

// Fetch videos
$videos = $conn->query("SELECT * FROM videos ORDER BY upload_date DESC");

if($_SERVER['REQUEST_METHOD']=="POST"){
    $video_id   = $_POST['video_id'] ?? NULL;
    $selected_students = $_POST['students'] ?? [];
    $selected_batches  = $_POST['batches'] ?? [];

    foreach($selected_students as $s){
        list($student_id,$student_table) = explode("|",$s);
        $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, student_id, student_table) VALUES (?,?,?)");
        $stmt->bind_param("iis",$video_id,$student_id,$student_table);
        $stmt->execute();
    }

    foreach($selected_batches as $b){
        $batch_id = $b;
        $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, batch_id) VALUES (?,?)");
        $stmt->bind_param("ii",$video_id,$batch_id);
        $stmt->execute();
    }

    echo "Assigned successfully!";
}
?>

<h1>Assign Video</h1>
<form method="POST">
<label>Select Video</label>
<select name="video_id" required>
<option value="">--Select Video--</option>
<?php while($v=$videos->fetch_assoc()): ?>
<option value="<?= $v['video_id'] ?>"><?= htmlspecialchars($v['title']) ?></option>
<?php endwhile; ?>
</select><br>

<label>Students</label>
<select name="students[]" multiple>
<?php while($st=$students->fetch_assoc()): ?>
<option value="<?= $st['student_id'] ?>|<?= $st['student_table'] ?>"><?= htmlspecialchars($st['name']) ?></option>
<?php endwhile; ?>
</select><br>

<label>Batches</label>
<select name="batches[]" multiple>
<?php while($b=$batches->fetch_assoc()): ?>
<option value="<?= $b['batch_id'] ?>"><?= htmlspecialchars($b['batch_name']) ?></option>
<?php endwhile; ?>
</select><br>

<button type="submit">Assign</button>
</form>
