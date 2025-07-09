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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7ff;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .dashboard {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 450px;
        }

        .dashboard h1 {
            font-size: 28px;
            color: #4a4a4a;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 4px solid #4c84ff;
        }

        .student-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .student-id {
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
            display: inline-block;
        }

        .info {
            font-size: 16px;
            color: #444;
            margin-bottom: 10px;
        }

        .info i {
            margin-right: 8px;
            color: #4c84ff;
        }

        @media (max-width: 500px) {
            .dashboard {
                padding: 25px;
            }

            .dashboard h1 {
                font-size: 22px;
            }

            .student-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Student Dashboard</h1>
        <img class="profile-img" src="../uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo">
        <h2 class="student-name"><?= htmlspecialchars($student['name']) ?></h2>
        <span class="student-id">ID: <?= htmlspecialchars($student['enrollment_id']) ?></span>
        
        <p class="info"><i class="fas fa-book"></i>Course: <?= htmlspecialchars($student['course']) ?></p>
        <p class="info"><i class="fas fa-map-marker-alt"></i>Address: <?= htmlspecialchars($student['address']) ?></p>
        <p class="info"><i class="fas fa-phone-alt"></i>Contact: <?= htmlspecialchars($student['contact_number']) ?></p>
        <p class="info"><a href="logout.php">Logout</a>
    </div>
</body>
</html>
