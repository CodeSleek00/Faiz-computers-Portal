<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$students = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Fee Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
table td, table th { vertical-align: middle; text-align:center; }
img { width:50px; height:50px; object-fit:cover; border-radius:50%; }
</style>
</head>
<body>
<div class="container my-4">
<h2>Students Fee Dashboard</h2>
<table class="table table-bordered table-striped mt-3">
<thead class="table-dark">
<tr>
<th>Photo</th>
<th>Enrollment No</th>
<th>Name</th>
<th>Course</th>
<th>Total Fee</th>
<th>Paid Fee</th>
<th>Show Fee</th>
<th>Set Fee</th>
<th>Complete Course</th>
</tr>
</thead>
<tbody>
<?php while($row = $students->fetch_assoc()):
$fee_res = $conn->query("SELECT * FROM student_fees WHERE student_id=".$row['student_id']." ORDER BY created_at DESC LIMIT 1");
$fee = $fee_res->fetch_assoc();
$total_paid = 0;
if($fee){
$total_paid = $fee['admission_fee'] + $fee['internal1'] + $fee['internal2'] + $fee['semester1'] + $fee['semester2'];
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
foreach($months as $m) $total_paid += $fee['month_'.$m];
}
?>
<tr>
<td><?php if(!empty($row['photo']) && file_exists("../uploads/".$row['photo'])): ?>
<img src="../uploads/<?php echo $row['photo']; ?>">
<?php else: ?>
<img src="https://via.placeholder.com/50">
<?php endif; ?></td>
<td><?php echo $row['student_id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['course']; ?></td>
<td><?php echo $fee['total_fee'] ?? 0; ?></td>
<td><?php echo $total_paid; ?></td>
<td><a class="btn btn-info btn-sm" href="show_fee.php?student_id=<?php echo $row['student_id']; ?>">Show Fee</a></td>
<td><a class="btn btn-success btn-sm" href="admin_fee_main.php?student_id=<?php echo $row['student_id']; ?>">Set Fee</a></td>
<td><a class="btn btn-warning btn-sm" href="#">Complete</a></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</body>
</html>
