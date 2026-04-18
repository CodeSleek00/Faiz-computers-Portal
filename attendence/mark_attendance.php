<?php
include '../database_connection/db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance</title>
<link rel="stylesheet" href="../css/global-theme.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

<style>
body{
    background:#ffffff;
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
    border:1px solid #D1D5DB;
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
    background:#2563EB;
    color:#fff;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
.bulk-actions{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
    margin:15px 0;
}
.bulk-actions button{
    background:#10B981;
}
.bulk-actions button:hover{
    background:#0F766E;
}
.bulk-actions label{
    display:flex;
    align-items:center;
    gap:6px;
    font-weight:500;
}
input{
    padding:8px;
    margin:5px;
}
</style>

</head>

<body>

<div class="card">

<h2>📋 Students Attendance (Only Continuing Students)</h2>

<p><a href="manage_student_status.php">Manage Student Status</a></p>

<form method="GET">
    <label>Date:</label>
    <input type="date" name="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>

    <label>Search:</label>
    <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Name / Enrollment ID">

    <button type="submit">Load Students</button>
</form>

<?php
if(isset($_GET['date'])):

$date = $_GET['date'];

// Prevent future dates
$today = date('Y-m-d');
if ($date > $today) {
    $date = $today;
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

/*
========================================
 FETCH WITH SEARCH + COURSE + STATUS
========================================
*/
$query = "
SELECT 
    student_id AS id,
    'students' AS table_name,
    name,
    enrollment_id,
    photo,
    course,
    status
FROM students
WHERE status = 'continue'

UNION ALL

SELECT 
    id AS id,
    'students26' AS table_name,
    name,
    enrollment_id,
    photo,
    course,
    status
FROM students26
WHERE status = 'continue'
";

if(!empty($search)){
    $query = "
    SELECT * FROM ($query) AS all_students
    WHERE name LIKE '%$search%' 
       OR enrollment_id LIKE '%$search%'
    ";
}

$query .= " ORDER BY name";

$students = $conn->query($query);
?>

<form action="save_attendance.php" method="POST">

<input type="hidden" name="date" value="<?= $date ?>">

<div class="bulk-actions">
    <label><input type="checkbox" id="selectAll"> Select All</label>
    <button type="button" id="markSelectedPresent">Mark Selected Present</button>
    <button type="button" id="markSelectedAbsent">Mark Selected Absent</button>
    <button type="button" id="markAllPresent">Mark All Present</button>
    <button type="button" id="markAllAbsent">Mark All Absent</button>
</div>

<table>
<tr>
    <th>Select</th>
    <th>Photo</th>
    <th>Enrollment ID</th>
    <th>Name</th>
    <th>Course</th>
    <th>Status</th>
</tr>

<?php if($students->num_rows > 0): ?>
<?php while($st = $students->fetch_assoc()): ?>
<tr>
    <td>
        <input type="checkbox" class="student-checkbox" name="selected[<?= $st['table_name'] ?>][<?= $st['id'] ?>]" value="1">
    </td>
    <td>
        <img src="../uploads/<?= !empty($st['photo']) ? $st['photo'] : 'default.png' ?>">
    </td>
    <td><?= htmlspecialchars($st['enrollment_id']) ?></td>
    <td><?= htmlspecialchars($st['name']) ?></td>
    <td><?= htmlspecialchars($st['course']) ?></td>
    <td>
        <select class="status-select" name="status[<?= $st['table_name'] ?>][<?= $st['id'] ?>]">
            <option value="Absent">Absent</option>
            <option value="Present">Present</option>
        </select>
    </td>
</tr>
<?php endwhile; ?>

<?php else: ?>
<tr>
    <td colspan="5">No students found</td>
</tr>
<?php endif; ?>

</table>

<br>
<button type="submit">Save Attendance</button>

</form>

<?php endif; ?>

</div>

<script>
const selectAll = document.getElementById('selectAll');
const markSelectedPresent = document.getElementById('markSelectedPresent');
const markSelectedAbsent = document.getElementById('markSelectedAbsent');
const markAllPresent = document.getElementById('markAllPresent');
const markAllAbsent = document.getElementById('markAllAbsent');

function getSelectedStatusElements() {
    return Array.from(document.querySelectorAll('.student-checkbox'))
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.closest('tr').querySelector('.status-select'))
        .filter(Boolean);
}

function setStatuses(status, elements) {
    elements.forEach(select => select.value = status);
}

if (selectAll) {
    selectAll.addEventListener('change', () => {
        const allCheckboxes = document.querySelectorAll('.student-checkbox');
        allCheckboxes.forEach(chk => chk.checked = selectAll.checked);
    });
}

if (markSelectedPresent) {
    markSelectedPresent.addEventListener('click', () => {
        setStatuses('Present', getSelectedStatusElements());
    });
}

if (markSelectedAbsent) {
    markSelectedAbsent.addEventListener('click', () => {
        setStatuses('Absent', getSelectedStatusElements());
    });
}

if (markAllPresent) {
    markAllPresent.addEventListener('click', () => {
        setStatuses('Present', Array.from(document.querySelectorAll('.status-select')));
    });
}

if (markAllAbsent) {
    markAllAbsent.addEventListener('click', () => {
        setStatuses('Absent', Array.from(document.querySelectorAll('.status-select')));
    });
}
</script>

</body>
</html>