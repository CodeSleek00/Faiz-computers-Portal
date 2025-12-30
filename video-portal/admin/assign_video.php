<?php
include '../../database_connection/db_connect.php';

$section_id = $_POST['section_id'] ?? NULL;
$video_id   = $_POST['video_id'] ?? NULL;
$selected_students = $_POST['students'] ?? [];
$selected_batches  = $_POST['batches'] ?? [];

// Check if assigning a video
if($video_id){
    // Verify video exists
    $check = $conn->prepare("SELECT video_id FROM videos WHERE video_id=?");
    $check->bind_param("i",$video_id);
    $check->execute();
    $res = $check->get_result();
    if($res->num_rows==0){
        die("Error: Selected video does not exist.");
    }
}

// If assigning section (all videos inside it)
if($section_id && !$video_id){
    $vids = $conn->query("SELECT video_id FROM videos WHERE section_id=$section_id");
    while($v=$vids->fetch_assoc()){
        $video_id_list[] = $v['video_id'];
    }
} else {
    $video_id_list[] = $video_id; // single video
}

// Assign to students
foreach($selected_students as $s){
    list($student_id,$student_table) = explode("|",$s);
    foreach($video_id_list as $vid){
        $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, student_id, student_table) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis",$vid,$section_id,$student_id,$student_table);
        $stmt->execute();
    }
}

// Assign to batches
foreach($selected_batches as $b){
    $batch_id = $b;
    foreach($video_id_list as $vid){
        $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, batch_id) VALUES (?,?,?)");
        $stmt->bind_param("iii",$vid,$section_id,$batch_id);
        $stmt->execute();
    }
}

echo "Assigned successfully!";

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
