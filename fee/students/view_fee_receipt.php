<?php
session_start();
include 'database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    exit("Unauthorized access");
}

$id = $_GET['id'];
$enrollment_id = $_SESSION['enrollment_id'];

$stmt = $conn->prepare("
    SELECT * 
    FROM student_monthly_fee 
    WHERE id = ? AND enrollment_id = ?
");
$stmt->bind_param("is", $id, $enrollment_id);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();

if (!$receipt) {
    exit("Receipt not found");
}
?>

<h2>Monthly Fee Receipt</h2>

<p><b>Receipt No:</b> <?= $receipt['receipt_no']; ?></p>
<p><b>Enrollment ID:</b> <?= $receipt['enrollment_id']; ?></p>
<p><b>Name:</b> <?= $receipt['student_name']; ?></p>
<p><b>Month:</b> <?= $receipt['month']; ?></p>
<p><b>Amount Paid:</b> ₹<?= $receipt['amount']; ?></p>
<p><b>Payment Date:</b> <?= $receipt['paid_date']; ?></p>
<p><b>Payment Mode:</b> <?= $receipt['payment_mode']; ?></p>
<p><b>Status:</b> <?= $receipt['status']; ?></p>

<button onclick="window.print()">🖨 Print Receipt</button>
