<?php
// student26.php
session_start();
include("database_connection/db_connect.php");

// ================= GET STUDENT =================
$student_id = $_GET['id'] ?? 26; // default student26

$stmt = $conn->prepare("SELECT id, name, contact_number, enrollment_id, course, photo FROM students WHERE id=? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found");
}

// ================= UPDATE STUDENT =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'];
    $contact = $_POST['contact'];
    $enroll  = $_POST['enrollment'];
    $course  = $_POST['course'];

    // IMAGE UPLOAD
    if (!empty($_FILES['photo']['name'])) {
        $photoName = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photoName);
    } else {
        $photoName = $student['photo'];
    }

    $update = $conn->prepare("UPDATE students SET name=?, contact_number=?, enrollment_id=?, course=?, photo=? WHERE id=?");
    $update->bind_param("sssssi", $name, $contact, $enroll, $course, $photoName, $student_id);
    $update->execute();

    header("Location: student26.php?id=$student_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body{
    font-family:Poppins, sans-serif;
    background:#f4f6f9;
}
.dashboard{
    max-width:900px;
    margin:40px auto;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    padding:30px;
}
.profile{
    display:flex;
    align-items:center;
    gap:30px;
}
.profile img{
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #2563eb;
}
.info h2{
    margin:0;
    color:#1f2937;
}
.info p{
    margin:6px 0;
    color:#4b5563;
}
.edit-btn{
    margin-top:20px;
    background:#2563eb;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:8px;
    cursor:pointer;
}
form{
    margin-top:30px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
form input{
    padding:10px;
    border-radius:8px;
    border:1px solid #d1d5db;
}
form button{
    grid-column:span 2;
    background:#16a34a;
    color:white;
    padding:12px;
    border:none;
    border-radius:8px;
    font-size:16px;
}
</style>
</head>
<body>

<div class="dashboard">
    <div class="profile">
        <img src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Student Photo">
        <div class="info">
            <h2><?= htmlspecialchars($student['name']) ?></h2>
            <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact_number']) ?></p>
            <p><strong>Enrollment:</strong> <?= htmlspecialchars($student['enrollment_id']) ?></p>
            <p><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" placeholder="Student Name" required>
        <input type="text" name="contact" value="<?= htmlspecialchars($student['contact_number']) ?>" placeholder="Contact Number" required>
        <input type="text" name="enrollment" value="<?= htmlspecialchars($student['enrollment_id']) ?>" placeholder="Enrollment ID" required>
        <input type="text" name="course" value="<?= htmlspecialchars($student['course']) ?>" placeholder="Course" required>
        <input type="file" name="photo">
        <button type="submit">Update Student Details</button>
    </form>
</div>

</body>
</html>