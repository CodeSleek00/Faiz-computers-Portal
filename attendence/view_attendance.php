<?php
include '../database_connection/db_connect.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
<title>View Attendance</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:#f4f6f9;
    padding:20px;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    max-width:1300px;
    margin:auto;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}
h2{text-align:center;margin-bottom:15px;}
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:10px;
    border-bottom:1px solid #eee;
    text-align:center;
}
th{
    background:#2c3e50;
    color:white;
}
img{
    width:45px;
    height:45px;
    border-radius:50%;
    object-fit:cover;
}
.present{color:green;font-weight:600;}
.absent{color:red;font-weight:600;}
</style>

</head>
<body>

<div class="card">

<h2>Attendance Records</h2>

<div class="top-bar">
    <div>📅 Selected Date: <b><?= date('d M Y', strtotime($date)) ?></b></div>

    <form method="GET">
        <input type="date" name="date" value="<?= $date ?>">
        <button>View</button>
    </form>
</div>

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
    a.status,
    s.name,
    s.enrollment_id,
    s.course,
    s.photo
FROM attendance a
JOIN students s 
ON a.student_id = s.student_id 
WHERE a.table_name='students' AND a.date='$date'

UNION ALL

SELECT 
    a.status,
    s26.name,
    s26.enrollment_id,
    s26.course,
    s26.photo
FROM attendance a
JOIN students26 s26 
ON a.student_id = s26.id 
WHERE a.table_name='students26' AND a.date='$date'
";

$result = $conn->query($sql);

if($result->num_rows == 0){
    echo "<tr><td colspan='5'>No attendance found for this date</td></tr>";
}

while($row = $result->fetch_assoc()){
?>

<tr>

<td>
<?php if(!empty($row['photo'])){ ?>
<img src="../uploads/<?= $row['photo'] ?>">
<?php } else { ?>
<img src="https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>">
<?php } ?>
</td>

<td><?= $row['name'] ?></td>
<td><?= $row['enrollment_id'] ?></td>
<td><?= $row['course'] ?></td>

<td class="<?= strtolower($row['status']) ?>">
<?= $row['status'] ?>
</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>