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
$stmt = $conn->prepare("SELECT name, photo FROM $table WHERE enrollment_id = ? LIMIT 1");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$student_name  = $student['name'];
$student_photo = $student['photo']; // 👈 photo ka path

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="welcome-text">
                <h2>Welcome back, <?= htmlspecialchars($student['name']) ?>!</h2>
                <p>Ready to continue your learning journey?</p>
            </div>
            <img src="uploads/<?= $student['photo'] ?>" alt="Profile" class="user-profile-img">
        </section>

</body>
</html>
