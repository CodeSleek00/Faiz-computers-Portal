<?php
include '../../database_connection/db_connect.php';

$section_id = $_GET['section_id'] ?? NULL;

// Fetch videos for this section
$videos = $conn->query("SELECT * FROM videos WHERE section_id=".($section_id??0));

// Fetch all students
$students = $conn->query("SELECT student_id AS id, name FROM students ORDER BY name ASC");
$students26 = $conn->query("SELECT id, name FROM students26 ORDER BY name ASC");

// Fetch all batches
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $video_id = $_POST['video_id'] ?? NULL;
    $section_id = $_POST['section_id'] ?? NULL;
    $selected_students = $_POST['students'] ?? [];
    $selected_batches  = $_POST['batches'] ?? [];

    foreach($selected_students as $s){
        list($student_id,$student_table) = explode("|",$s);
        $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, student_id, student_table) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis",$video_id,$section_id,$student_id,$student_table);
        $stmt->execute();
    }

    foreach($selected_batches as $b){
        $batch_id = $b;
        $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, batch_id) VALUES (?,?,?)");
        $stmt->bind_param("iii",$video_id,$section_id,$batch_id);
        $stmt->execute();
    }

    echo "<p>Assigned successfully!</p>";
}
?>

<h2>Assign Section/Video</h2>
<form method="POST">
    <label>Select Video (optional if assigning entire section)</label>
    <select name="video_id">
        <option value="">Assign Entire Section</option>
        <?php while($v=$videos->fetch_assoc()): ?>
            <option value="<?= $v['video_id'] ?>"><?= htmlspecialchars($v['title']) ?></option>
        <?php endwhile; ?>
    </select>

    <input type="hidden" name="section_id" value="<?= $section_id ?>">

    <h3>Select Students</h3>
    <?php while($s=$students->fetch_assoc()): ?>
        <input type="checkbox" name="students[]" value="<?= $s['id'] ?>|students"> <?= htmlspecialchars($s['name']) ?><br>
    <?php endwhile; ?>
    <?php while($s=$students26->fetch_assoc()): ?>
        <input type="checkbox" name="students[]" value="<?= $s['id'] ?>|students26"> <?= htmlspecialchars($s['name']) ?><br>
    <?php endwhile; ?>

    <h3>Select Batches</h3>
    <?php while($b=$batches->fetch_assoc()): ?>
        <input type="checkbox" name="batches[]" value="<?= $b['batch_id'] ?>"> <?= htmlspecialchars($b['batch_name']) ?><br>
    <?php endwhile; ?>

    <button type="submit">Assign</button>
</form>
