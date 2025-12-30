<?php
include '../database_connection/db_connect.php';

// Fetch batches
$batches = $conn->query("SELECT * FROM batches ORDER BY batch_name");
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
button{
    padding:10px 20px;
    background:#2ecc71;
    color:#fff;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="card">
<h2>📋 Batch Wise Attendance</h2>

<form method="GET">
    <label>Date:</label>
    <input type="date" name="date" required>

    <label>Batch:</label>
    <select name="batch_id" required>
        <option value="">Select Batch</option>
        <?php while($b = $batches->fetch_assoc()){ ?>
            <option value="<?= $b['batch_id'] ?>">
                <?= htmlspecialchars($b['batch_name']) ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Load Students</button>
</form>

<?php
if(isset($_GET['batch_id'], $_GET['date'])):

$batch_id = intval($_GET['batch_id']);
$date     = $_GET['date'];

/*
==================================================
 CORRECT JOIN (students.student_id & students26.id)
==================================================
*/
$students = $conn->query("
SELECT 
    sb.student_id,
    sb.student_table,

    CASE 
        WHEN sb.student_table = 'students' 
            THEN s.name
        WHEN sb.student_table = 'students26' 
            THEN s26.name
    END AS name,

    CASE 
        WHEN sb.student_table = 'students' 
            THEN s.enrollment_id
        WHEN sb.student_table = 'students26' 
            THEN s26.enrollment_id
    END AS enrollment_id

FROM student_batches sb

LEFT JOIN students s 
    ON sb.student_id = s.student_id 
   AND sb.student_table = 'students'

LEFT JOIN students26 s26 
    ON sb.student_id = s26.id 
   AND sb.student_table = 'students26'

WHERE sb.batch_id = $batch_id
");
?>

<form action="save_attendance.php" method="POST">
<input type="hidden" name="batch_id" value="<?= $batch_id ?>">
<input type="hidden" name="date" value="<?= $date ?>">

<table>
<tr>
    <th>Enrollment ID</th>
    <th>Name</th>
    <th>Status</th>
</tr>

<?php if($students->num_rows > 0): ?>
<?php while($st = $students->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($st['enrollment_id']) ?></td>
    <td><?= htmlspecialchars($st['name']) ?></td>
    <td>
        <select name="status[<?= $st['student_table'] ?>][<?= $st['student_id'] ?>]">
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="3">No students found</td>
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
