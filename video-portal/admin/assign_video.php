<?php
include '../database_connection/db_connect.php';

// Fetch students and batches
$students = $conn->query("SELECT student_id, name, 'students' as student_table FROM students
                         UNION
                         SELECT id, name, 'students26' as student_table FROM students26
                         ORDER BY name ASC");
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name ASC");

if($_SERVER['REQUEST_METHOD']=="POST"){
    $section_id = $_POST['section_id'] ?? NULL;
    $video_id   = $_POST['video_id'] ?? NULL;
    $selected_students = $_POST['students'] ?? [];
    $selected_batches  = $_POST['batches'] ?? [];

    $video_id_list = [];
    if($video_id){ $video_id_list[] = $video_id; }
    elseif($section_id){
        $vids = $conn->query("SELECT video_id FROM videos WHERE section_id=$section_id");
        while($v=$vids->fetch_assoc()){ $video_id_list[] = $v['video_id']; }
    }

    foreach($selected_students as $s){
        list($student_id,$student_table) = explode("|",$s);
        foreach($video_id_list as $vid){
            $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, student_id, student_table) VALUES (?,?,?,?)");
            $stmt->bind_param("iiis",$vid,$section_id,$student_id,$student_table);
            $stmt->execute();
        }
    }

    foreach($selected_batches as $b){
        $batch_id = $b;
        foreach($video_id_list as $vid){
            $stmt = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, batch_id) VALUES (?,?,?)");
            $stmt->bind_param("iii",$vid,$section_id,$batch_id);
            $stmt->execute();
        }
    }

    echo "Assigned successfully!";
}
?>

<h1>Assign Video / Section</h1>
<form method="POST">
<label>Section (optional)</label>
<select name="section_id" id="section_select" onchange="loadVideos(this.value)">
<option value="">--Select Section--</option>
<?php
$secs = $conn->query("SELECT * FROM sections");
while($s=$secs->fetch_assoc()): ?>
<option value="<?= $s['section_id'] ?>"><?= htmlspecialchars($s['title']) ?></option>
<?php endwhile; ?>
</select>

<label>Video (optional)</label>
<select name="video_id" id="video_select"><option value="">--Select Video--</option></select>

<label>Students</label>
<select name="students[]" multiple>
<?php while($st=$students->fetch_assoc()): ?>
<option value="<?= $st['student_id'] ?>|<?= $st['student_table'] ?>"><?= htmlspecialchars($st['name']) ?></option>
<?php endwhile; ?>
</select>

<label>Batches</label>
<select name="batches[]" multiple>
<?php while($b=$batches->fetch_assoc()): ?>
<option value="<?= $b['batch_id'] ?>"><?= htmlspecialchars($b['batch_name']) ?></option>
<?php endwhile; ?>
</select>

<button type="submit">Assign</button>
</form>

<script>
function loadVideos(sectionId){
    var select = document.getElementById('video_select');
    select.innerHTML = '<option value="">--Select Video--</option>';
    if(sectionId=='') return;
    fetch('fetch_videos.php?section_id='+sectionId)
    .then(res=>res.json())
    .then(data=>{
        data.forEach(v=>{
            var opt = document.createElement('option');
            opt.value = v.video_id;
            opt.innerText = v.title;
            select.appendChild(opt);
        });
    });
}
</script>
