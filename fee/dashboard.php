<?php
include("db_connect.php");

$students = $conn->query("
    SELECT DISTINCT enrollment_id, name, photo, course_name, admission_date
    FROM student_monthly_fee
    ORDER BY admission_date DESC
");
?>

<table border="1" width="100%" cellpadding="10">
<tr>
    <th>Photo</th>
    <th>Name</th>
    <th>Course</th>
    <th>Admission Date</th>
    <th>Action</th>
</tr>

<?php while($s = $students->fetch_assoc()): ?>
<tr>
    <td>
        <img src="../uploads/<?= $s['photo'] ?: 'no-photo.png' ?>" width="50"
             onerror="this.src='../assets/no-photo.png'">
    </td>
    <td><?= htmlspecialchars($s['name']) ?></td>
    <td><?= htmlspecialchars($s['course_name']) ?></td>
    <td><?= date("d-M-Y", strtotime($s['admission_date'])) ?></td>
    <td>
        <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>">
            <button>Submit Fee</button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>
