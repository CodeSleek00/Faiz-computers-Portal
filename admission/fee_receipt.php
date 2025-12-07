<?php
include "db.php";
$receipt_no = $_GET['receipt_no'];

$data = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT r.*, s.name, s.enrollment_id FROM fee_receipts r 
     JOIN student_2026 s ON r.student_id = s.student_id 
     WHERE r.receipt_no='$receipt_no'"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Fee Receipt</title>
<style>
body { font-family: Arial; padding:20px; }
.box { border:1px solid #000; padding:20px; width:600px; margin:auto; }
</style>
</head>
<body>

<div class="box">
    <h2 style="text-align:center;">FAIZ INSTITUTE - Fee Receipt</h2>
    <hr>

    <p><b>Receipt No:</b> <?= $data['receipt_no'] ?></p>
    <p><b>Name:</b> <?= $data['name'] ?></p>
    <p><b>Enrollment ID:</b> <?= $data['enrollment_id'] ?></p>
    <p><b>Fee Type:</b> <?= ucfirst($data['fee_type']) ?></p>
    <p><b>Month:</b> <?= $data['month_name'] ?></p>
    <p><b>Amount:</b> ₹<?= $data['amount'] ?></p>
    <p><b>Payment Mode:</b> <?= $data['payment_mode'] ?></p>
    <p><b>Date:</b> <?= $data['created_at'] ?></p>

    <hr>
    <p style="text-align:center; font-size:13px;">This is a computer generated receipt.</p>
</div>

</body>
</html>
