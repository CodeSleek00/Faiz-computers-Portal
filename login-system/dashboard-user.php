<?php
session_start();

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 40px;
            background: #eef2f5;
        }
        .card {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
            text-align: center;
        }
        h2 {
            margin-bottom: 10px;
        }
        .logout {
            margin-top: 25px;
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
        }
        .logout:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
        <p>Your Enrollment ID: <?= $_SESSION['enrollment_id'] ?></p>
        <a class="logout" href="logout.php">Logout</a>
    </div>
</body>
</html>
