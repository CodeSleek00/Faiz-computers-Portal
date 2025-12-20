<?php
include("db_connect.php");

/* ================= SAFE GET DATA ================= */
$fee_id = $_GET['fee_id'] ?? null;

if(!$fee_id){
    die("Invalid fee ID.");
}

/* ================= FETCH FEE RECORD ================= */
$fee = $conn->query("SELECT * FROM student_monthly_fee WHERE id='$fee_id'")->fetch_assoc();

if(!$fee){
    die("Fee record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt - <?= htmlspecialchars($fee['name']) ?></title>
<style>
body{font-family:Arial;background:#f4f6f8;padding:20px;}
.receipt{max-width:600px;margin:auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
h2{background:#0d6efd;color:#fff;padding:10px;border-radius:4px;text-align:center;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#0d6efd;color:#fff;}
button{padding:8px 12px;border:none;background:#198754;color:#fff;cursor:pointer;margin-top:15px;}
img{width:70px;height:70px;border-radius:50%;}
</style>
</head>
<body>

<div class="receipt">
<h2>Fee Receipt</h2>

<table>
<tr><th>Student Name</th><td><?= htmlspecialchars($fee['name']) ?></td></tr>
<tr><th>Enrollment ID</th><td><?= htmlspecialchars($fee['enrollment_id']) ?></td></tr>
<tr><th>Course</th><td><?= htmlspecialchars($fee['course_name']) ?></td></tr>
<tr><th>Fee Type</th><td><?= htmlspecialchars($fee['fee_type']) ?></td></tr>
<?php if(!empty($fee['month_name'])): ?>
<tr><th>Month</th><td><?= htmlspecialchars($fee['month_name']) ?></td></tr>
<?php endif; ?>
<tr><th>Amount Paid</th><td>₹<?= number_format($fee['fee_amount'],2) ?></td></tr>
<tr><th>Payment Date</th><td><?= date('d-M-Y', strtotime($fee['payment_date'])) ?></td></tr>
</table>

<button onclick="window.print()">🖨️ Print Receipt</button>

</div>

</body>
</html>
