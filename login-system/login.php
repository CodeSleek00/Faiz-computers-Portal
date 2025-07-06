<?php
session_start();
include '../database_connection/db_connect.php';

// If already logged in, redirect
if (isset($_SESSION['enrollment_id'])) {
    header("Location: dashboard-user.php");
    exit;
}

$error = "";

// On form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enrollment_id = $_POST['enrollment_id'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE enrollment_id = ?");
    $stmt->bind_param("s", $enrollment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['enrollment_id'] = $row['enrollment_id'];
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['name'] = $row['name'];

            header("Location: dashboard-user.php");
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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Student Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    :root {
      --primary: #3b82f6;
      --secondary: #2563eb;
      --bg: #f9fafb;
      --dark: #1f2937;
      --error: #dc2626;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: "Poppins", sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }

    .container {
      display: flex;
      width: 100%;
      max-width: 1000px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .left {
      flex: 1;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .left img {
      width: 100%;
      max-width: 350px;
      height: auto;
      object-fit: contain;
    }

    .right {
      flex: 1;
      padding: 2.5rem 2rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .back-btn {
      position: absolute;
      top: 15px;
      left: 15px;
      text-decoration: none;
      color: white;
      background: var(--secondary);
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 0.9rem;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
      margin: 0 auto;
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      color: var(--dark);
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    label {
      font-weight: 500;
      display: block;
      margin-bottom: 0.5rem;
    }

    .form-control {
      width: 100%;
      padding: 0.8rem 1rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper i {
      position: absolute;
      top: 50%;
      right: 15px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #666;
    }

    .btn {
      width: 100%;
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.9rem;
      font-size: 1rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: var(--secondary);
    }

    .error {
      background: #fee2e2;
      color: var(--error);
      padding: 0.75rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .left {
        display: none;
      }

      .right {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="left">
      <img src="login.webp" alt="Login Illustration">
    </div>

    <div class="right">
      <div class="login-box">
        <h2>Student Login</h2>

        <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="form-group">
            <label for="enrollment_id">Enrollment ID</label>
            <input type="text" id="enrollment_id" name="enrollment_id" class="form-control" placeholder="Enter enrollment ID" required>
          </div>

          <div class="form-group password-wrapper">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            <i class="fas fa-eye" id="togglePassword"></i>
          </div>

          <button type="submit" class="btn">Login</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const togglePassword = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
      const type = password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);
      this.classList.toggle("fa-eye-slash");
    });
  </script>
</body>
</html>
