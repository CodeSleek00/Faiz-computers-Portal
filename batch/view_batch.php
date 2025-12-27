<?php
include '../database_connection/db_connect.php';

$batch_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch batch info
$batch_res = $conn->query("SELECT * FROM batches WHERE batch_id = $batch_id");
$batch = $batch_res->fetch_assoc();

// Fetch students in this batch
$students = $conn->query("
    SELECT sb.student_id, sb.student_table,
           COALESCE(s.name, s26.name) AS name,
           COALESCE(s.enrollment_id, s26.enrollment_id) AS enrollment_id,
           COALESCE(s.course, s26.course) AS course
    FROM student_batches sb
    LEFT JOIN students s ON sb.student_id = s.student_id AND sb.student_table = 'students'
    LEFT JOIN students26 s26 ON sb.student_id = s26.id AND sb.student_table = 'students26'
    WHERE sb.batch_id = $batch_id
    ORDER BY name ASC
");
?>

<h1>Batch: <?= htmlspecialchars($batch['batch_name']) ?></h1>
<h3>Timing: <?= htmlspecialchars($batch['timing']) ?></h3>

<h2>Students in this Batch:</h2>
<?php if ($students->num_rows > 0): ?>
    <ul>
        <?php while ($stu = $students->fetch_assoc()): ?>
            <li><?= htmlspecialchars($stu['name']) ?> (<?= htmlspecialchars($stu['enrollment_id']) ?> - <?= htmlspecialchars($stu['course']) ?>)</li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No students assigned to this batch yet.</p>
<?php endif; ?>
