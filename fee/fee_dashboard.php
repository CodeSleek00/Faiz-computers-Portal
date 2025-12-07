<?php
include "db_connect.php";
$students = $conn->query("SELECT * FROM students");
?>
<h2>Fee Dashboard</h2>

<table border="1" width="100%">
<tr>
    <th>Photo</th>
    <th>Name</th>
    <th>Course</th>
    <th>Total Pending</th>
    <th>Manage</th>
</tr>

<?php while($s = $students->fetch()):

$pending = $conn->query("SELECT SUM(amount) as total FROM fee_master WHERE student_id=".$s['id']." AND status='unpaid' ")->fetch()['total'];

?>
<tr>
    <td><img src="<?= $s['photo'] ?>" width="50"></td>
    <td><?= $s['full_name'] ?></td>
    <td><?= $s['course_name'] ?></td>
    <td><b>₹ <?= $pending ?></b></td>
    <td><a href="manage_fee.php?id=<?= $s['id'] ?>">View</a></td>
</tr>
<?php endwhile; ?>

</table>
