<?php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$receipt_no = $_GET['receipt_no'] ?? '';
if (!$receipt_no) die("Receipt not found");

$stmt = $conn->prepare("SELECT sf.*, s.name, s.enrollment_id, s.course FROM student_fees sf 
                        JOIN students s ON s.student_id = sf.student_id
                        WHERE sf.receipt_no = ?");
$stmt->bind_param("s", $receipt_no);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();
if (!$fee) die("Receipt not found");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Fee Receipt</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{font-family:'Segoe UI',sans-serif;padding:30px;background:#f4f6f9;}
.receipt{max-width:700px;margin:auto;background:#fff;padding:30px;border-radius:15px;box-shadow:0 4px 6px rgba(0,0,0,0.1);}
h2{text-align:center;margin-bottom:30px;}
.table td, .table th{text-align:left;}
</style>
</head>
<body>
<div class="receipt">
<h2>Fee Receipt</h2>
<table class="table table-borderless">
<tr><th>Receipt No:</th><td><?= htmlspecialchars($fee['receipt_no']) ?></td></tr>
<tr><th>Student Name:</th><td><?= htmlspecialchars($fee['name']) ?></td></tr>
<tr><th>Enrollment ID:</th><td><?= htmlspecialchars($fee['enrollment_id']) ?></td></tr>
<tr><th>Course:</th><td><?= htmlspecialchars($fee['course']) ?></td></tr>
<tr><th>Month:</th><td><?= htmlspecialchars($fee['month_name'].' '.$fee['year']) ?></td></tr>
<tr><th>Amount Paid:</th><td>₹<?= number_format($fee['amount'],2) ?></td></tr>
<tr><th>Payment Method:</th><td><?= htmlspecialchars($fee['payment_method']) ?></td></tr>
<tr><th>Payment Date:</th><td><?= date('d-m-Y', strtotime($fee['payment_date'])) ?></td></tr>
</table>
<div class="text-center mt-4">
<button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
</div>
</div>
</body>
</html>
