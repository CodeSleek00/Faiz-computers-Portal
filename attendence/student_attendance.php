<?php
session_start();
include '../database_connection/db_connect.php';

$enrollment_id = $_SESSION['enrollment_id'];

$student = null;
$table_name = "";

/* find student */
$res = $conn->query("SELECT * FROM students WHERE enrollment_id='$enrollment_id'");
if($res->num_rows){
    $student = $res->fetch_assoc();
    $student_id = $student['student_id'];
    $table_name = "students";
}else{
    $res = $conn->query("SELECT * FROM students26 WHERE enrollment_id='$enrollment_id'");
    $student = $res->fetch_assoc();
    $student_id = $student['id'];
    $table_name = "students26";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Attendance</title>
<style>
body{font-family:Arial;background:#f4f6f9;padding:20px;}
.card{background:#fff;padding:20px;border-radius:10px;max-width:900px;margin:auto;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border-bottom:1px solid #ddd;text-align:center;}
th{background:#2c3e50;color:white;}
.present{color:green;font-weight:bold;}
.absent{color:red;font-weight:bold;}
</style>
</head>
<body>

<div class="card">
<h2>My Attendance</h2>

<table>
<tr>
<th>Date</th>
<th>Status</th>
</tr>

<?php
$res = $conn->query("
SELECT * FROM attendance 
WHERE student_id='$student_id' AND table_name='$table_name'
ORDER BY date DESC
");

while($row = $res->fetch_assoc()){
?>

<tr>
<td><?= $row['date'] ?></td>
<td class="<?= strtolower($row['status']) ?>">
<?= $row['status'] ?>
</td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>