<?php
session_start();
require_once 'database_connection/db_connect.php';

/* ================= LOGIN CHECK ================= */
if (
    !isset($_SESSION['enrollment_id']) ||
    !isset($_SESSION['student_table']) ||
    !isset($_SESSION['student_id'])
) {
    header("Location: login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$table         = $_SESSION['student_table'];

/* ================= FETCH STUDENT NAME ================= */
$stmt = $conn->prepare("SELECT name FROM $table WHERE enrollment_id = ? LIMIT 1");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login-system/login.php");
    exit;
}

$student = $result->fetch_assoc();
$student_name = $student['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body{
            margin:0;
            font-family: Arial, sans-serif;
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
        h2{ color:#28a745; }
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
        }
    </style>
</head>
<body>

<div class="card">
    <h2>✅ Login Successful</h2>
    <p>Welcome, <span class="name"><?= htmlspecialchars($student_name) ?></span></p>

    <a href="student_dashboard.php" class="btn">Go to Dashboard</a>
</div>

</body>
</html>
