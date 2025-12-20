<?php
include("db_connect.php");

$enrollment_id = $_GET['eid'];
$month_no = $_GET['month_no'];

// Fetch student + month info
$fee = $conn->query("SELECT * FROM student_monthly_fee WHERE enrollment_id='$enrollment_id' AND month_no='$month_no'")->fetch_assoc();

if($_SERVER['REQUEST_METHOD']=='POST'){
    $amount = $_POST['fee_amount'];
    $payment_date = date('Y-m-d');

    // Update table
    $conn->query("UPDATE student_monthly_fee 
                  SET fee_amount='$amount', payment_status='Paid', payment_date='$payment_date'
                  WHERE enrollment_id='$enrollment_id' AND month_no='$month_no'");
    
    // Redirect to receipt
    header("Location: view_receipt.php?eid=$enrollment_id&month_no=$month_no");
    exit;
}
?>

<h2>Submit Fee for <?= $fee['month_name']." - ".$fee['name'] ?></h2>
<form method="POST">
    <label>Fee Amount:</label>
    <input type="number" name="fee_amount" value="<?= $fee['fee_amount'] ?>" required>
    <br><br>
    <button type="submit">Submit Fee</button>
</form>
