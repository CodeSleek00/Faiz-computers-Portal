<?php
include("db_connect.php");

$enroll = $_GET['enroll'];

// Student info
$student = $conn->query("
    SELECT name FROM student_monthly_fee 
    WHERE enrollment_id='$enroll' LIMIT 1
")->fetch_assoc();

// Fetch all fees
$fees = $conn->query("
    SELECT * FROM student_monthly_fee
    WHERE enrollment_id='$enroll'
    ORDER BY fee_type, month_name
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Fee Details</title>
    <style>
        body { font-family: Arial; background:#f4f6f8; }
        h2 { margin-bottom: 5px; }
        table {
            width:100%;
            border-collapse: collapse;
            background:#fff;
            margin-bottom:25px;
        }
        th, td {
            border:1px solid #ddd;
            padding:10px;
            text-align:center;
        }
        th {
            background:#2c3e50;
            color:white;
        }
        .paid {
            color:green;
            font-weight:bold;
        }
        .pending {
            color:red;
            font-weight:bold;
        }
        .section-title {
            background:#ecf0f1;
            padding:10px;
            font-weight:bold;
            margin-top:25px;
            border-left:5px solid #3498db;
        }
        .pay-btn {
            padding:12px 25px;
            font-size:16px;
            background:#27ae60;
            color:white;
            border:none;
            cursor:pointer;
            border-radius:5px;
        }
    </style>
</head>
<body>

<h2><?= htmlspecialchars($student['name']) ?> (<?= $enroll ?>)</h2>
<p><strong>Fee Status Overview</strong></p>

<form method="POST" action="fee_payment.php">
<input type="hidden" name="enrollment_id" value="<?= $enroll ?>">

<?php
$feeGroups = [
    'Registration' => [],
    'Semester' => [],
    'Monthly' => []
];

// Group fees
while ($row = $fees->fetch_assoc()) {
    $feeGroups[$row['fee_type']][] = $row;
}

foreach ($feeGroups as $type => $items):
if (count($items) == 0) continue;
?>

<div class="section-title"><?= $type ?> Fee</div>

<table>
<tr>
    <th>Select</th>
    <th>Fee Type</th>
    <th>Month / Term</th>
    <th>Amount (₹)</th>
    <th>Status</th>
</tr>

<?php foreach ($items as $f): ?>
<tr>
    <td>
        <?php if ($f['payment_status'] == 'Pending'): ?>
            <input type="checkbox" name="fee_ids[]" value="<?= $f['id'] ?>">
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
    <td><?= $f['fee_type'] ?></td>
    <td><?= $f['month_name'] ?: '-' ?></td>
    <td><?= number_format($f['fee_amount'], 2) ?></td>
    <td class="<?= strtolower($f['payment_status']) ?>">
        <?= $f['payment_status'] ?>
    </td>
</tr>
<?php endforeach; ?>

</table>
<?php endforeach; ?>

<button type="submit" class="pay-btn">Proceed to Payment</button>
</form>

</body>
</html>
