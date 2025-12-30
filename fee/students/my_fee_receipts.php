<?php
session_start();
include '../../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: ../../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];

$stmt = $conn->prepare("
    SELECT * 
    FROM student_monthly_fee 
    WHERE enrollment_id = ?
    ORDER BY paid_date DESC
");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Fee Receipts</h2>

<table border="1" cellpadding="8" width="100%">
<tr>
    <th>Receipt No</th>
    <th>Month</th>
    <th>Amount</th>
    <th>Paid Date</th>
    <th>Mode</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?= $row['receipt_no']; ?></td>
    <td><?= $row['month']; ?></td>
    <td>₹<?= $row['amount']; ?></td>
    <td><?= $row['paid_date']; ?></td>
    <td><?= $row['payment_mode']; ?></td>
    <td><?= $row['status']; ?></td>
    <td>
        <a href="view_fee_receipt.php?id=<?= $row['id']; ?>">View</a>
    </td>
</tr>
<?php } ?>
</table>
