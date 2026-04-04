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
<div class="reader">
    <h1>
        this is a reader page where im checking about the term 
        and condition of the admission form and also checking about the signature and also checking about the photo and also checking about the name and also checking about the batch id and also checking about the course and also checking about the enrollment id and also checking about the student id and also checking about the student table and also checking about the database connection and also checking about the session and also checking about the login check and also checking about the student info from session and also checking about the fetch student details and also checking about the prepare statement and also checking about the bind param and also checking about the execute statement and also checking about the get result and also checking about the fetch assoc and also checking about the html structure and also checking about the css styling and also checking about the print media query and also checking about the print button and also checking about the header section and also checking about the photo container and also checking about the section title and also checking about the table structure and also checking about the td styling and also checking about the signature table styling and also checking about the sign space styling and also checking about the footer note styling
    </h1>
    <div class="class">
        and this is a div where the class is defined and the class name is class
        and the data is shifting on the own of their own data is reasoning andthe thing is youre  makiing up a 
        story and the story is about the student and the student is a good student and the student is a hard working student and the student is a smart student and the student is a intelligent student and the student is a brilliant student and the student is a talented student and the student is a creative student and the student is a innovative student and the student is a passionate student and the student is a dedicated student and the student is a motivated student and the student is a ambitious student and the student is a confident student and the student is a responsible student and the student is a disciplined student and the student is a organized student and the student is a punctual student and the student is a respectful student and the student is a honest student and the student is a loyal student and the student is a trustworthy student and the student is a reliable student and the student is a supportive student and the student is a helpful student and the student is a friendly student and the student is a kind-hearted student
    </div>
    <div class="new">creating new object on their own to make it more 
        the qucik brown fox over the lazy dog 
        the qucik brown fox over the lazy dog 
        the 
        <script>
            a=Number(prompt("Enter the number"))
            b=Number(prompt("Enter the number"))
            if a % 2 ==0 {
                console.log("the number is even ")
            }
             else {
                console.log("the number is odd")
            }
            first $name=prompt("Enter your first name")
            last $name=prompt("Enter your last name")
        </script>
    </div>
</div>
</body>
</html>
