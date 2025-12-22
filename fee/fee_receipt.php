<?php
include("db_connect.php");

$ids = $_GET['ids'];
$fees = $conn->query("
    SELECT * FROM student_monthly_fee WHERE id IN ($ids)
");
?>

<h2>Fee Receipt</h2>

<?php while($f = $fees->fetch_assoc()): ?>
<div style="border:1px solid #000;padding:10px;margin:10px;">
<b>Name:</b> <?= $f['name'] ?><br>
<b>Enrollment:</b> <?= $f['enrollment_id'] ?><br>
<b>Fee Type:</b> <?= $f['fee_type'] ?><br>
<b>Month:</b> <?= $f['month_name'] ?><br>
<b>Amount:</b> ₹<?= $f['fee_amount'] ?><br>
<b>Mode:</b> <?= $f['payment_mode'] ?><br>
<b>Date:</b> <?= date('d-M-Y',strtotime($f['payment_date'])) ?>
</div>
<?php endwhile; ?>
