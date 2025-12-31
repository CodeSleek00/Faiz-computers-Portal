<?php
session_start();

/* ================= LOGIN CHECK ================= */
if (
    !isset($_SESSION['enrollment_id']) ||
    !isset($_SESSION['student_table']) ||
    !isset($_SESSION['student_id']) ||
    !isset($_SESSION['student_name'])   // 👈 name session
) {
    header("Location: login-system/login.php");
    exit;
}

$student_name = $_SESSION['student_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body{
            margin:0;
            font-family: Arial, Helvetica, sans-serif;
            background:#f4f6f9;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
        }
        .card{
            background:#fff;
            padding:30px 40px;
            border-radius:10px;
            box-shadow:0 10px 25px rgba(0,0,0,0.1);
            text-align:center;
        }
        .card h2{
            color:#28a745;
            margin-bottom:10px;
        }
        .card p{
            color:#333;
            font-size:16px;
            margin-top:5px;
        }
        .name{
            font-weight:bold;
            color:#007bff;
        }
        .btn{
            display:inline-block;
            margin-top:20px;
            padding:10px 18px;
            background:#007bff;
            color:#fff;
            text-decoration:none;
            border-radius:6px;
            font-size:14px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>✅ Login Successful</h2>
    <p>Welcome, <span class="name"><?php echo htmlspecialchars($student_name); ?></span></p>

    <a href="student_dashboard.php" class="btn">Go to Dashboard</a>
</div>

</body>
</html>
