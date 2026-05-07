<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

$fetchStudents = function(string $table) use ($conn): array {
    $rows = [];
    $result = mysqli_query($conn, "SELECT student_id, name, enrollment_id FROM `$table` ORDER BY id DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['table_name'] = $table;
            $rows[] = $row;
        }
    }
    return $rows;
};

$students = array_merge(
    $fetchStudents("students"),
    $fetchStudents("students26")
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Face Attendance System</title>
    <style>
        body{ font-family: Arial; background:#111827; color:white; padding:30px; }
        .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; gap:12px; }
        a.btn{ text-decoration:none; background:#2563eb; color:white; padding:10px 14px; border-radius:8px; display:inline-block; }
        a.btn.secondary{ background:#334155; }
        table{ width:100%; border-collapse:collapse; background:#1f2937; }
        th,td{ padding:14px; border:1px solid #374151; text-align:left; }
        .tag{ display:inline-block; padding:4px 10px; border-radius:999px; background:#0f172a; border:1px solid #334155; color:#cbd5e1; font-size:12px; }
        .muted{ color:#94a3b8; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <h2 style="margin:0;">Students</h2>
        <div class="muted">Record face, then mark attendance from camera.</div>
    </div>
    <div>
        <a class="btn secondary" href="attendance_dashboard.php">Attendance Dashboard</a>
        <a class="btn" href="attendance_live.php">Open Attendance Camera</a>
    </div>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Enrollment</th>
        <th>Table</th>
        <th>Actions</th>
    </tr>

    <?php if (count($students) === 0) { ?>
        <tr>
            <td colspan="5" style="text-align:center; padding:20px; color:#cbd5e1;">No students found in `students` / `students26`.</td>
        </tr>
    <?php } ?>

    <?php foreach ($students as $row) { ?>
        <tr>
            <td><?= (int)$row['id']; ?></td>
            <td><?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($row['enrollment_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span class="tag"><?= htmlspecialchars($row['table_name'], ENT_QUOTES, 'UTF-8'); ?></span></td>
            <td>
                <a class="btn" href="face_register.php?id=<?= (int)$row['id']; ?>&table=<?= urlencode($row['table_name']); ?>">Record Face</a>
            </td>
        </tr>
    <?php } ?>
</table>

</body>
</html>

