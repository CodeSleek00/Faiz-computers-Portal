<?php
session_start();
include '../../database_connection/db_connect.php';

// ✅ Login check
if (!isset($_SESSION['enrollment_id'])) {
    header("Location: login.php");
    exit;
}

$student_id    = $_SESSION['student_id'];
$student_table = $_SESSION['student_table']; // 'students' or 'students26'

// Fetch student details
$stmt = $conn->prepare("SELECT * FROM $student_table WHERE " . ($student_table == 'students' ? "student_id" : "id") . "=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Fetch assigned videos (individual + batch)
$sql = "SELECT v.* 
        FROM videos v
        JOIN video_assignments va ON v.video_id = va.video_id
        LEFT JOIN student_batches sb 
               ON va.batch_id = sb.batch_id 
               AND sb.student_id=? AND sb.student_table=?
        WHERE (va.student_id=? AND va.student_table=?) 
           OR (va.batch_id IS NOT NULL AND sb.id IS NOT NULL)
        ORDER BY v.upload_date DESC";

$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("isis",$student_id,$student_table,$student_id,$student_table);
$stmt2->execute();
$videos = $stmt2->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($student['name']) ?> - Videos</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #4361ee;
        }
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill,minmax(300px,1fr));
            gap: 1.5rem;
        }
        .video-card {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .video-card h3 {
            margin: 0 0 0.5rem;
            color: #3f37c9;
        }
        .video-card p {
            font-size: 0.9rem;
            color: #555;
        }
        .logout-btn {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: #ff3333;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-btn">Logout</a>
        <h1>Welcome, <?= htmlspecialchars($student['name']) ?></h1>

        <?php if ($videos->num_rows > 0): ?>
            <div class="video-grid">
                <?php while($v = $videos->fetch_assoc()): ?>
                    <div class="video-card">
                        <h3><?= htmlspecialchars($v['title']) ?></h3>
                        <video width="100%" controls>
                            <source src="../videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
                        </video>
                        <p><?= htmlspecialchars($v['description']) ?></p>
                        <p>Uploaded on: <?= $v['upload_date'] ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center; margin-top:2rem;">No videos assigned to you yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
