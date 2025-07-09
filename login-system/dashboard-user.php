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
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --dark: #1b263b;
            --light: #f8f9fa;
            --danger: #ef233c;
            --success: #4cc9f0;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: var(--dark);
        }
        
        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 30px;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -75px;
        }
        
        .profile-img-container {
            position: relative;
            margin-bottom: 20px;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .profile-img:hover {
            transform: scale(1.05);
        }
        
        .profile-verified {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--success);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }
        
        .student-name {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .student-id {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .dashboard-content {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .info-card {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: left;
            transition: transform 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
        }
        
        .info-card i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .info-card h3 {
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .info-card p {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .dashboard-actions {
            padding: 20px 30px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 15px;
            cursor: pointer;
            border: none;
            outline: none;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-back {
            background: #e9ecef;
            color: #495057;
        }
        
        .btn-back:hover {
            background: #dee2e6;
            transform: translateX(-3px);
        }
        
        .btn-logout {
            background: var(--danger);
            color: white;
        }
        
        .btn-logout:hover {
            background: #d90429;
            transform: translateX(3px);
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 20px;
            }
            
            .profile-img {
                width: 120px;
                height: 120px;
            }
            
            .student-name {
                font-size: 24px;
            }
            
            .dashboard-content {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .dashboard-actions {
                justify-content: center;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dashboard-container {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Student Dashboard</h1>
        </div>
        
        <div class="profile-container">
            <div class="profile-img-container">
                <img class="profile-img" src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="Profile Photo">
                <div class="profile-verified">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h2 class="student-name"><?= htmlspecialchars($student['name']) ?></h2>
            <span class="student-id">ID: <?= htmlspecialchars($student['enrollment_id']) ?></span>
        </div>
        
        <div class="dashboard-content">
            <div class="info-card">
                <i class="fas fa-graduation-cap"></i>
                <h3>Course</h3>
                <p><?= htmlspecialchars($student['course']) ?></p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Address</h3>
                <p><?= htmlspecialchars($student['address']) ?></p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-phone-alt"></i>
                <h3>Contact Number</h3>
                <p><?= htmlspecialchars($student['contact_number']) ?></p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p><?= htmlspecialchars($student['email'] ?? 'Not provided') ?></p>
            </div>
        </div>
        
        <div class="dashboard-actions">
            <a href="../test.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</body>
</html>