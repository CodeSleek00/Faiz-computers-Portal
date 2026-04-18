<?php
include '../database_connection/db_connect.php';

/* ================= DATE FILTER ================= */
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$dateFilter = '';
if (!empty($from_date) && !empty($to_date)) {
    $dateFilter = "AND DATE(a.date) BETWEEN '$from_date' AND '$to_date'";
}

/* ================= SQLITE SETUP ================= */
$sqlitePath = __DIR__ . '/attendance.sqlite';
$sqlite = new SQLite3($sqlitePath);
$sqlite->exec('PRAGMA journal_mode = WAL');

$sqlite->exec('BEGIN TRANSACTION');
$sqlite->exec('CREATE TABLE IF NOT EXISTS attendance (id INTEGER PRIMARY KEY, student_id INTEGER, table_name TEXT, date TEXT, status TEXT)');
$sqlite->exec('CREATE TABLE IF NOT EXISTS students (student_id INTEGER PRIMARY KEY, name TEXT, enrollment_id TEXT, photo TEXT, status TEXT)');
$sqlite->exec('CREATE TABLE IF NOT EXISTS students26 (id INTEGER PRIMARY KEY, name TEXT, enrollment_id TEXT, photo TEXT, status TEXT)');
$sqlite->exec('DELETE FROM attendance');
$sqlite->exec('DELETE FROM students');
$sqlite->exec('DELETE FROM students26');

$insertAttendance = $sqlite->prepare('INSERT INTO attendance (id, student_id, table_name, date, status) VALUES (:id, :student_id, :table_name, :date, :status)');
$attendanceResult = $conn->query('SELECT id, student_id, table_name, date, status FROM attendance');
while ($row = $attendanceResult->fetch_assoc()) {
    $insertAttendance->bindValue(':id', $row['id'], SQLITE3_INTEGER);
    $insertAttendance->bindValue(':student_id', $row['student_id'], SQLITE3_INTEGER);
    $insertAttendance->bindValue(':table_name', $row['table_name'], SQLITE3_TEXT);
    $insertAttendance->bindValue(':date', $row['date'], SQLITE3_TEXT);
    $insertAttendance->bindValue(':status', $row['status'], SQLITE3_TEXT);
    $insertAttendance->execute();
}

$insertStudent = $sqlite->prepare('INSERT INTO students (student_id, name, enrollment_id, photo, status) VALUES (:student_id, :name, :enrollment_id, :photo, :status)');
$studentResult = $conn->query('SELECT student_id, name, enrollment_id, photo, status FROM students');
while ($row = $studentResult->fetch_assoc()) {
    $insertStudent->bindValue(':student_id', $row['student_id'], SQLITE3_INTEGER);
    $insertStudent->bindValue(':name', $row['name'], SQLITE3_TEXT);
    $insertStudent->bindValue(':enrollment_id', $row['enrollment_id'], SQLITE3_TEXT);
    $insertStudent->bindValue(':photo', $row['photo'], SQLITE3_TEXT);
    $insertStudent->bindValue(':status', $row['status'], SQLITE3_TEXT);
    $insertStudent->execute();
}

$insertStudent26 = $sqlite->prepare('INSERT INTO students26 (id, name, enrollment_id, photo, status) VALUES (:id, :name, :enrollment_id, :photo, :status)');
$student26Result = $conn->query('SELECT id, name, enrollment_id, photo, status FROM students26');
while ($row = $student26Result->fetch_assoc()) {
    $insertStudent26->bindValue(':id', $row['id'], SQLITE3_INTEGER);
    $insertStudent26->bindValue(':name', $row['name'], SQLITE3_TEXT);
    $insertStudent26->bindValue(':enrollment_id', $row['enrollment_id'], SQLITE3_TEXT);
    $insertStudent26->bindValue(':photo', $row['photo'], SQLITE3_TEXT);
    $insertStudent26->bindValue(':status', $row['status'], SQLITE3_TEXT);
    $insertStudent26->execute();
}

$sqlite->exec('COMMIT');

/* ================= FETCH ATTENDANCE ================= */
$query = "SELECT a.date, CASE WHEN a.table_name = 'students' THEN s.name WHEN a.table_name = 'students26' THEN s26.name ELSE 'Unknown' END AS student_name, CASE WHEN a.table_name = 'students' THEN s.enrollment_id WHEN a.table_name = 'students26' THEN s26.enrollment_id END AS enrollment_id, CASE WHEN a.table_name = 'students' THEN s.photo WHEN a.table_name = 'students26' THEN s26.photo END AS photo, a.status FROM attendance a LEFT JOIN students s ON a.table_name = 'students' AND a.student_id = s.student_id LEFT JOIN students26 s26 ON a.table_name = 'students26' AND a.student_id = s26.id WHERE ((a.table_name = 'students' AND LOWER(TRIM(s.status)) = 'continue') OR (a.table_name = 'students26' AND LOWER(TRIM(s26.status)) = 'continue')) $dateFilter ORDER BY a.date DESC";
$data = $sqlite->query($query);
if (!$data) {
    die('Attendance query failed: ' . $sqlite->lastErrorMsg());
}

/* ================= COUNT ================= */
$countQuery = $sqlite->query("SELECT SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) AS present_count, SUM(CASE WHEN a.status='Absent' THEN 1 ELSE 0 END) AS absent_count FROM attendance a LEFT JOIN students s ON a.table_name = 'students' AND a.student_id = s.student_id LEFT JOIN students26 s26 ON a.table_name = 'students26' AND a.student_id = s26.id WHERE ((a.table_name = 'students' AND LOWER(TRIM(s.status)) = 'continue') OR (a.table_name = 'students26' AND LOWER(TRIM(s26.status)) = 'continue')) $dateFilter");
$countData = $countQuery->fetchArray(SQLITE3_ASSOC);
$present = $countData['present_count'] ?? 0;
$absent  = $countData['absent_count'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Attendance Records</title>

<style>
body{
    font-family: Arial;
    background:#f4f6f9;
    padding:20px;
}
h2{
    margin-bottom:10px;
}
.filter-box{
    background:#fff;
    padding:15px;
    border-radius:8px;
    margin-bottom:15px;
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
button{
    padding:8px 15px;
    border:none;
    background:#007bff;
    color:#fff;
    border-radius:5px;
    cursor:pointer;
}
button:hover{
    background:#0056b3;
}
</style>

</head>

<body>

<h2>📊 Attendance Records</h2>

<!-- FILTER -->
<div class="filter-box">
<form method="GET">
    From: <input type="date" name="from_date" value="<?= $from_date ?>">
    To: <input type="date" name="to_date" value="<?= $to_date ?>">
    <button type="submit">Filter</button>
    <a href="view_attendance.php"><button type="button">Reset</button></a>
</form>
</div>

<!-- SUMMARY -->
<div class="summary">
    <div class="present">✅ Present: <?= $present ?></div>
    <div class="absent">❌ Absent: <?= $absent ?></div>
</div>

<!-- TABLE -->
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