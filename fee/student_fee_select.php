<?php
include("db_connect.php");

$enroll = $_GET['enroll'];

$student = $conn->query("
    SELECT * FROM student_monthly_fee 
    WHERE enrollment_id='$enroll' LIMIT 1
")->fetch_assoc();

$fees = $conn->query("
    SELECT * FROM student_monthly_fee
    WHERE enrollment_id='$enroll'
    AND payment_status='Pending'
");
?>

<h2><?= $student['name'] ?> - Pending Fees</h2>

<form method="POST" action="fee_payment.php">
<input type="hidden" name="enrollment_id" value="<?= $enroll ?>">

<?php while($f = $fees->fetch_assoc()): ?>
<div>
    <input type="checkbox" name="fee_ids[]" value="<?= $f['id'] ?>">
    <?= $f['fee_type'] ?>
    <?= $f['month_name'] ? " - ".$f['month_name'] : "" ?>
    ₹<?= $f['fee_amount'] ?>
</div>
<?php endwhile; ?>

<br>
<button type="submit">Proceed to Payment</button>
</form>
