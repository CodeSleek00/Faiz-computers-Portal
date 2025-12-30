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
body{font-family:Arial;background:#f4f6f9;padding:20px}
.card{background:#fff;padding:20px;border-radius:8px}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #ccc;padding:10px;text-align:center}
button{padding:10px 20px;background:#2ecc71;color:#fff;border:none;border-radius:5px}
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
        <?php while($b=$batches->fetch_assoc()){ ?>
            <option value="<?= $b['batch_id'] ?>"><?= $b['batch_name'] ?></option>
        <?php } ?>
    </select>

    <button type="submit">Load Students</button>
</form>

<?php
if(isset($_GET['batch_id'], $_GET['date'])):
$batch_id = $_GET['batch_id'];
$date = $_GET['date'];

$students = $conn->query("
SELECT s.id, s.name, s.enrollment_id 
FROM student_batches bs
JOIN students s ON bs.student_id = s.id
WHERE bs.batch_id = $batch_id
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

<?php while($st=$students->fetch_assoc()){ ?>
<tr>
    <td><?= $st['enrollment_id'] ?></td>
    <td><?= $st['name'] ?></td>
    <td>
        <select name="status[<?= $st['id'] ?>]">
            <option value="Present">Present</option>
            <option value="Absent">Absent</option>
        </select>
    </td>
</tr>
<?php } ?>

</table>
<br>
<button type="submit">Save Attendance</button>
</form>

<?php endif; ?>
</div>

</body>
</html>
