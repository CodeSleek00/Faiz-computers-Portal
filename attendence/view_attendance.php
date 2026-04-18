<?php
include '../database_connection/db_connect.php';

$data = $conn->query("
SELECT
    a.date,

    CASE
        WHEN a.table_name = 'students' THEN s.name
        WHEN a.table_name = 'students26' THEN s26.name
        ELSE 'Unknown'
    END AS student_name,

    CASE
        WHEN a.table_name = 'students' THEN s.enrollment_id
        WHEN a.table_name = 'students26' THEN s26.enrollment_id
    END AS enrollment_id,

    CASE
        WHEN a.table_name = 'students' THEN s.photo
        WHEN a.table_name = 'students26' THEN s26.photo
    END AS photo,

    a.status

FROM attendance a

LEFT JOIN students s
    ON a.student_id = s.student_id
   AND a.table_name = 'students'

LEFT JOIN students26 s26
    ON a.student_id = s26.id
   AND a.table_name = 'students26'

ORDER BY a.date DESC
");

if (!$data) {
    die("Attendance query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>View Attendance</title>

<style>
body{
    font-family: Arial;
    background:#f4f6f9;
    padding:20px;
}
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
th,td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}
img{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}
</style>

</head>

<body>

<h2>📊 Attendance Records</h2>

<table>
<tr>
    <th>Date</th>
    <th>Photo</th>
    <th>Enrollment ID</th>
    <th>Student Name</th>
    <th>Status</th>
</tr>

<?php while($row = $data->fetch_assoc()): ?>
<tr>
    <td><?= $row['date'] ?></td>
    <td>
        <img src="../uploads/<?= !empty($row['photo']) ? $row['photo'] : 'default.png' ?>">
    </td>
    <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>