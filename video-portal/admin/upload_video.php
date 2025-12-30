<?php
include '../database_connection/db_connect.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
    $section_id = $_POST['section_id'] ?? NULL;
    $title = $_POST['title'];
    $desc = $_POST['description'] ?? '';
    $uploaded_by = "Admin";

    $file_name = $_FILES['video']['name'];
    $tmp_name = $_FILES['video']['tmp_name'];
    $dest = "videos/".$file_name;
    move_uploaded_file($tmp_name,$dest);

    $stmt = $conn->prepare("INSERT INTO videos (section_id,title,description,filename,uploaded_by) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss",$section_id,$title,$desc,$file_name,$uploaded_by);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");
?>

<h1>Upload Video</h1>
<form method="POST" enctype="multipart/form-data">
<label>Title</label>
<input type="text" name="title" required>
<label>Description</label>
<textarea name="description"></textarea>
<label>Section (optional)</label>
<select name="section_id">
<option value="">Independent Video</option>
<?php while($sec=$sections->fetch_assoc()): ?>
<option value="<?= $sec['section_id'] ?>"><?= htmlspecialchars($sec['title']) ?></option>
<?php endwhile; ?>
</select>
<label>Video File</label>
<input type="file" name="video" accept="video/*" required>
<button type="submit">Upload</button>
</form>
