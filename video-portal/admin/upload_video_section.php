<?php
include '../../database_connection/db_connect.php';
$section_id = $_GET['section_id'];

// Handle video upload
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_FILES['video_file'])){
    $title = $_POST['title'];
    $desc  = $_POST['description'] ?? '';
    $file  = $_FILES['video_file'];
    $filename = time().'_'.$file['name'];
    move_uploaded_file($file['tmp_name'], '../videos/'.$filename);

    $stmt = $conn->prepare("INSERT INTO videos (section_id, title, description, filename, uploaded_by) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss",$section_id,$title,$desc,$filename,"Admin");
    $stmt->execute();
    header("Location: upload_video_section.php?section_id=$section_id");
    exit;
}

// Fetch existing videos for section
$stmt2 = $conn->prepare("SELECT * FROM videos WHERE section_id=? ORDER BY upload_date DESC");
$stmt2->bind_param("i",$section_id);
$stmt2->execute();
$videos = $stmt2->get_result();
?>

<h2>Upload Video to Section</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Video Title" required>
    <textarea name="description" placeholder="Video Description"></textarea>
    <input type="file" name="video_file" accept="video/*" required>
    <button type="submit">Upload</button>
</form>

<h2>Videos in Section</h2>
<?php while($v=$videos->fetch_assoc()): ?>
    <div>
        <h4><?= htmlspecialchars($v['title']) ?></h4>
        <video width="300" controls>
            <source src="../videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
        </video>
        <p><?= htmlspecialchars($v['description']) ?></p>
    </div>
<?php endwhile; ?>
