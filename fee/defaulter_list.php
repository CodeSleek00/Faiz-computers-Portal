<?php
include("db_connect.php");

$month_no = $_GET['month_no'] ?? date('n');
$monthName = date('F', mktime(0,0,0,$month_no,1));

$defaulters = $conn->query("
    SELECT name,enrollment_id,course_name
    FROM student_monthly_fee
    WHERE fee_type='Monthly'
    AND month_no='$month_no'
    AND payment_status='Pending'
");
?>

<h2><?= $monthName ?> Defaulters</h2>

<table border="1" cellpadding="8">
<tr>
    <th>Name</th>
    <th>Enrollment</th>
    <th>Course</th>
</tr>

<?php if($defaulters->num_rows > 0): ?>
<?php while($d = $defaulters->fetch_assoc()): ?>
<tr>
    <td><?= $d['name'] ?></td>
    <td><?= $d['enrollment_id'] ?></td>
    <td><?= $d['course_name'] ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="3">No Defaulters Found</td>
</tr>
<?php endif; ?>
</table>
