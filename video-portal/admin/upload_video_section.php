<?php
include '../../database_connection/db_connect.php';
$section_id = $_GET['section_id'] ?? NULL;

if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_FILES['video_file'])){
    $section_id = $_POST['section_id'] ?: NULL;
    $title      = $_POST['title'];
    $desc       = $_POST['description'] ?? '';
    $file       = $_FILES['video_file'];
    $filename   = time().'_'.$file['name'];
    move_uploaded_file($file['tmp_name'], 'videos/'.$filename);

    $stmt = $conn->prepare("INSERT INTO videos (section_id,title,description,filename,uploaded_by) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss",$section_id,$title,$desc,$filename,"Admin");
    $stmt->execute();
    $video_id = $conn->insert_id;

    // Auto assign to students/batches if section already assigned
    if($section_id){
        $assigned = $conn->query("SELECT * FROM video_assignments WHERE section_id=$section_id");
        while($a=$assigned->fetch_assoc()){
            $stmt2 = $conn->prepare("INSERT INTO video_assignments (video_id, section_id, batch_id, student_id, student_table) VALUES (?,?,?,?,?)");
            $stmt2->bind_param("iiiss",$video_id,$section_id,$a['batch_id'],$a['student_id'],$a['student_table']);
            $stmt2->execute();
        }
    }
    header("Location: upload_video_section.php?section_id=$section_id");
    exit;
}

// Fetch videos for section
$stmt2 = $conn->prepare("SELECT * FROM videos WHERE section_id=? ORDER BY upload_date DESC");
$stmt2->bind_param("i",$section_id);
$stmt2->execute();
$videos = $stmt2->get_result();

// Fetch all sections
$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");
?>

<h2>Upload Video</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Select Section (or leave blank for independent video)</label>
    <select name="section_id">
        <option value="">Independent Video</option>
        <?php while($s=$sections->fetch_assoc()): ?>
            <option value="<?= $s['section_id'] ?>" <?= $s['section_id']==$section_id?"selected":"" ?>><?= htmlspecialchars($s['title']) ?></option>
        <?php endwhile; ?>
    </select>

    <input type="text" name="title" placeholder="Video Title" required>
    <textarea name="description" placeholder="Video Description"></textarea>
    <input type="file" name="video_file" accept="video/*" required>
    <button type="submit">Upload</button>
</form>

<h3>Videos in this Section</h3>
<?php while($v=$videos->fetch_assoc()): ?>
    <div>
        <h4><?= htmlspecialchars($v['title']) ?></h4>
        <video width="300" controls>
            <source src="videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
        </video>
        <p><?= htmlspecialchars($v['description']) ?></p>
    </div>
<?php endwhile; ?>
