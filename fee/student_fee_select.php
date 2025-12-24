<?php
include("db_connect.php");

$enroll = $_GET['enroll'] ?? '';

// ================= STUDENT INFO =================
$student = $conn->query("
    SELECT name, photo 
    FROM student_monthly_fee 
    WHERE enrollment_id='$enroll' 
    LIMIT 1
")->fetch_assoc();

// fallback safety
$student_name = $student['name'] ?? 'Student';
$photo = (!empty($student['photo']))
    ? "../uploads/students/" . $student['photo']
    : "assets/no-photo.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Fee Details</title>

    <style>
        body{
            margin:0;
            font-family:Segoe UI, Arial;
            background:#f2f4f7;
        }
        .container{
            max-width:1100px;
            margin:30px auto;
            padding:20px;
        }
        .student-card{
            display:flex;
            align-items:center;
            gap:20px;
            background:white;
            padding:20px;
            border-radius:10px;
            box-shadow:0 0 15px rgba(0,0,0,0.08);
            margin-bottom:30px;
        }
        .student-card img{
            width:120px;
            height:120px;
            border-radius:50%;
            object-fit:cover;
            border:4px solid #3498db;
        }
        .student-info h2{
            margin:0;
            color:#2c3e50;
        }
        .student-info p{
            margin:6px 0;
            color:#555;
        }
        .section-title{
            margin:25px 0 10px;
            padding:10px 15px;
            background:#eaf2f8;
            border-left:5px solid #2980b9;
            font-size:18px;
            font-weight:bold;
        }
        table{
            width:100%;
            border-collapse:collapse;
            background:white;
            border-radius:8px;
            overflow:hidden;
            box-shadow:0 0 10px rgba(0,0,0,0.06);
        }
        th{
            background:#2c3e50;
            color:white;
            padding:12px;
        }
        td{
            padding:12px;
            border-bottom:1px solid #eee;
            text-align:center;
        }
        tr:last-child td{
            border-bottom:none;
        }
        .paid{
            color:#27ae60;
            font-weight:bold;
        }
        .pending{
            color:#e74c3c;
            font-weight:bold;
        }
        .pay-btn{
            margin-top:25px;
            background:#27ae60;
            color:white;
            padding:14px 35px;
            font-size:16px;
            border:none;
            border-radius:6px;
            cursor:pointer;
        }
        .pay-btn:hover{
            background:#219150;
        }
    </style>
</head>

<body>

<div class="container">

<!-- ================= STUDENT CARD ================= -->
<div class="student-card">
    <img src="<?= $photo ?>" onerror="this.src='assets/no-photo.png'">
    <div class="student-info">
        <h2><?= htmlspecialchars($student_name) ?></h2>
        <p><strong>Enrollment ID:</strong> <?= htmlspecialchars($enroll) ?></p>
    </div>
</div>

<form method="POST" action="fee_payment.php">
<input type="hidden" name="enrollment_id" value="<?= htmlspecialchars($enroll) ?>">

<?php
// ALL fee categories
$groups = ['Registration', 'Semester', 'Monthly', 'Internal', 'Additional'];

foreach ($groups as $type):

$result = $conn->query("
    SELECT * FROM student_monthly_fee
    WHERE enrollment_id='$enroll'
    AND fee_type='$type'
");

if ($result->num_rows == 0) continue;
?>

<!-- ================= FEE SECTION ================= -->
<div class="section-title"><?= $type ?> Fee</div>

<table>
<tr>
    <th>Select</th>
    <th>Fee Type</th>
    <th>Month / Term</th>
    <th>Amount (₹)</th>
    <th>Status</th>
</tr>

<?php while($f = $result->fetch_assoc()): ?>
<tr>
    <td>
        <?php if($f['payment_status'] === 'Pending'): ?>
            <input type="checkbox" name="fee_ids[]" value="<?= $f['id'] ?>">
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($f['fee_type']) ?></td>
    <td><?= htmlspecialchars($f['month_name'] ?: '-') ?></td>
    <td><?= number_format($f['fee_amount'], 2) ?></td>
    <td class="<?= strtolower($f['payment_status']) ?>">
        <?= htmlspecialchars($f['payment_status']) ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

<?php endforeach; ?>

<button class="pay-btn">Proceed to Payment</button>
</form>

</div>

</body>
</html>
