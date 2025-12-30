<?php
include '../database_connection/db_connect.php';

// Add section
if($_SERVER['REQUEST_METHOD']=="POST"){
    $title = $_POST['title'];
    $desc  = $_POST['description'] ?? '';
    $stmt = $conn->prepare("INSERT INTO sections (title, description, created_by) VALUES (?,?,?)");
    $admin = "Admin";
    $stmt->bind_param("sss",$title,$desc,$admin);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

// Fetch sections
$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");
?>

<h1>Manage Sections</h1>
<form method="POST">
<input type="text" name="title" placeholder="Section Title" required>
<textarea name="description" placeholder="Description"></textarea>
<button type="submit">Add Section</button>
</form>

<h2>Existing Sections</h2>
<ul>
<?php while($sec=$sections->fetch_assoc()): ?>
<li><?= htmlspecialchars($sec['title']) ?> - <?= htmlspecialchars($sec['description']) ?></li>
<?php endwhile; ?>
</ul>
