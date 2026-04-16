<?php
include '../database_connection/db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance</title>

<style>
body{
    font-family: Arial;
    background:#f4f6f9;
    padding:20px;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:8px;
    max-width:1100px;
    margin:auto;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
th,td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}
img{
    width:60px;
    height:60px;
    border-radius:50%;
    object-fit:cover;
}
button{
    padding:10px 20px;
    background:#2ecc71;
    color:#fff;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
input{
    padding:8px;
    margin:5px;
}
</style>

</head>

<body>

<div class="card">

<h2>📋 All Students Attendance</h2>

<form method="GET">
    <label>Select Date:</label>
    <input type="date" name="date" required>
    <button type="submit">Load Students</button>
</form>

<?php
if(isset($_GET['date'])):

$date = $_GET['date'];

/*
========================================
 FETCH ALL STUDENTS WITH PHOTO
========================================
*/
$students = $conn->query("
SELECT 
    student_id AS id,
    'students' AS table_name,
    name,
    enrollment_id,
    photo
FROM students

UNION ALL

SELECT 
    id AS id,
    'students26' AS table_name,
    name,
    enrollment_id,
    photo
FROM students26

ORDER BY name
");
?>

<form action="save_attendance.php" method="POST">

<input type="hidden" name="date" value="<?= $date ?>">

<table>
<tr>
    <th>Photo</th>
    <th>Enrollment ID</th>
    <th>Name</th>
    <th>Status</th>
</tr>

<?php if($students->num_rows > 0): ?>
<?php while($st = $students->fetch_assoc()): ?>
<tr>
    <td>
        <img src="../uploads/<?= htmlspecialchars($st['photo']) ?>" alt="photo">
    </td>
    <td><?= htmlspecialchars($st['enrollment_id']) ?></td>
    <td><?= htmlspecialchars($st['name']) ?></td>
    <td>
        <select name="status[<?= $st['table_name'] ?>][<?= $st['id'] ?>]">
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>
    </td>
</tr>
<?php endwhile; ?>

<?php else: ?>
<tr>
    <td colspan="4">No students found</td>
</tr>
<?php endif; ?>

</table>

<br>
<button type="submit">Save Attendance</button>

</form>

<?php endif; ?>

</div>

</body>
</html>