<?php
include '../database_connection/db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Status Manager</title>

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
    max-width:1200px;
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
.badge{
    padding:5px 10px;
    border-radius:20px;
    color:white;
}
</style>

</head>
<body>

<div class="card">
<h2>Student Status Management</h2>

<table>
<tr>
<th>Name</th>
<th>Enrollment</th>
<th>Course</th>
<th>Status</th>
<th>Update</th>
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
    
    $status = $row['status'];

    $color = match($status){
        'Continue' => '#3498db',
        'Completed' => '#2ecc71',
        'Dropped' => '#e74c3c',
        'Hold' => '#f39c12',
        default => '#7f8c8d'
    };
?>

<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['enrollment_id'] ?></td>
<td><?= $row['course'] ?></td>

<td>
<span class="badge" style="background:<?= $color ?>;">
<?= $status ?>
</span>
</td>

<td>
<form method="POST" action="update_status.php">
<input type="hidden" name="id" value="<?= $row['id'] ?>">
<input type="hidden" name="table_name" value="<?= $row['table_name'] ?>">

<select name="status" onchange="this.form.submit()">
<option <?= $status=='Continue'?'selected':'' ?>>Continue</option>
<option <?= $status=='Completed'?'selected':'' ?>>Completed</option>
<option <?= $status=='Dropped'?'selected':'' ?>>Dropped</option>
<option <?= $status=='Hold'?'selected':'' ?>>Hold</option>
</select>

</form>
</td>

</tr>

<?php } ?>

</table>
</div>

</body>
</html>