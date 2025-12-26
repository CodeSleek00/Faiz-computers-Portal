<?php
session_start();
if (!isset($_SESSION['student_enroll'])) {
    header("Location: student_login.php");
    exit;
}

include("db_connect.php");

$enroll = $_SESSION['student_enroll'];

// ================= STUDENT INFO =================
$student = $conn->query("
    SELECT * FROM students26 
    WHERE enrollment_id='$enroll' 
    LIMIT 1
")->fetch_assoc();

if (!$student) {
    die("Student not found");
}

// ================= PAID FEES ONLY =================
$fees = $conn->query("
    SELECT * FROM student_fee_payments
    WHERE enrollment_id='$enroll'
    AND payment_status='Paid'
    ORDER BY payment_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($student['name']) ?></h2>

<p>
<b>Enrollment:</b> <?= htmlspecialchars($student['enrollment_id']) ?><br>
<b>Course:</b> <?= htmlspecialchars($student['course'] ?? 'N/A') ?><br>
<b>Phone:</b> <?= htmlspecialchars($student['phone'] ?? 'N/A') ?>
</p>

<h3>Fee Receipts (Paid Only)</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Date</th>
    <th>Fee Type</th>
    <th>Month</th>
    <th>Amount</th>
    <th>Receipt</th>
</tr>

<?php if ($fees && $fees->num_rows > 0) { ?>
    <?php while ($row = $fees->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['payment_date'] ?></td>
        <td><?= $row['fee_type'] ?></td>
        <td><?= $row['month_name'] ?></td>
        <td>₹<?= $row['amount'] ?></td>
        <td>
            <a href="fee_receipt.php?id=<?= $row['id'] ?>" target="_blank">
                View / Print
            </a>
        </td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="5" align="center">No Paid Fees Found</td>
    </tr>
<?php } ?>

</table>

<br>
<a href="logout.php">Logout</a>

</body>
</html>
