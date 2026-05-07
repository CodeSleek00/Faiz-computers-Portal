<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<?php
include '../db_connect.php';

$attendance = mysqli_query($conn,
"SELECT * FROM attendance ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Dashboard</title>

    <style>
        body{
            background:#111827;
            color:white;
            font-family:Arial;
            padding:30px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:#1f2937;
        }

        th,td{
            border:1px solid #374151;
            padding:15px;
        }
    </style>
</head>
<body>

<h1>Attendance Dashboard</h1>

<table>

<tr>
    <th>ID</th>
    <th>Student</th>
    <th>Enrollment</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
</tr>

<?php while($row = mysqli_fetch_assoc($attendance)){ ?>

<tr>
    <td><?= $row['id']; ?></td>
    <td><?= $row['student_name']; ?></td>
    <td><?= $row['enrollment_id']; ?></td>
    <td><?= $row['attendance_date']; ?></td>
    <td><?= $row['attendance_time']; ?></td>
    <td><?= $row['status']; ?></td>
</tr>

<?php } ?>

</table>

</body>
</html>