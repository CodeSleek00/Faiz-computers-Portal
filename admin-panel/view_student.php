<?php
include '../database_connection/db_connect.php';

// Check if ID exists and is valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID");
}

$id = (int)$_GET['id'];

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if student exists
if ($result->num_rows === 0) {
    die("Student not found");
}

$row = $result->fetch_assoc();
$stmt->close();
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
    <div class="container">
        <h2>Student Details</h2>
        
        <?php if (!empty($row['photo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" class="student-photo" alt="Student Photo">
        <?php else: ?>
            <div class="student-photo" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                No Photo
            </div>
        <?php endif; ?>
        
        <div class="detail-item">
            <strong>Name:</strong> <?= htmlspecialchars($row['name']) ?>
        </div>
        
        <div class="detail-item">
            <strong>Contact:</strong> <?= htmlspecialchars($row['contact_number']) ?>
        </div>
        
        <div class="detail-item">
            <strong>Courses:</strong> <?=htmlspecialchars($row['course']) ?>
        </div>
        
        <div class="detail-item">
            <strong>Address:</strong> <?= htmlspecialchars($row['address']) ?>
        </div>
        
        <div class="detail-item">
            <strong>Enrollment ID:</strong> <?= htmlspecialchars($row['enrollment_id']) ?>
        </div>
        
        <a href="manage_student.php" class="back-link">Back to Students</a>
    </div>
</body>
</html>