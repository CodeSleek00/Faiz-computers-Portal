
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

<h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>

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
        <div class="video-list">
            <?php while($v=$videos->fetch_assoc()): ?>
                <div class="video-card">
                    <h3><?= htmlspecialchars($v['title']) ?></h3>
                    <video width="100%" controls>
                        <source src="../admin/videos/<?= htmlspecialchars($v['filename']) ?>" type="video/mp4">
                    </video>
                    <p><?= htmlspecialchars($v['description']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endwhile; ?>
