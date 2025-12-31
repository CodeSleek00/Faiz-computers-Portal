<?php
session_start();
require_once 'database_connection/db_connect.php';

// LOGIN CHECK
if (!isset($_SESSION['enrollment_id'], $_SESSION['student_table'], $_SESSION['student_id'])) {
    header("Location: login-system/login.php");
    exit;
}

// Student info from session
$student_id    = $_SESSION['student_id'];
$enrollment_id = $_SESSION['enrollment_id'];
$student_table = $_SESSION['student_table'];

// Fetch student details
$stmt = $conn->prepare("SELECT name, photo, batch_id, course FROM $student_table WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#f4f4f4; }
        .container { display: flex; height: 100vh; }
        /* Left Sidebar */
        .sidebar { width: 220px; background:#2c3e50; color:#ecf0f1; padding:20px 0; display:flex; flex-direction:column; }
        .sidebar a { color:#ecf0f1; padding:12px 20px; text-decoration:none; transition:0.3s; }
        .sidebar a:hover, .sidebar a.active { background:#34495e; }
        /* Main Content */
        .main { flex:1; padding:20px; overflow-y:auto; background:#ecf0f1; }
        /* Right Sidebar */
        .rightbar { width: 250px; background:#bdc3c7; padding:20px; }
        .profile img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
        .profile h3, .profile p { margin:10px 0; }
        .flex-container { display:flex; flex:1; }
    </style>
</head>
<body>

<div class="container">
    <!-- LEFT NAVBAR -->
    <div class="sidebar">
        <h2 style="text-align:center; margin-bottom:20px;">Portal Menu</h2>
        <a href="?page=dashboard" class="<?=(!isset($_GET['page']) || $_GET['page']=='dashboard')?'active':''?>">Dashboard</a>
        <a href="?page=attendance" class="<?=($_GET['page']=='attendance')?'active':''?>">Attendance</a>
        <a href="?page=assignments" class="<?=($_GET['page']=='assignments')?'active':''?>">Assignments</a>
        <a href="?page=exams" class="<?=($_GET['page']=='exams')?'active':''?>">Exams</a>
        <a href="?page=study_material" class="<?=($_GET['page']=='study_material')?'active':''?>">Study Material</a>
        <a href="?page=fee" class="<?=($_GET['page']=='fee')?'active':''?>">Fee & Receipts</a>
        <a href="?page=profile" class="<?=($_GET['page']=='profile')?'active':''?>">Profile</a>
        <a href="login-system/logout.php">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <?php
        $page = $_GET['page'] ?? 'dashboard';
        switch($page){
            case 'attendance':
                include 'student_pages/attendance.php';
                break;
            case 'assignments':
                include 'student_pages/assignments.php';
                break;
            case 'exams':
                include 'student_pages/exams.php';
                break;
            case 'study_material':
                include 'student_pages/study_material.php';
                break;
            case 'fee':
                include 'student_pages/fee.php';
                break;
            case 'profile':
                include 'student_pages/profile.php';
                break;
            default:
                echo "<h2>Welcome, {$student['name']}</h2>";
                echo "<p>Select a menu item from the left to view details.</p>";
        }
        ?>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="rightbar">
        <div class="profile">
            <img src="uploads/student_photos/<?=$student['photo']?>" alt="Student Photo">
            <h3><?=$student['name']?></h3>
            <p><strong>Enrollment:</strong> <?=$enrollment_id?></p>
            <p><strong>Batch ID:</strong> <?=$student['batch_id']?></p>
            <p><strong>Course:</strong> <?=$student['course']?></p>
        </div>
    </div>
</div>

</body>
</html>
