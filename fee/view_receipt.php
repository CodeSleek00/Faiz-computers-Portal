<?php
include("db_connect.php");

$eid = $_GET['eid'] ?? '';
$fee_ids = $_GET['fees'] ?? '';

if(empty($eid) || empty($fee_ids)){
    die("Invalid request");
}

$fee_ids_array = explode(',', $fee_ids);

// Fetch student info
$student = $conn->query("SELECT name, photo, course_name FROM students26 WHERE enrollment_id='$eid'")->fetch_assoc();

// Fetch paid fees
$fees = $conn->query("SELECT * FROM student_monthly_fee WHERE id IN (".implode(',', $fee_ids_array).")");
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt</title>
<style>
body{font-family:Arial;background:#f4f6f8;padding:20px;}
.receipt{width:700px;margin:auto;background:#fff;padding:20px;border-radius:8px;border:1px solid #ccc;}
h2{text-align:center;background:#0d6efd;color:#fff;padding:10px;border-radius:4px;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#198754;color:#fff;}
.total{font-weight:bold;}
button{padding:10px 15px;margin-top:15px;background:#0d6efd;color:#fff;border:none;cursor:pointer;border-radius:4px;}
</style>
<script>
function printReceipt(){
    window.print();
}
</script>
</head>
<body>

<div class="receipt">
<h2>Fee Receipt</h2>

<p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
<p><strong>Enrollment ID:</strong> <?= htmlspecialchars($eid) ?></p>
<p><strong>Course:</strong> <?= htmlspecialchars($student['course_name']) ?></p>

<table>
<tr>
    <th>Fee Type</th>
    <th>Month</th>
    <th>Amount (₹)</th>
    <th>Payment Date</th>
</tr>

<?php while($fee = $fees->fetch_assoc()): 
    $total += $fee['fee_amount'];
?>
<tr>
    <td><?= htmlspecialchars($fee['fee_type']) ?></td>
    <td><?= htmlspecialchars($fee['month_name'] ?: '-') ?></td>
    <td><?= number_format($fee['fee_amount'],2) ?></td>
    <td><?= date('d-M-Y', strtotime($fee['payment_date'])) ?></td>
</tr>
<?php endwhile; ?>

<tr>
    <td colspan="2" class="total">Total</td>
    <td colspan="2" class="total">₹<?= number_format($total,2) ?></td>
</tr>
</table>

<button onclick="printReceipt()">🖨 Print Receipt</button>
</div>

</body>
</html>
