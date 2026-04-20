<?php 
include '../database_connection/db_connect.php'; 
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance</title>

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
h2{
    text-align:center;
    margin-bottom:15px;
}
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    flex-wrap:wrap;
    gap:10px;
}
.date-box{
    font-weight:500;
}
.total-box{
    font-weight:600;
    color:#2980b9;
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
select{
    padding:5px;
    border-radius:5px;
}
.btn{
    padding:12px 25px;
    background:#27ae60;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    display:block;
    margin:20px auto;
    font-size:15px;
}
.present-count{
    font-size:12px;
    color:#27ae60;
    font-weight:500;
}
</style>

</head>
<body>

<div class="card">

<h2>Mark Attendance</h2>

<form method="POST" action="save_attendance.php">

<?php
$date = date('Y-m-d');

$sql = "
SELECT 
    s.student_id as id,
    s.photo,
    s.name,
    s.enrollment_id,
    s.course,
    'students' as table_name
FROM students s
JOIN student_status ss 
ON s.student_id = ss.student_id
WHERE ss.status='Continue' AND ss.table_name='students'

UNION ALL

SELECT 
    s26.id as id,
    s26.photo,
    s26.name,
    s26.enrollment_id,
    s26.course,
    'students26' as table_name
FROM students26 s26
JOIN student_status ss 
ON s26.id = ss.student_id
WHERE ss.status='Continue' AND ss.table_name='students26'
";

$result = $conn->query($sql);

/* ✅ TOTAL STUDENTS */
$total_students = $result->num_rows;
?>

<div class="top-bar">
    <div class="date-box">
        📅 Today: <?= date('d M Y') ?>
    </div>

    <div class="total-box">
        👨‍🎓 Total Students: <?= $total_students ?>
    </div>

    <div>
        Select Date:
        <input type="date" name="date" required value="<?= date('Y-m-d') ?>">
    </div>
</div>

<table>
<tr>
<th>Photo</th>
<th>Name</th>
<th>Enrollment</th>
<th>Course</th>
<th>Present Count</th>
<th>Status</th>
</tr>

<?php
while($row = $result->fetch_assoc()){
    $id = $row['id'];
    $table = $row['table_name'];

    // 🔥 Present Count
    $countRes = $conn->query("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE student_id='$id' AND table_name='$table' AND status='Present'
    ");
    $count = $countRes->fetch_assoc()['total'];

    // 🔥 Already marked today
    $checkRes = $conn->query("
    SELECT status FROM attendance 
    WHERE student_id='$id' AND table_name='$table' AND date='$date'
    ");

    $selectedStatus = "Absent";

    if($checkRes->num_rows > 0){
        $selectedStatus = $checkRes->fetch_assoc()['status'];
    }
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

<td class="present-count">
<?= $count ?> Days
</td>

<td>
<input type="hidden" name="student_id[]" value="<?= $id ?>">
<input type="hidden" name="table_name[]" value="<?= $table ?>">

<select name="status[]">
<option value="Present" <?= $selectedStatus=='Present'?'selected':'' ?>>Present</option>
<option value="Absent" <?= $selectedStatus=='Absent'?'selected':'' ?>>Absent</option>
</select>
</td>

</tr>

<?php } ?>

</table>

<button class="btn">💾 Save Attendance</button>

</form>

</div>

</body>
</html>