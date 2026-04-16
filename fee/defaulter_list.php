<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db_connect.php");

/* ================= MONTH ================= */
$month_no = $_GET['month_no'] ?? date('n');
$monthName = date('F', mktime(0,0,0,$month_no,1));

/* ================= QUERY ================= */
$sql = "

SELECT 
    s2.name,
    s2.enrollment_id,
    s2.course AS course_name,
    s2.photo,
    'students26' AS tbl
FROM students26 s2
LEFT JOIN student_monthly_fee f 
    ON f.enrollment_id = s2.enrollment_id
    AND f.month_no = '$month_no'
    AND f.fee_type='Monthly'
WHERE f.payment_status IS NULL OR f.payment_status != 'Paid'

ORDER BY name ASC
";

$defaulters = $conn->query($sql);

if(!$defaulters){
    die("SQL Error: " . $conn->error);
}

$total = $defaulters->num_rows;
?>

<!DOCTYPE html>
<html>
<head>
<title><?= $monthName ?> Defaulters</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    font-family:Poppins;
    background:#f1f5f9;
    padding:20px;
}
.box{
    background:#fff;
    padding:20px;
    border-radius:10px;
    max-width:900px;
    margin:auto;
}
h2{
    text-align:center;
    color:#4f46e5;
}
.count{
    text-align:center;
    margin-bottom:10px;
    font-weight:600;
}
.search{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
    margin-bottom:10px;
}
.table{
    width:100%;
    border-collapse:collapse;
}
.table th{
    background:#4f46e5;
    color:#fff;
    padding:10px;
}
.table td{
    padding:10px;
    border-bottom:1px solid #ddd;
}
.student{
    display:flex;
    align-items:center;
    gap:10px;
}
.student img{
    width:40px;
    height:40px;
    border-radius:50%;
    object-fit:cover;
}
.badge{
    font-size:12px;
    background:#e5e7eb;
    padding:2px 6px;
    border-radius:5px;
}
</style>
</head>

<body>

<div class="box">

<h2><?= $monthName ?> Defaulters</h2>

<div class="count">
Total Defaulters: <?= $total ?>
</div>

<!-- 🔍 SEARCH -->
<input type="text" id="search" class="search" placeholder="Search student...">

<table class="table" id="table">
<tr>
    <th>Student</th>
    <th>Enrollment</th>
    <th>Course</th>
</tr>

<?php if($total > 0): ?>
<?php while($d = $defaulters->fetch_assoc()): ?>
<tr class="row">

<td>
<div class="student">
<img src="<?= $d['photo'] ? $d['photo'] : 'default.png' ?>">
<div>
<b><?= htmlspecialchars($d['name']) ?></b><br>
<span class="badge"><?= $d['tbl'] ?></span>
</div>
</div>
</td>

<td><?= htmlspecialchars($d['enrollment_id']) ?></td>
<td><?= htmlspecialchars($d['course_name']) ?></td>

</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="3" style="text-align:center">No Defaulters Found</td>
</tr>
<?php endif; ?>

</table>

</div>

<script>
// 🔍 LIVE SEARCH
document.getElementById('search').addEventListener('keyup', function(){
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.row');

    rows.forEach(row=>{
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>