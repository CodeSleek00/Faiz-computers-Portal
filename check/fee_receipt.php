<?php
include("auth_check.php");
include("db_connect.php");

$id = $_GET['id'];

$data = $conn->query("
    SELECT f.*, s.name, s.course
    FROM student_monthly_fee f
    JOIN students26 s ON f.enrollment_id = s.enrollment_id
    WHERE f.id='$id'
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fee Receipt</title>
    <style>
        body { font-family: Arial; }
        .receipt { width: 400px; margin: auto; border: 1px solid #000; padding: 20px; }
    </style>
</head>
<body>

<div class="receipt">
    <h3 align="center">FAIZ COMPUTER INSTITUTE</h3>
    <hr>

    <p>
    <b>Name:</b> <?= $data['name'] ?><br>
    <b>Enrollment:</b> <?= $data['enrollment_id'] ?><br>
    <b>Course:</b> <?= $data['course'] ?>
    </p>

    <p>
    <b>Fee Type:</b> <?= $data['fee_type'] ?><br>
    <b>Month:</b> <?= $data['month_name'] ?><br>
    <b>Amount:</b> ₹<?= $data['fee_amount'] ?><br>
    <b>Payment Mode:</b> <?= $data['payment_mode'] ?><br>
    <b>Date:</b> <?= $data['payment_date'] ?><br>
   
    </p>

    <center>
        <button onclick="window.print()">Print Receipt</button>
    </center>
</div>

</body>
</html>
