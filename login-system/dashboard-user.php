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
    <link rel="icon" type="image/png" href="image.png">
    <link rel="apple-touch-icon" href="image.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="container">

    <!-- ✅ BACK BUTTON FIX (HTML ONLY) -->
    <a href="../test.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
    </a>

    <div class="profile-card">

        <div class="profile-header">
            <h1><?= htmlspecialchars($student['name']) ?></h1>
            <p><?= htmlspecialchars($student['course']) ?></p>
        </div>

        <!-- PROFILE IMAGE -->
        <img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" 
             alt="Profile Photo" 
             class="profile-pic">

        <div class="profile-body">

            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-user-graduate"></i> Basic Information
                </h2>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Student ID</span>
                        <span class="info-value"><?= htmlspecialchars($student['enrollment_id']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?= htmlspecialchars($student['name']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Course</span>
                        <span class="info-value"><?= htmlspecialchars($student['course']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-active">
                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                            Active
                        </span>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-address-card"></i> Contact Details
                </h2>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Contact Number</span>
                        <span class="info-value"><?= htmlspecialchars($student['contact_number']) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Address</span>
                        <span class="info-value"><?= htmlspecialchars($student['address']) ?></span>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
