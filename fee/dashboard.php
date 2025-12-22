<?php
include("db_connect.php");

$students = $conn->query("
    SELECT DISTINCT enrollment_id, name, photo, course_name
    FROM student_monthly_fee
    ORDER BY name ASC
");
?>

<table border="1" width="100%">
<tr>
    <th>Photo</th>
    <th>Name</th>
    <th>Course</th>
    <th>Action</th>
</tr>

<?php while($s = $students->fetch_assoc()): ?>
<tr>
    <td><img src="uploads/<?= $s['photo'] ?>" width="50"></td>
    <td><?= $s['name'] ?></td>
    <td><?= $s['course_name'] ?></td>
    <td>
        <a href="student_fee_select.php?enroll=<?= $s['enrollment_id'] ?>">
            <button>Submit Fee</button>
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>
