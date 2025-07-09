<?php
session_start();
include '../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$student = $conn->query("SELECT * FROM students WHERE enrollment_id = '$enrollment_id'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | <?= htmlspecialchars($student['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>
        
       
                <img class="profile-img" src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo">
                <h2 class="student-name"><?= htmlspecialchars($student['name']) ?></h2>
                <span class="student-id">ID: <?= htmlspecialchars($student['enrollment_id']) ?></span>
                <p>Course<?= htmlspecialchars($student['course']) ?></p>
                <p>Address<?= htmlspecialchars($student['address']) ?></p>
                <p>Contact Number<?= htmlspecialchars($student['contact_number']) ?></p>
           
</body>
</html>