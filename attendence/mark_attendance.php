<?php
include '../database_connection/db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance</title>
<style>
body{font-family:Arial;background:#f4f6f9;padding:20px;}
.card{background:#fff;padding:20px;border-radius:10px;max-width:1200px;margin:auto;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border-bottom:1px solid #ddd;text-align:center;}
th{background:#2c3e50;color:white;}
.btn{padding:10px 20px;background:#27ae60;color:white;border:none;border-radius:5px;}
</style>
</head>
<body>

<div class="card">
<h2>Mark Attendance</h2>

<form method="POST" action="save_attendance.php">

<input type="date" name="date" required value="<?= date('Y-m-d') ?>">

<table>
<tr>
<th>Name</th>
<th>Enrollment</th>
<th>Status</th>
</tr>

<?php

$sql = "
SELECT 
    s.student_id as id,
    s.name,
    s.enrollment_id,
    'students' as table_name
FROM students s
JOIN student_status ss 
ON s.student_id = ss.student_id 
WHERE ss.status='Continue' AND ss.table_name='students'

UNION ALL

SELECT 
    s26.id as id,
    s26.name,
    s26.enrollment_id,
    'students26' as table_name
FROM students26 s26
JOIN student_status ss 
ON s26.id = ss.student_id 
WHERE ss.status='Continue' AND ss.table_name='students26'
";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
?>

<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['enrollment_id'] ?></td>

<td>
<input type="hidden" name="student_id[]" value="<?= $row['id'] ?>">
<input type="hidden" name="table_name[]" value="<?= $row['table_name'] ?>">

<select name="status[]">
<option value="Present">Present</option>
<option value="Absent">Absent</option>
</select>
</td>

</tr>

<?php } ?>

</table>

<br>
<button class="btn">Save Attendance</button>

</form>
</div>

</body>
</html>