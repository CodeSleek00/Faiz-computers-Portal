<?php
include 'db_connect.php';

$fee_id = $_GET['fee_id'];
$fee = $conn->query("SELECT sf.*, s.name, s.enrollment_id, s.course, s.contact, s.photo 
    FROM student_fees sf 
    JOIN students s ON sf.student_id=s.student_id 
    WHERE sf.fee_id=$fee_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Receipt</title>
    <style>
        body { font-family: Arial; margin:30px; }
        .receipt { border:1px solid #000; padding:20px; width:500px; margin:auto; }
        .header { text-align:center; }
        .photo { float:right; }
        .actions { margin-top:20px; text-align:center; }
        button { padding:8px 12px; }
        a { margin-left:10px; padding:8px 12px; background:green; color:white; text-decoration:none; }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h2>Fee Receipt</h2>
    </div>
    <div>
        <img src="photo/<?php echo $fee['photo']; ?>" width="80" class="photo">
        <p><b>Enrollment:</b> <?php echo $fee['enrollment_id']; ?></p>
        <p><b>Name:</b> <?php echo $fee['name']; ?></p>
        <p><b>Course:</b> <?php echo $fee['course']; ?></p>
        <p><b>Month:</b> <?php echo $fee['month']; ?></p>
        <p><b>Amount:</b> ₹<?php echo $fee['amount']; ?></p>
        <p><b>Date:</b> <?php echo $fee['created_at']; ?></p>
    </div>
    <div class="actions">
        <button onclick="window.print()">Print</button>
        <a href="https://wa.me/91<?php echo $fee['contact']; ?>?text=Hello%20<?php echo urlencode($fee['name']); ?>,%20Your%20fee%20of%20₹<?php echo $fee['amount']; ?>%20for%20<?php echo $fee['month']; ?>%20has%20been%20received.%20Thank%20you." target="_blank">Share on WhatsApp</a>
    </div>
</div>
</body>
</html>
