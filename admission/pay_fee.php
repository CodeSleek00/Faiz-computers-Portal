<?php
include "db.php";

$id = $_POST['id'];   // fee_structure id (row)
$payment_mode = $_POST['payment_mode'];

// Generate Receipt No
$receipt_no = "REC" . time();

// Update Fee Row
mysqli_query($conn, "UPDATE fee_structure
    SET status='paid', paid_date=NOW(), receipt_no='$receipt_no'
    WHERE id='$id'");

// Insert Receipt
$fee_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM fee_structure WHERE id=$id"));

mysqli_query($conn, "INSERT INTO fee_receipts
    (receipt_no, student_id, fee_type, month_name, amount, payment_mode)
    VALUES 
    ('$receipt_no', '{$fee_data['student_id']}', '{$fee_data['fee_type']}', '{$fee_data['month_name']}',
     '{$fee_data['amount']}', '$payment_mode')");

// Redirect to receipt
header("Location: fee_receipt.php?receipt_no=$receipt_no");
exit;
?>
