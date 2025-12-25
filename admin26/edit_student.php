<?php
include("../database_connection/db_connect.php");

$id = $_GET['id'] ?? 0;

// Fetch student
$stmt = $conn->prepare("SELECT * FROM students26 WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found");
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'];
    $contact = $_POST['contact'];
    $enroll  = $_POST['enrollment'];
    $course  = $_POST['course'];

    if (!empty($_FILES['photo']['name'])) {
        $photo = time().'_'.$_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/".$photo);
    } else {
        $photo = $student['photo'];
    }

    $update = $conn->prepare("
        UPDATE students26 
        SET name=?, contact=?, enrollment_id=?, course=?, photo=? 
        WHERE id=?
    ");
    $update->bind_param("sssssi", $name, $contact, $enroll, $course, $photo, $id);
    $update->execute();

    header("Location: admin_dashboard26.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
body{font-family:Poppins;background:#f4f6f9}
.box{max-width:500px;margin:40px auto;background:#fff;padding:25px;border-radius:12px}
input,button{width:100%;padding:10px;margin-top:10px}
button{background:#2563eb;color:#fff;border:none;border-radius:6px}
</style>
</head>
<body>

<div class="box">
<h2>Edit Student</h2>

<form method="post" enctype="multipart/form-data">
    <input type="text" name="name" value="<?= $student['name'] ?>" required>
    <input type="text" name="contact" value="<?= $student['contact'] ?>" required>
    <input type="text" name="enrollment" value="<?= $student['enrollment_id'] ?>" required>
    <input type="text" name="course" value="<?= $student['course'] ?>" required>
    <input type="file" name="photo">
    <button type="submit">Update Student</button>
</form>
</div>

</body>
</html>
