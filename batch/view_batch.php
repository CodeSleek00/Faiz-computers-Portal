<?php
include '../database_connection/db_connect.php';

$batches = $conn->query("SELECT * FROM batches");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Batches</title>
    <style>
        body { font-family: Poppins, sans-serif; background: #eef2f5; padding: 30px; }
        .batch { background: white; margin-bottom: 20px; padding: 20px; border-radius: 10px; box-shadow: 0 5px 10px rgba(0,0,0,0.05); }
        h3 { margin-top: 0; }
        ul { padding-left: 20px; }
        a { color: #007bff; text-decoration: none; margin-right: 15px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>All Batches</h2>

<?php while ($batch = $batches->fetch_assoc()) { ?>
    <div class="batch">
        <h3><?= $batch['batch_name'] ?> (<?= $batch['timing'] ?>)</h3>
        <strong>Students:</strong>
        <ul>
        <?php
            $bid = $batch['batch_id'];
            $students = $conn->query("SELECT s.name, s.enrollment_id FROM student_batches sb JOIN students s ON sb.student_id = s.student_id WHERE sb.batch_id = $bid");
            while ($student = $students->fetch_assoc()) {
                echo "<li>{$student['name']} ({$student['enrollment_id']})</li>";
            }
        ?>
        </ul>
        <a href="edit_batch.php?id=<?= $batch['batch_id'] ?>">✏️ Edit</a>
        <a href="delete_batch.php?id=<?= $batch['batch_id'] ?>" onclick="return confirm('Delete this batch?')">🗑 Delete</a>
    </div>
<?php } ?>

</body>
</html>
