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
select,input[type=date]{
    padding:6px;
}
</style>
</head>

<body>

<div class="card">
<h2>📋 Batch Wise Attendance</h2>

<!-- ================= FILTER FORM ================= -->
<form method="GET">
    <label>Date:</label>
    <input type="date" name="date" required>

    <label>Batch:</label>
    <select name="batch_id" required>
        <option value="">Select Batch</option>
        <?php while($b = $batches->fetch_assoc()){ ?>
            <option value="<?= $b['batch_id'] ?>">
                <?= $b['batch_name'] ?>
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
  ================= STUDENT FETCH LOGIC =================
  Supports BOTH tables: students & students26
*/
$students = $conn->query("
    SELECT 
        s.student_id AS sid,
        s.name,
        s.enrollment_id,
        'students' AS source
    FROM student_batches sb
    JOIN students s ON sb.student_id = s.student_id
    WHERE sb.batch_id = $batch_id

    UNION ALL

    SELECT 
        s26.id AS sid,
        s26.name,
        s26.enrollment_id,
        'students26' AS source
    FROM student_batches sb
    JOIN students26 s26 ON sb.id = s26.id
    WHERE sb.batch_id = $batch_id
");
?>

<!-- ================= ATTENDANCE FORM ================= -->
<form action="save_attendance.php" method="POST">
<input type="hidden" name="batch_id" value="<?= $batch_id ?>">
<input type="hidden" name="date" value="<?= $date ?>">

<table>
<tr>
    <th>Enrollment ID</th>
    <th>Name</th>
    <th>Status</th>
</tr>

<?php
if($students->num_rows > 0):
while($st = $students->fetch_assoc()):
?>
<tr>
    <td><?= htmlspecialchars($st['enrollment_id']) ?></td>
    <td><?= htmlspecialchars($st['name']) ?></td>
    <td>
        <select name="status[<?= $st['sid'] ?>]">
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>
    </td>
</tr>
<?php
endwhile;
else:
?>
<tr>
    <td colspan="3">No students found in this batch</td>
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
