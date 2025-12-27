<?php
include '../database_connection/db_connect.php';

$res = $conn->query("
    SELECT sb.*, 
           COALESCE(s.name, s26.name) AS student_name, 
           COALESCE(s.enrollment_id, s26.enrollment_id) AS enrollment_id,
           COALESCE(s.course, s26.course) AS course,
           b.batch_name, b.timing
    FROM student_batches sb
    LEFT JOIN students s ON sb.student_id = s.student_id AND sb.student_table='students'
    LEFT JOIN students26 s26 ON sb.student_id = s26.id AND sb.student_table='students26'
    LEFT JOIN batches b ON sb.batch_id = b.batch_id
    ORDER BY b.batch_name, student_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Batches</title>
    <style>
        table { border-collapse: collapse; width:100%; }
        th, td { padding:0.5rem; border:1px solid #ccc; text-align:left; }
    </style>
</head>
<body>
<h1>All Batches</h1>

<table>
    <thead>
        <tr>
            <th>Batch Name</th>
            <th>Timing</th>
            <th>Student Name</th>
            <th>Enrollment ID</th>
            <th>Course</th>
            <th>Source Table</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $res->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['batch_name']) ?></td>
                <td><?= htmlspecialchars($row['timing']) ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><?= htmlspecialchars($row['student_table']) ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
</body>
</html>
