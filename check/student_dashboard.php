<?php
include("auth_check.php");
include("db_connect.php");

$enroll = $_SESSION['student_enroll'];

$student = $conn->query("
    SELECT * FROM students WHERE enrollment_id='$enroll'
")->fetch_assoc();

$fees = $conn->query("
    SELECT * FROM student_fee_payments
    WHERE enrollment_id='$enroll'
    ORDER BY payment_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>

<h2>Welcome, <?= $student['name'] ?></h2>

<p>
<b>Enrollment:</b> <?= $student['enrollment_id'] ?><br>
<b>Course:</b> <?= $student['course'] ?><br>
<b>Phone:</b> <?= $student['phone'] ?>
</p>

<h3>Fee Receipts</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Date</th>
    <th>Fee Type</th>
    <th>Month</th>
    <th>Amount</th>
    <th>Receipt</th>
</tr>

<?php while($row = $fees->fetch_assoc()) { ?>
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

</table>

<br>
<a href="logout.php">Logout</a>

</body>
</html>
