<?php
include 'db_connect.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM students WHERE student_id = $id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

    <title>View Student</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        min-height: 100vh;
        color: #333;
        line-height: 1.6;
    }
    
    .container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        padding: 2.5rem;
        max-width: 600px;
        width: 100%;
        margin: 2rem;
    }
    
    h2 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 1.5rem;
        font-weight: 600;
        font-size: 1.8rem;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.8rem;
    }
    
    .student-photo {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #f0f0f0;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .detail-item {
        margin-bottom: 1rem;
        padding: 0.8rem;
        background-color: #f9f9f9;
        border-radius: 8px;
    }
    
    .detail-item strong {
        color: #555;
        font-weight: 500;
        display: inline-block;
        min-width: 120px;
    }
    
    .back-link {
        display: inline-block;
        margin-top: 2rem;
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .back-link:hover {
        background-color: #f0f7ff;
        text-decoration: none;
    }
    
    .back-link::before {
        content: "←";
        margin-right: 6px;
    }
    
    @media (max-width: 600px) {
        .container {
            padding: 1.5rem;
            margin: 1rem;
        }
        
        h2 {
            font-size: 1.5rem;
        }
        
        .detail-item strong {
            display: block;
            margin-bottom: 0.3rem;
        }
    }
</style>
</head>
<body>
    <h2>Student Details</h2>
    <img src="uploads/<?= $row['photo'] ?>" width="120"><br><br>
    <strong>Name:</strong> <?= $row['name'] ?><br>
    <strong>Contact:</strong> <?= $row['contact_number'] ?><br>
    <strong>Address:</strong> <?= $row['address'] ?><br>
    <strong>Enrollment ID:</strong> <?= $row['enrollment_id'] ?><br>
    <a href="manage_students.php">⬅ Back</a>
</body>
</html>
