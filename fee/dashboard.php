<?php
include("db_connect.php");

/* ================= FETCH ALL STUDENTS ================= */
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
<title>Student Dashboard - Fee Payment</title>
<style>
body{font-family:Arial;background:#f4f6f8;padding:20px;}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #ccc;padding:10px;text-align:left;vertical-align:top;}
th{background:#0d6efd;color:#fff;}
img{width:50px;height:50px;border-radius:50%;}
button{padding:5px 10px;border:none;background:#0d6efd;color:#fff;cursor:pointer;border-radius:4px;margin:2px;}
.fee-box{background:#f9f9f9;padding:5px;margin-top:5px;border-radius:4px;}
.fee-paid{color:green;}
.fee-pending{color:red;}
.toggle-btn{background:#198754;}
</style>
<script>
function toggleFees(id){
    var div = document.getElementById("fees_"+id);
    if(div.style.display==="none") div.style.display="block";
    else div.style.display="none";
}
</script>
</head>
<body>

<h2>Student Dashboard - Multiple Fee Payment</h2>

<form method="POST" action="submit_multiple_fees.php">

<table>
<tr>
    <th>Photo</th>
    <th>Name</th>
    <th>Course</th>
    <th>Action (Select Fees to Pay)</th>
    <th>Paid Fees Details</th>
</tr>

<?php while($student = $students->fetch_assoc()): ?>
<tr>
    <td><img src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="photo"></td>
    <td><?= htmlspecialchars($student['name']) ?></td>
    <td><?= htmlspecialchars($student['course_name']) ?></td>
    <td>
        <button type="button" class="toggle-btn" onclick="toggleFees('<?= $student['enrollment_id'] ?>')">View Pending Fees</button>
        <div id="fees_<?= $student['enrollment_id'] ?>" style="display:none;">
            <?php
            $fees = $conn->query("SELECT * FROM student_monthly_fee WHERE enrollment_id='".$student['enrollment_id']."' ORDER BY fee_type, month_no ASC");
            while($fee = $fees->fetch_assoc()):
                if($fee['payment_status']=='Pending'):
            ?>
            <div class="fee-box">
                <input type="checkbox" name="fee_ids[]" value="<?= $fee['id'] ?>">
                <?= htmlspecialchars($fee['fee_type']) ?>
                <?php if(!empty($fee['month_name'])): ?>
                    - <?= htmlspecialchars($fee['month_name']) ?>
                <?php endif; ?>
                : ₹<?= number_format($fee['fee_amount'],2) ?>
            </div>
            <?php
                endif;
            endwhile;
            ?>
        </div>
    </td>
    <td>
        <?php
        $paid_fees = $conn->query("SELECT * FROM student_monthly_fee WHERE enrollment_id='".$student['enrollment_id']."' AND payment_status='Paid' ORDER BY fee_type, month_no ASC");
        if($paid_fees->num_rows > 0){
            while($pf = $paid_fees->fetch_assoc()){
                echo "<div class='fee-box fee-paid'>";
                echo htmlspecialchars($pf['fee_type']);
                if(!empty($pf['month_name'])){
                    echo " - ".htmlspecialchars($pf['month_name']);
                }
                echo ": ₹".number_format($pf['fee_amount'],2);
                echo " (".date('d-M-Y', strtotime($pf['payment_date'])).")";
                echo "</div>";
            }
        }else{
            echo "-";
        }
        ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

<br>
<button type="submit">Pay Selected Fees</button>
</form>

</body>
</html>
