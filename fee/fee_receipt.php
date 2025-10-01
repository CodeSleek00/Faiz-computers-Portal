<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$student_id = $_GET['student_id'] ?? '';
if(!$student_id) die("Student not selected");

$student = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();
if(!$student) die("Student not found");

$fee = $conn->query("SELECT * FROM student_fees WHERE student_id=$student_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
if(!$fee) die("No fee records found");

$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

$total_paid = $fee['admission_fee']+$fee['internal1']+$fee['internal2']+$fee['semester1']+$fee['semester2'];
foreach($months as $m) $total_paid += $fee['month_'.$m];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt - <?php echo $student['name']; ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
body { background:#f0f2f5; }
.card { max-width:800px; margin:40px auto; padding:20px; }
img { width:80px; height:80px; object-fit:cover; border-radius:50%; float:right; }
</style>
</head>
<body>
<div class="card shadow-sm">
<h3>Fee Receipt</h3>
<img src="<?php echo (!empty($student['photo']) && file_exists("../uploads/".$student['photo'])) ? "../uploads/".$student['photo'] : 'https://via.placeholder.com/80'; ?>" alt="Student Photo">
<p><strong>Student:</strong> <?php echo $student['name']; ?> (<?php echo $student['student_id']; ?>)<br>
<strong>Course:</strong> <?php echo $student['course']; ?><br>
<strong>Date:</strong> <?php echo $fee['payment_date']; ?></p>

<table class="table table-bordered">
<tr><th>Fee Type</th><th>Amount (₹)</th></tr>
<tr><td>Total Fee</td><td><?php echo $fee['total_fee']; ?></td></tr>
<tr><td>Admission Fee</td><td><?php echo $fee['admission_fee']; ?></td></tr>
<tr><td>Internal 1</td><td><?php echo $fee['internal1']; ?></td></tr>
<tr><td>Internal 2</td><td><?php echo $fee['internal2']; ?></td></tr>
<tr><td>Semester 1</td><td><?php echo $fee['semester1']; ?></td></tr>
<tr><td>Semester 2</td><td><?php echo $fee['semester2']; ?></td></tr>
<tr><td colspan="2" class="table-active text-center">Monthly Fees</td></tr>
<?php foreach($months as $m):
if($fee['month_'.$m]>0): ?>
<tr><td><?php echo ucfirst($m); ?></td><td><?php echo $fee['month_'.$m]; ?></td></tr>
<?php endif; endforeach; ?>
<tr class="table-success"><td><strong>Total Paid</strong></td><td><strong><?php echo $total_paid; ?></strong></td></tr>
</table>

<div class="d-flex gap-2">
<button class="btn btn-primary" onclick="window.print()">Print</button>
<a class="btn btn-success" href="https://wa.me/<?php echo $student['contact_number']; ?>?text=<?php echo urlencode('Hello '.$student['name'].', your fee receipt has been generated. Total Paid: ₹'.$total_paid); ?>" target="_blank">Share on WhatsApp</a>
<a class="btn btn-secondary" href="admin_fee_dashboard.php">Back</a>
</div>

</div>
</body>
</html>
