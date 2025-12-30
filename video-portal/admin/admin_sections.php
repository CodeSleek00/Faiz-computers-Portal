<?php
include '../../database_connection/db_connect.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $created_by = "Admin";

    $stmt = $conn->prepare("INSERT INTO sections (title, description, created_by) VALUES (?,?,?)");
    $stmt->bind_param("sss",$title,$desc,$created_by);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

$sections = $conn->query("SELECT * FROM sections ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Sections</title>
</head>
<body>
<h1>Sections</h1>
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
</body>
</html>
