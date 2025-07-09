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
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            margin: 0;
            background: #eef2f5;
        }
        .dashboard {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            text-align: center;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            margin-bottom: 15px;
        }
        h2 {
            margin: 10px 0;
            color: #333;
        }
        p {
            margin: 8px 0;
            font-size: 16px;
            color: #555;
        }
        .actions {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .btn {
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s ease;
            font-size: 15px;
        }
        .btn-logout {
            background-color: #dc3545;
            color: white;
        }
        .btn-logout:hover {
            background-color: #c82333;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }

        @media (max-width: 600px) {
            .dashboard {
                padding: 20px;
            }
            .profile-img {
                width: 90px;
                height: 90px;
            }
            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <img class="profile-img" src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo">
        <h2><?= htmlspecialchars($student['name']) ?></h2>
        <p><strong>Enrollment ID:</strong> <?= htmlspecialchars($student['enrollment_id']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($student['contact_number']) ?></p>

        <div class="actions">
            <a href="../test.php" class="btn btn-back">← Back</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </div>
</body>
</html>
