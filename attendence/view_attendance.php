<?php
include '../database_connection/db_connect.php';

$data = $conn->query("
SELECT
    a.attendance_date,
    b.batch_name,
    CASE
        WHEN a.student_table = 'students' THEN s.name
        WHEN a.student_table = 'students26' THEN s26.name
        ELSE 'Unknown'
    END AS student_name,
    a.status
FROM attendance a
LEFT JOIN batches b
    ON a.batch_id = b.batch_id
LEFT JOIN students s
    ON a.student_id = s.student_id
   AND a.student_table = 'students'
LEFT JOIN students26 s26
    ON a.student_id = s26.id
   AND a.student_table = 'students26'
ORDER BY a.attendance_date DESC
");

if (!$data) {
    die("Attendance query failed: " . $conn->error);
}
?>

<table border="1" cellpadding="10">
<tr>
<th>Date</th><th>Batch</th><th>Student</th><th>Status</th>
</tr>
<?php while($row=$data->fetch_assoc()){ ?>
<tr>
<td><?= $row['attendance_date'] ?></td>
<td><?= $row['batch_name'] ?></td>
<td><?= htmlspecialchars($row['student_name']) ?></td>
<td><?= $row['status'] ?></td>
</tr>
<?php } ?>
</table>
