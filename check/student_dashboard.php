<?php
// 🚨 ABSOLUTELY NOTHING before this (no space, no HTML)
session_start();

// Debug (remove later)
if (!isset($_SESSION)) {
    die("Session not started");
}

if (!isset($_SESSION['student_enroll'])) {
    header("Location: student_login.php");
    exit;
}

include("db_connect.php"); // correct path

$enroll = $_SESSION['student_enroll'];

$student = $conn->query("
    SELECT * FROM students26 
    WHERE enrollment_id='$enroll' 
    LIMIT 1
")->fetch_assoc();

if (!$student) {
    die("Student record not found");
}
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
