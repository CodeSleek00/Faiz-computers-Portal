<?php
include("db_connect.php");

$fee_ids = $_POST['fee_ids'];
$enroll  = $_POST['enrollment_id'];

$total = 0;
$ids = implode(",", $fee_ids);

$fees = $conn->query("SELECT * FROM student_monthly_fee WHERE id IN ($ids)");
?>

<h2>Select Payment Mode</h2>

<form method="POST" action="submit_fee_action.php">
<input type="hidden" name="fee_ids" value="<?= $ids ?>">

<label>
    <input type="radio" name="payment_mode" value="Cash" required> Cash
</label>
<label>
    <input type="radio" name="payment_mode" value="Online"> Online
</label>

<h3>Fee Summary</h3>
<?php while($f = $fees->fetch_assoc()):
$total += $f['fee_amount']; ?>
<div>
<?= $f['fee_type'] ?> <?= $f['month_name'] ?> ₹<?= $f['fee_amount'] ?>
</div>
<?php endwhile; ?>

<h3>Total: ₹<?= $total ?></h3>

<button type="submit">Submit Fee</button>
</form>
