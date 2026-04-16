<?php
include("db_connect.php");

/* ================= MONTH SET ================= */
$month_no = $_GET['month_no'] ?? date('n');
$monthName = date('F', mktime(0,0,0,$month_no,1));

/* ================= DEFAULTERS QUERY ================= */
$defaulters = $conn->query("

    /* STUDENTS TABLE */
    SELECT 
        s.student_id AS sid,
        s.name,
        s.enrollment_id,
        s.course AS course_name,
        s.photo,
        'students' AS tbl
    FROM students s
    WHERE s.student_id NOT IN (
        SELECT student_id 
        FROM student_monthly_fee 
        WHERE month_no='$month_no'
        AND fee_type='Monthly'
        AND payment_status='Paid'
    )

    UNION ALL

    /* STUDENTS26 TABLE */
    SELECT 
        s2.id AS sid,
        s2.name,
        s2.enrollment_id,
        s2.course AS course_name,
        s2.photo,
        'students26' AS tbl
    FROM students26 s2
    WHERE s2.id NOT IN (
        SELECT student_id 
        FROM student_monthly_fee 
        WHERE month_no='$month_no'
        AND fee_type='Monthly'
        AND payment_status='Paid'
    )

    ORDER BY name ASC

");

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