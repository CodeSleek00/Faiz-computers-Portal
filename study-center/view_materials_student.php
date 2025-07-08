<?php
include '../database_connection/db_connect.php';
session_start();

$enrollment = $_SESSION['enrollment_id'] ?? null;
if (!$enrollment) die("Please login.");

$student = $conn->query("SELECT student_id FROM students WHERE enrollment_id = '$enrollment'")->fetch_assoc();
$student_id = $student['student_id'];

$query = "
    SELECT DISTINCT m.*
    FROM study_materials m
    JOIN study_material_targets t ON m.id = t.material_id
    WHERE t.student_id = $student_id
       OR t.batch_id IN (SELECT batch_id FROM student_batches WHERE student_id = $student_id)
    ORDER BY m.uploaded_at DESC
";
$data = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Study Materials</title>
    <style>
        body { font-family: Poppins, sans-serif; background: #f0f2f5; padding: 40px; }
        .container { max-width: 800px; margin: auto; }
        .material { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 6px 10px rgba(0,0,0,0.05); }
        .material h4 { margin: 0 0 10px; }
        a.download { display: inline-block; margin-top: 10px; color: #007bff; text-decoration: none; font-weight: 500; }
        a.download:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>📘 My Study Materials</h2>

    <?php if ($data->num_rows > 0): ?>
        <?php while ($row = $data->fetch_assoc()): ?>
            <div class="material">
                <h4><?= htmlspecialchars($row['title']) ?></h4>
                <a class="download" href="uploads/study_materials/<?= $row['file_name'] ?>" target="_blank">📥 Download PDF</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No study material assigned yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
