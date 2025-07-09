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
    <style>* {
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f9fbff;
    margin: 0;
    padding: 15px;
}

.container {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    width: 100%;
    margin: auto;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 15px;
}

.header h2 {
    font-size: 20px;
    color: #333;
    margin: 0;
}

.edit-btn {
    background: #00bcd4;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: background 0.3s ease;
}

.edit-btn:hover {
    background: #0097a7;
}

.profile-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.profile-photo {
    text-align: center;
    margin-bottom: 15px;
}

.profile-photo img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #00bcd4;
}

.profile-photo h3 {
    margin: 10px 0 0;
    font-size: 18px;
}

.profile-info {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    font-size: 14px;
}

.profile-info label {
    color: #999;
    font-weight: 500;
    font-size: 13px;
}

.profile-info div {
    color: #333;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.status {
    background: #e0f7fa;
    color: #00796b;
    padding: 4px 8px;
    display: inline-block;
    border-radius: 4px;
    font-weight: 500;
    font-size: 13px;
}

.tabs {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.tab {
    padding: 8px 15px;
    background: #f1f1f1;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    color: #333;
    font-size: 13px;
    white-space: nowrap;
}

.tab.active {
    background: #00bcd4;
    color: white;
}

/* Small devices (landscape phones, 576px and up) */
@media (min-width: 576px) {
    .container {
        padding: 25px;
    }
    
    .profile-info {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
}

/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
    .container {
        max-width: 700px;
        padding: 30px 40px;
    }
    
    .profile-content {
        flex-direction: row;
        gap: 30px;
    }
    
    .profile-photo {
        margin-bottom: 0;
    }
    
    .profile-photo img {
        width: 140px;
        height: 140px;
    }
    
    .profile-info {
        font-size: 15px;
    }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
    .container {
        max-width: 900px;
    }
    
    .profile-photo img {
        width: 160px;
        height: 160px;
    }
    
    .profile-info {
        font-size: 16px;
    }
}</style>
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
