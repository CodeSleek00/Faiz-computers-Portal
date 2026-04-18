<?php
include '../database_connection/db_connect.php';

// Get selected date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Prevent future dates
$today = date('Y-m-d');
if ($selected_date > $today) {
    $selected_date = $today;
}

// Check if status column exists
$status_exists_students = $conn->query("SHOW COLUMNS FROM students LIKE 'status'")->num_rows > 0;
$status_exists_students26 = $conn->query("SHOW COLUMNS FROM students26 LIKE 'status'")->num_rows > 0;

// Fetch attendance records for selected date
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

WHERE a.date = '$selected_date'
ORDER BY a.date DESC
");

if (!$data) {
    die("Attendance query failed: " . $conn->error);
}

// Count present students
$present_count = $data->num_rows;

// Get list of continue students to calculate absent count
$continue_query = "
SELECT COUNT(*) AS total FROM (
SELECT student_id AS id, 'students' AS tbl FROM students WHERE " . ($status_exists_students ? "status = 'continue'" : "1=1") . "
UNION ALL
SELECT id, 'students26' FROM students26 WHERE " . ($status_exists_students26 ? "status = 'continue'" : "1=1") . "
) AS continue_students
";

$continue_result = $conn->query($continue_query);
$continue_row = $continue_result->fetch_assoc();
$total_continue_students = $continue_row['total'];

// Absent count = total continue students - present students
$absent_count = $total_continue_students - $present_count;
$absent_count = max(0, $absent_count); // Ensure non-negative
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
.card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 1200px;
    margin: auto;
}
.filter-section {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
}
.filter-section input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.filter-section button {
    padding: 8px 15px;
    background: #2563EB;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.stats-section {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    justify-content: space-around;
}
.stat-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    flex: 1;
}
.stat-box.present {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}
.stat-box.absent {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
}
.stat-box.total {
    background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
}
.stat-box h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}
.stat-box .number {
    font-size: 36px;
    font-weight: bold;
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
.no-data {
    text-align: center;
    padding: 30px;
    color: #999;
}
</style>

</head>

<body>

<div class="card">
<h2>📊 Attendance Records</h2>

<div class="filter-section">
    <form method="GET" style="display: flex; gap: 10px; align-items: center;">
        <label><strong>Select Date:</strong></label>
        <input type="date" name="date" value="<?php echo $selected_date; ?>" max="<?php echo $today; ?>" required>
        <button type="submit">Filter</button>
    </form>
</div>

<?php if ($present_count > 0 || $absent_count > 0): ?>
<div class="stats-section">
    <div class="stat-box present">
        <h3>Present</h3>
        <div class="number"><?php echo $present_count; ?></div>
    </div>
    <div class="stat-box absent">
        <h3>Absent</h3>
        <div class="number"><?php echo $absent_count; ?></div>
    </div>
    <div class="stat-box total">
        <h3>Total Continue Students</h3>
        <div class="number"><?php echo $total_continue_students; ?></div>
    </div>
</div>
<?php endif; ?>

<?php if ($present_count > 0): ?>
<table>
<tr>
    <th>Date</th>
    <th>Photo</th>
    <th>Enrollment ID</th>
    <th>Student Name</th>
    <th>Status</th>
</tr>

<?php $data->data_seek(0); while($row = $data->fetch_assoc()): ?>
<tr>
    <td><?= $row['date'] ?></td>
    <td>
        <img src="../uploads/<?= !empty($row['photo']) ? $row['photo'] : 'default.png' ?>">
    </td>
    <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    <td><strong><?= $row['status'] ?></strong></td>
</tr>
<?php endwhile; ?>

</table>
<?php else: ?>
<div class="no-data">
    <p>No attendance records found for <?php echo $selected_date; ?></p>
</div>
<?php endif; ?>

</div>

</body>
</html>