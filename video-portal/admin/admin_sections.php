<?php
include '../../database_connection/db_connect.php';

// Handle Section Creation
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['title'])){
    $title = $_POST['title'];
    $desc  = $_POST['description'] ?? '';
    $stmt = $conn->prepare("INSERT INTO sections (title, description, created_by) VALUES (?,?,?)");
    $stmt->bind_param("sss",$title,$desc,"Admin");
    $stmt->execute();
    header("Location: admin_sections.php");
    exit;
}

// Fetch sections
$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");
?>

<h2>Create Section</h2>
<form method="POST">
    <input type="text" name="title" placeholder="Section Title" required>
    <textarea name="description" placeholder="Section Description"></textarea>
    <button type="submit">Create Section</button>
</form>

<h2>Existing Sections</h2>
<?php while($sec=$sections->fetch_assoc()): ?>
    <div>
        <h3><?= htmlspecialchars($sec['title']) ?></h3>
        <p><?= htmlspecialchars($sec['description']) ?></p>
        <a href="upload_video_section.php?section_id=<?= $sec['section_id'] ?>">Add Videos</a>
    </div>
<?php endwhile; ?>
