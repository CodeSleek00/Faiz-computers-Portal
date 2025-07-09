<?php
session_start();
include '../database_connection/db_connect.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['enrollment_id'])) {
    header("Location:../test.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enrollment_id = $_POST['enrollment_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE enrollment_id = ?");
    $stmt->bind_param("s", $enrollment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            // Login success - store session
            $_SESSION['enrollment_id'] = $row['enrollment_id'];
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['name'] = $row['name'];

            header("Location: ../test.php");
            exit;
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ Invalid enrollment ID.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --error-color: #ff3333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .left-side {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            z-index: 10;
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .illustration {
            max-width: 80%;
            height: auto;
            margin-bottom: 2rem;
            animation: float 6s ease-in-out infinite;
        }
        
        .left-content {
            text-align: center;
            color: white;
            z-index: 2;
        }
        
        .left-content h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .left-content p {
            opacity: 0.9;
            max-width: 400px;
        }
        
        .right-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-box {
            background: #fff;
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .login-box h2 {
            text-align: center;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: border 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .btn {
            width: 100%;
            padding: 0.8rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: var(--secondary-color);
        }
        
        .error {
            color: var(--error-color);
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .decoration-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .circle-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }
        
        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -50px;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .left-side {
                padding: 1.5rem;
                min-height: 40vh;
            }
            
            .left-content h1 {
                font-size: 1.5rem;
            }
            
            .illustration {
                max-width: 60%;
            }
            
            .right-side {
                padding: 1.5rem;
            }
            
            .login-box {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .left-side {
                min-height: 30vh;
            }
            
            .illustration {
                max-width: 70%;
            }
            
            .login-box {
                padding: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left Side with Illustration -->
        <div class="left-side">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            
            <img src="login.webp" alt="Student Learning" class="illustration">
            
            <div class="left-content">
                <h1>Welcome Back!</h1>
                <p>Login to access your courses, assignments, and learning resources.</p>
            </div>
        </div>
        
        <!-- Right Side with Login Form -->
        <div class="right-side">
            <div class="login-box">
                <h2>Student Login</h2>

                <?php if ($error): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="enrollment_id">Enrollment ID</label>
                        <input type="text" id="enrollment_id" name="enrollment_id" class="form-control" placeholder="Enter your enrollment ID" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>