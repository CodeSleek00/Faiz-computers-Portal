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
    max-width:1400px;
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
    padding:6px;
}
.btn{
    padding:12px 25px;
    background:#27ae60;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    display:block;
    margin:20px auto;
    font-size:16px;
}
img{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}
.success{
    background:#2ecc71;
    color:white;
    padding:10px;
    text-align:center;
    border-radius:5px;
    margin-bottom:10px;
}
</style>

</head>
<body>

<div class="card">

<h2>Bulk Student Status Declaration</h2>

<?php if(isset($_GET['success'])){ ?>
<div class="success">✅ Status Updated Successfully</div>
<?php } ?>

<form method="POST" action="save_bulk_status.php">

<table>
<tr>
<th>Photo</th>
<th>Name</th>
<th>Enrollment</th>
<th>Course</th>
<th>Status</th>
</tr>

<?php

$sql = "
SELECT 
    s.student_id as id,
    s.photo,
    s.name,
    s.enrollment_id,
    s.course,
    COALESCE(ss.status, 'Continue') as status,
    'students' as table_name

FROM students s
LEFT JOIN student_status ss 
ON s.student_id = ss.student_id AND ss.table_name='students'

UNION ALL

SELECT 
    s26.id as id,
    s26.photo,
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

if(!$result){
    die("Query Error: " . $conn->error);
}

while($row = $result->fetch_assoc()){
?>

<tr>

<td>
<?php if(!empty($row['photo'])){ ?>
<img src="../uploads/<?= $row['photo'] ?>">
<?php } else { ?>
<img src="../uploads/default.png">
<?php } ?>
</td>

<td><?= $row['name'] ?></td>
<td><?= $row['enrollment_id'] ?></td>
<td><?= $row['course'] ?></td>

<td>
<input type="hidden" name="student_id[]" value="<?= $row['id'] ?>">
<input type="hidden" name="table_name[]" value="<?= $row['table_name'] ?>">

<select name="status[]">
<option value="Continue" <?= $row['status']=='Continue'?'selected':'' ?>>Continue</option>
<option value="Completed" <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
<option value="Dropped" <?= $row['status']=='Dropped'?'selected':'' ?>>Dropped</option>
<option value="Hold" <?= $row['status']=='Hold'?'selected':'' ?>>Hold</option>
</select>
</td>

</tr>

<?php } ?>

</table>

<button class="btn">💾 Save All Status</button>

</form>

</div>

</body>
</html>