<?php
include("db_connect.php");

$ids = $_POST['fee_ids'];
$mode = $_POST['payment_mode'];
$date = date("Y-m-d");

$conn->query("
    UPDATE student_monthly_fee 
    SET payment_status='Paid',
        payment_mode='$mode',
        payment_date='$date'
    WHERE id IN ($ids)
");

header("Location: fee_receipt.php?ids=$ids");
