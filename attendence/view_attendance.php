<?php
include '../database_connection/db_connect.php';

/* ================= FETCH ATTENDANCE ================= */
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

/* ================= COUNT PRESENT & ABSENT ================= */
$countQuery = $conn->query("
SELECT 
    SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present_count,
    SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) AS absent_count
FROM attendance
");

$countData = $countQuery->fetch_assoc();

$present = $countData['present_count'] ?? 0;
$absent  = $countData['absent_count'] ?? 0;
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
h2{
    margin-bottom:10px;
}
.summary{
    background:#fff;
    padding:15px;
    margin-bottom:15px;
    border-radius:8px;
    display:flex;
    gap:20px;
    font-size:18px;
}
.present{
    color:green;
    font-weight:bold;
}
.absent{
    color:red;
    font-weight:bold;
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
th{
    background:#eee;
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

<div class="summary">
    <div class="present">✅ Present: <?= $present ?></div>
    <div class="absent">❌ Absent: <?= $absent ?></div>
</div>

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
    <td><?= htmlspecialchars($row['date']) ?></td>
    
    <td>
        <img src="../uploads/<?= !empty($row['photo']) ? htmlspecialchars($row['photo']) : 'default.png' ?>">
    </td>
    
    <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
    
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    
    <td>
        <?php if($row['status'] == 'Present'): ?>
            <span style="color:green; font-weight:bold;">Present</span>
        <?php else: ?>
            <span style="color:red; font-weight:bold;">Absent</span>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>