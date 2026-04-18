<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database_connection/db_connect.php';
?>


<!DOCTYPE html>
<html>
<head>
<title>Bulk Student Status Update</title>

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    padding:20px;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:10px;
    max-width:1300px;
    margin:auto;
}
h2{
    text-align:center;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th, td{
    padding:10px;
    border-bottom:1px solid #ddd;
    text-align:center;
}
th{
    background:#2c3e50;
    color:white;
}
select{
    padding:5px;
}
.btn{
    padding:10px 20px;
    background:#27ae60;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    display:block;
    margin:20px auto;
}
</style>

</head>
<body>

<div class="card">
<h2>Bulk Student Status Declaration</h2>

<form method="POST" action="save_bulk_status.php">

<table>
<tr>
<th>Name</th>
<th>Enrollment</th>
<th>Course</th>
<th>Status</th>
</tr>

<?php
$sql = "
SELECT 
    s.id,
    s.name,
    s.enrollment_id,
    s.course,
    COALESCE(ss.status, 'Continue') as status,
    'students' as table_name

FROM students s
LEFT JOIN student_status ss 
ON s.id = ss.student_id AND ss.table_name='students'

UNION ALL

SELECT 
    s26.id,
    s26.name,
    s26.enrollment_id,
    s26.course,
    COALESCE(ss.status, 'Continue') as status,
    'students26' as table_name

FROM students26 s26
LEFT JOIN student_status ss 
ON s26.id = ss.student_id AND ss.table_name='students26'
";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
?>

<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['enrollment_id'] ?></td>
<td><?= $row['course'] ?></td>

<td>
<input type="hidden" name="student_id[]" value="<?= $row['id'] ?>">
<input type="hidden" name="table_name[]" value="<?= $row['table_name'] ?>">

<select name="status[]">
<option <?= $row['status']=='Continue'?'selected':'' ?>>Continue</option>
<option <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
<option <?= $row['status']=='Dropped'?'selected':'' ?>>Dropped</option>
<option <?= $row['status']=='Hold'?'selected':'' ?>>Hold</option>
</select>
</td>

</tr>

<?php } ?>

</table>

<button class="btn">Save All Status</button>

</form>

</div>

</body>
</html>