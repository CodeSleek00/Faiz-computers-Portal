<?php
session_start();
include '../database_connection/db_connect.php';

$student_id = $_SESSION['student_id'];

$data = $conn->query("
SELECT attendance_date, status 
FROM attendance 
WHERE student_id=$student_id
ORDER BY attendance_date DESC
");
?>

<h2>📅 My Attendance</h2>
<table border="1" cellpadding="10">
<tr><th>Date</th><th>Status</th></tr>
<?php while($row=$data->fetch_assoc()){ ?>
<tr>
<td><?= $row['attendance_date'] ?></td>
<td><?= $row['status'] ?></td>
</tr>
<?php } ?>
</table>
