<?php
include '../database_connection/db_connect.php';

$data = $conn->query("
SELECT a.attendance_date, b.batch_name, s.name, a.status
FROM attendance a
JOIN batches b ON a.batch_id=b.id
JOIN students s ON a.student_id=s.id
ORDER BY a.attendance_date DESC
");
?>

<table border="1" cellpadding="10">
<tr>
<th>Date</th><th>Batch</th><th>Student</th><th>Status</th>
</tr>
<?php while($row=$data->fetch_assoc()){ ?>
<tr>
<td><?= $row['attendance_date'] ?></td>
<td><?= $row['batch_name'] ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['status'] ?></td>
</tr>
<?php } ?>
</table>
