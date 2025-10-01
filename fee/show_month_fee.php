<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$month = $_GET['month'] ?? '';
$valid_months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
if(!in_array(strtolower($month), $valid_months)) die("Invalid month");

$month_col = "month_".strtolower($month);

$fees = $conn->query("SELECT s.name,s.student_id,s.course,f.$month_col AS amount,f.payment_date
                      FROM students s
                      JOIN student_fees f ON s.student_id=f.student_id
                      WHERE f.$month_col>0
                      ORDER BY f.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo ucfirst($month); ?> Fees</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container my-4">
<h2><?php echo ucfirst($month); ?> Fee Payments</h2>
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>Enrollment No</th>
<th>Name</th>
<th>Course</th>
<th>Amount (₹)</th>
<th>Payment Date</th>
</tr>
</thead>
<tbody>
<?php while($row = $fees->fetch_assoc()): ?>
<tr>
<td><?php echo $row['student_id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['course']; ?></td>
<td><?php echo $row['amount']; ?></td>
<td><?php echo $row['payment_date']; ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<a class="btn btn-secondary" href="admin_fee_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
