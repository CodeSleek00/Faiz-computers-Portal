<?php
include("db_connect.php");

// Date select logic
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Day-wise fee list
$fees = $conn->query("
    SELECT name, enrollment_id, fee_type, month_name, fee_amount, payment_mode
    FROM student_monthly_fee
    WHERE payment_status='Paid'
    AND payment_date='$selected_date'
");

// Day-end total
$total = $conn->query("
    SELECT SUM(fee_amount) AS total
    FROM student_monthly_fee
    WHERE payment_status='Paid'
    AND payment_date='$selected_date'
")->fetch_assoc();

// Cash / Online breakup
$modes = $conn->query("
    SELECT payment_mode, SUM(fee_amount) AS amount
    FROM student_monthly_fee
    WHERE payment_status='Paid'
    AND payment_date='$selected_date'
    GROUP BY payment_mode
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Day End Fee Report</title>
    <style>
        body { font-family: Arial; background:#f5f6fa; padding:20px; }
        h2 { margin-bottom:10px; }
        table { width:100%; border-collapse:collapse; background:#fff; }
        th, td { padding:10px; border:1px solid #ccc; text-align:center; }
        th { background:#2f3640; color:#fff; }
        .total-box {
            background:#27ae60;
            color:#fff;
            padding:15px;
            font-size:20px;
            margin-top:15px;
            text-align:center;
        }
        .filter-box {
            margin-bottom:15px;
            background:#fff;
            padding:10px;
        }
    </style>
</head>

<body>

<h2>📅 Day End Fee Report</h2>

<div class="filter-box">
    <form method="GET">
        <label>Select Date:</label>
        <input type="date" name="date" value="<?= $selected_date ?>">
        <button type="submit">Check</button>
        <a href="day_end_fee_report.php">Today</a>
    </form>
</div>

<table>
<tr>
    <th>Name</th>
    <th>Enrollment</th>
    <th>Fee Type</th>
    <th>Month</th>
    <th>Amount</th>
    <th>Mode</th>
</tr>

<?php if($fees->num_rows > 0): ?>
    <?php while($row = $fees->fetch_assoc()): ?>
    <tr>
        <td><?= $row['name'] ?></td>
        <td><?= $row['enrollment_id'] ?></td>
        <td><?= $row['fee_type'] ?></td>
        <td><?= $row['month_name'] ?? '-' ?></td>
        <td>₹<?= $row['fee_amount'] ?></td>
        <td><?= $row['payment_mode'] ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="6">No payment found for this date</td>
</tr>
<?php endif; ?>
</table>

<div class="total-box">
    Day End Total: ₹<?= $total['total'] ?? 0 ?>
</div>

<h3>💳 Payment Mode Summary</h3>
<table>
<tr>
    <th>Mode</th>
    <th>Amount</th>
</tr>
<?php while($m = $modes->fetch_assoc()): ?>
<tr>
    <td><?= $m['payment_mode'] ?></td>
    <td>₹<?= $m['amount'] ?></td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
