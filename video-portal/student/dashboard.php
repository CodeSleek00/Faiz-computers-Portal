<?php
session_start();
include '../../database_connection/db_connect.php';

if(!isset($_SESSION['enrollment_id'])){
    header("Location: ../login/login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_table = $_SESSION['student_table'];

// Fetch assigned sections
$sql = "SELECT DISTINCT s.section_id, s.title, s.description
        FROM sections s
        JOIN video_assignments va ON va.section_id = s.section_id
        LEFT JOIN student_batches sb ON va.batch_id = sb.batch_id
            AND sb.student_id=? AND sb.student_table=?
        WHERE (va.student_id=? AND va.student_table=?) OR (va.batch_id IS NOT NULL AND sb.id IS NOT NULL)
        ORDER BY s.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isis",$student_id,$student_table,$student_id,$student_table);
$stmt->execute();
$sections = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body{font-family:Arial;background:#f4f4f4;margin:0;padding:0;}
        h1{text-align:center;background:#4361ee;color:white;padding:15px;}
        .container{width:95%;margin:auto;overflow:hidden;}
        .section-card{background:white;padding:15px;margin:15px 0;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
        video{width:100%;margin-top:5px;border-radius:5px;}
    </style>
</head>
<body>
<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
<div class="container">
<?php while($sec=$sections->fetch_assoc()): ?>
    <div class="section-card">
        <h2><?= htmlspecialchars($sec['title']) ?></h2>
        <p><?= htmlspecialchars($sec['description']) ?></p>

        <?php
        $vidStmt = $conn->prepare("SELECT * FROM videos WHERE section_id=? ORDER BY upload_date ASC");
        $vidStmt->bind_param("i",$sec['section_id']);
        $vidStmt->execute();
        $videos = $vidStmt->get_result();
        ?>
        <?php while($v=$videos->fetch_assoc()): ?>
            <div>
                <h3><?= htmlspecialchars($v['title']) ?></h3>
                <video controls>
                    <source src="../admin/videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
                </video>
                <p><?= htmlspecialchars($v['description']) ?></p>
            </div>
        <?php endwhile; ?>
    </div>
<?php endwhile; ?>
</div>
</body>
</html>
