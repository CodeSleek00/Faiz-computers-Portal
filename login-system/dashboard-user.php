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
    <title>Student Profile | <?= htmlspecialchars($student['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f9fbff;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            padding: 30px 40px;
            max-width: 1000px;
            margin: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header h2 {
            font-size: 24px;
            color: #333;
        }

        .edit-btn {
            background: #00bcd4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .edit-btn:hover {
            background: #0097a7;
        }

        .profile-content {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .profile-photo {
            flex: 1;
            text-align: center;
        }

        .profile-photo img {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #00bcd4;
        }

        .profile-info {
            flex: 2;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 40px;
            font-size: 16px;
        }

        .profile-info label {
            color: #999;
            font-weight: 500;
        }

        .profile-info div {
            color: #333;
            margin-bottom: 10px;
        }

        .status {
            background: #e0f7fa;
            color: #00796b;
            padding: 5px 10px;
            display: inline-block;
            border-radius: 5px;
            font-weight: 500;
        }

        .tabs {
            margin-top: 30px;
            display: flex;
            gap: 20px;
        }

        .tab {
            padding: 10px 20px;
            background: #f1f1f1;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            color: #333;
        }

        .tab.active {
            background: #00bcd4;
            color: white;
        }

        @media(max-width: 768px) {
            .profile-content {
                flex-direction: column;
                align-items: center;
            }

            .profile-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Profile</h2>
        </div>

        <div class="profile-content">
            <div class="profile-photo">
                <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo">
                <h3><?= htmlspecialchars($student['name']) ?></h3>
            </div>

            <div class="profile-info">
                <div>
                    <label>Student ID</label><br>
                    <?= htmlspecialchars($student['enrollment_id']) ?>
                </div>
                <div>
                    <label>Course</label><br>
                    <?= htmlspecialchars($student['course']) ?>
                </div>
                
                <div>
                    <label>Contact Number</label><br>
                    <?= htmlspecialchars($student['contact_number']) ?>
                </div>
                <div>
                    <label>Address</label><br>
                    <?= htmlspecialchars($student['address']) ?>
                </div>
                <div>
                    <label>Status</label><br>
                    <span class="status">Active</span>
                </div>
            </div>
        </div>

       

</body>
</html>
