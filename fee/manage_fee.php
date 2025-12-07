<?php
include "db_connect.php";
$id = $_GET['id'];

$student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch();
$fees = $conn->query("SELECT * FROM fee_master WHERE student_id=$id ORDER BY id ASC");
?>

<h2>Manage Fee for <?= $student['full_name'] ?></h2>

<table border="1" width="100%">
<tr>
    <th>Month</th>
    <th>Fee Type</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($f = $fees->fetch()): ?>
<tr>
    <td><?= $f['month_name'] ?></td>
    <td><?= $f['fee_type'] ?></td>
    <td>₹ <?= $f['amount'] ?></td>
    <td><?= $f['status'] ?></td>
    <td>
        <?php if($f['status'] == "unpaid"): ?>
            <a href="mark_paid.php?id=<?= $f['id'] ?>">Mark Paid</a>
        <?php else: ?>
            PAID
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
