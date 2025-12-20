<?php
include("db_connect.php");

/* ================= FETCH ALL STUDENTS WITH FEES ================= */
$students = $conn->query("
    SELECT DISTINCT enrollment_id, name, photo, course_name
    FROM student_monthly_fee
    ORDER BY name ASC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<style>
body{font-family:Arial;background:#f4f6f8;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{border:1px solid #ccc;padding:8px;text-align:center;}
th{background:#0d6efd;color:#fff;}
button{padding:5px 10px;border:none;background:#198754;color:#fff;cursor:pointer;border-radius:4px;}
button.pay-btn{background:#0d6efd;}
img{width:50px;height:50px;border-radius:50%;}
.status-paid{color:green;font-weight:bold;}
.status-pending{color:red;font-weight:bold;}
</style>
</head>
<body>

<h2>Student Dashboard</h2>

<table>
    <tr>
        <th>Photo</th>
        <th>Name</th>
        <th>Enrollment ID</th>
        <th>Course</th>
        <th>Fee Type</th>
        <th>Month</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Payment Date</th>
        <th>Action</th>
    </tr>

    <?php
    while($student = $students->fetch_assoc()):

        // Fetch all fees for this student
        $fees = $conn->query("SELECT * FROM student_monthly_fee WHERE enrollment_id='".$student['enrollment_id']."' ORDER BY month_no ASC, fee_type ASC");
        while($fee = $fees->fetch_assoc()):
    ?>
    <tr>
        <td><img src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="photo"></td>
        <td><?= htmlspecialchars($student['name']) ?></td>
        <td><?= htmlspecialchars($student['enrollment_id']) ?></td>
        <td><?= htmlspecialchars($student['course_name']) ?></td>
        <td><?= htmlspecialchars($fee['fee_type']) ?></td>
        <td><?= htmlspecialchars($fee['month_name'] ?? '-') ?></td>
        <td>₹<?= number_format($fee['fee_amount'],2) ?></td>
        <td class="status-<?= strtolower($fee['payment_status']) ?>"><?= htmlspecialchars($fee['payment_status']) ?></td>
        <td><?= $fee['payment_date'] ? date('d-M-Y', strtotime($fee['payment_date'])) : '-' ?></td>
        <td>
            <?php if($fee['payment_status']=='Pending'): ?>
                <a href="submit_monthly_fee.php?fee_id=<?= $fee['id'] ?>"><button class="pay-btn">Pay Now</button></a>
            <?php else: ?>
                Paid
            <?php endif; ?>
        </td>
    </tr>
    <?php
        endwhile;
    endwhile;
    ?>

</table>

</body>
</html>
