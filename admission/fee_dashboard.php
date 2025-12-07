<?php
// fee_dashboard.php
include "db.php";

$enrollment_id = isset($_GET['enrollment_id']) ? mysqli_real_escape_string($conn, $_GET['enrollment_id']) : '';

if (!$enrollment_id) {
    echo "Please pass enrollment_id in URL. Example: ?enrollment_id=FAIZ-JAN26-1001";
    exit;
}

// student info
$sq = mysqli_query($conn, "SELECT * FROM student_2026 WHERE enrollment_id='".$enrollment_id."'");
$student = mysqli_fetch_assoc($sq);

if (!$student) {
    echo "Student not found";
    exit;
}

// fees
$res = mysqli_query($conn, "SELECT * FROM fee_structure WHERE enrollment_id='".$enrollment_id."' ORDER BY id ASC");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fee Dashboard - <?= htmlspecialchars($student['name']) ?></title>
    <style>
        body{font-family:Arial;padding:20px;background:#f4f4f4}
        .box{width:1000px;margin:auto;background:#fff;padding:20px;border-radius:6px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:10px;border:1px solid #ddd;text-align:left}
        .paid{color:green;font-weight:bold}
        .unpaid{color:red;font-weight:bold}
        .pay-btn{padding:6px 10px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer}
    </style>
</head>
<body>
<div class="box">
    <h2>Fee Dashboard</h2>
    <p><b>Student:</b> <?= htmlspecialchars($student['name']) ?> &nbsp; <b>Enrollment:</b> <?= htmlspecialchars($student['enrollment_id']) ?></p>

    <table>
        <thead>
            <tr><th>#</th><th>Fee Type</th><th>Month</th><th>Amount (₹)</th><th>Status</th><th>Paid Date</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php $i=1;
        while($row = mysqli_fetch_assoc($res)) {
            echo "<tr>";
            echo "<td>".$i++."</td>";
            echo "<td>".htmlspecialchars(ucwords(str_replace("_"," ", $row['fee_type'])))."</td>";
            echo "<td>".($row['month_name']?htmlspecialchars($row['month_name']):'-')."</td>";
            echo "<td>".number_format($row['amount'])."</td>";
            echo "<td>".($row['status']=='paid'?'<span class=\"paid\">Paid</span>':'<span class=\"unpaid\">Unpaid</span>')."</td>";
            echo "<td>".($row['paid_date']?htmlspecialchars($row['paid_date']):'-')."</td>";
            echo "<td>";
            if ($row['status'] != 'paid') {
                // show simple form to pay (POST to pay_fee.php)
                echo '<form method="post" action="pay_fee.php" style="display:inline-block">';
                echo '<input type="hidden" name="fee_id" value="'.$row['id'].'">';
                echo '<input type="hidden" name="enrollment_id" value="'.htmlspecialchars($enrollment_id).'">';
                echo '<select name="payment_mode" required>
                        <option value="">Payment Mode</option>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI/Online</option>
                      </select> ';
                echo '<button type="submit" class="pay-btn">Mark Paid</button>';
                echo '</form>';
            } else {
                echo '<a href="fee_receipt.php?fee_id='.$row['id'].'">View Receipt</a>';
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

</div>
</body>
</html>
