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
<title>Student Dashboard</title>
<style>
body{font-family:Arial;background:#f4f6f8;padding:20px;}
table{width:100%;border-collapse:collapse;}
th,td{border:1px solid #ccc;padding:10px;text-align:left;}
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

<h2>Student Dashboard</h2>

<table>
<tr>
    <th>Photo</th>
    <th>Name</th>
    <th>Course</th>
    <th>Action</th>
</tr>

<?php while($student = $students->fetch_assoc()): ?>
<tr>
    <td><img src="uploads/<?= htmlspecialchars($student['photo']) ?>" alt="photo"></td>
    <td><?= htmlspecialchars($student['name']) ?></td>
    <td><?= htmlspecialchars($student['course_name']) ?></td>
    <td>
        <button class="toggle-btn" onclick="toggleFees('<?= $student['enrollment_id'] ?>')">View Fees</button>
        <div id="fees_<?= $student['enrollment_id'] ?>" style="display:none;">
            <?php
            $fees = $conn->query("SELECT * FROM student_monthly_fee WHERE enrollment_id='".$student['enrollment_id']."' ORDER BY fee_type, month_no ASC");
            while($fee = $fees->fetch_assoc()):
            ?>
            <div class="fee-box">
                <?= htmlspecialchars($fee['fee_type']) ?>
                <?php if(!empty($fee['month_name'])): ?>
                    - <?= htmlspecialchars($fee['month_name']) ?>
                <?php endif; ?>
                : ₹<?= number_format($fee['fee_amount'],2) ?> -
                <span class="fee-<?= strtolower($fee['payment_status']) ?>"><?= $fee['payment_status'] ?></span>
                <?php if($fee['payment_status']=='Pending'): ?>
                    <a href="submit_monthly_fee.php?fee_id=<?= $fee['id'] ?>"><button>Pay Now</button></a>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>
