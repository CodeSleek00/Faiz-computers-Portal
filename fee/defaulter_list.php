<?php
include("db_connect.php");

$month = $_GET['month'] ?? date('F');

$defaulters = $conn->query("
    SELECT * FROM student_monthly_fee
    WHERE fee_type='Monthly'
    AND month_name='$month'
    AND payment_status='Pending'
");
?>

<h2><?= $month ?> Defaulters</h2>

<table border="1">
<tr>
    <th>Name</th>
    <th>Enrollment</th>
    <th>Course</th>
</tr>

<?php while($d = $defaulters->fetch_assoc()): ?>
<tr>
    <td><?= $d['name'] ?></td>
    <td><?= $d['enrollment_id'] ?></td>
    <td><?= $d['course_name'] ?></td>
</tr>
<?php endwhile; ?>
</table>
