<?php
include 'db_connect.php';

$student_id = $_GET['student_id'];
$student = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();
$fees = $conn->query("SELECT * FROM student_fees WHERE student_id=$student_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Records</title>
</head>
<body>
<h2>Fee Records - <?php echo $student['name']; ?> (<?php echo $student['enrollment_id']; ?>)</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>Month</th>
        <th>Amount</th>
        <th>Date</th>
    </tr>
    <?php while($row = $fees->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['month']; ?></td>
        <td><?php echo $row['amount']; ?></td>
        <td><?php echo $row['created_at']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
