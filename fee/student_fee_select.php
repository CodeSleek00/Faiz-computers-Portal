<?php
include("db_connect.php");

$enroll = $_GET['enroll'];

$student = $conn->query("
    SELECT * FROM student_monthly_fee 
    WHERE enrollment_id='$enroll' LIMIT 1
")->fetch_assoc();

/* Fetch All Pending Fees */
$fees = $conn->query("
    SELECT * FROM student_monthly_fee
    WHERE enrollment_id='$enroll'
    AND payment_status='Pending'
");

/* GROUP FEES INTO CATEGORIES */
$categories = [
    "Monthly Fee" => [],
    "Registration Fee" => [],
    "Internal Exam Fee" => [],
    "Semester Exam Fee" => []
];

while($row = $fees->fetch_assoc()){
    $cat = $row['fee_type'];

    if(isset($categories[$cat])){
        $categories[$cat][] = $row;
    }
}
?>

<h2><?= $student['name'] ?> - Pending Fees</h2>

<form method="POST" action="fee_payment.php">
<input type="hidden" name="enrollment_id" value="<?= $enroll ?>">

<style>
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

.table th, .table td {
    border: 1px solid #ccc;
    padding: 10px;
}

.category-title {
    background: #444;
    color: #fff;
    padding: 10px;
    font-size: 18px;
    margin-top: 25px;
}
</style>

<?php foreach($categories as $cat_name => $cat_fees): ?>
    <?php if(count($cat_fees) > 0): ?>

        <div class="category-title"><?= $cat_name ?></div>

        <table class="table">
            <tr>
                <th>Select</th>
                <th>Fee Type</th>
                <th>Month</th>
                <th>Amount</th>
            </tr>
            
            <?php foreach($cat_fees as $f): ?>
            <tr>
                <td>
                    <input type="checkbox" name="fee_ids[]" value="<?= $f['id'] ?>">
                </td>
                <td><?= $f['fee_type'] ?></td>
                <td><?= $f['month_name'] ?: '-' ?></td>
                <td>₹<?= $f['fee_amount'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>
<?php endforeach; ?>

<br>
<button type="submit">Proceed to Payment</button>
</form>
