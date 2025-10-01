<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// Get student ID
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// Fetch student info
$student = $conn->query("SELECT * FROM students WHERE student_id='$student_id'")->fetch_assoc();
if (!$student) die("Student not found.");

// Fetch student fee info
$fee = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'")->fetch_assoc();
if (!$fee) die("No fee record found for this student.");

// Months array
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Details - <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
body { background: #f8f9fa; }
.card { max-width: 900px; margin: 40px auto; padding: 20px; }
table th, table td { vertical-align: middle; }
img.photo { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; }
.no-print { margin-top: 10px; }
</style>
</head>
<body>
<div class="card shadow-sm">
  <div class="d-flex align-items-center mb-3">
    <div>
      <?php if(!empty($student['photo']) && file_exists("../uploads/".$student['photo'])): ?>
        <img src="../uploads/<?php echo $student['photo']; ?>" alt="Photo" class="photo">
      <?php else: ?>
        <img src="https://via.placeholder.com/80" alt="No Photo" class="photo">
      <?php endif; ?>
    </div>
    <div class="ms-3">
      <h3><?php echo htmlspecialchars($student['name']); ?></h3>
      <p>Enrollment ID: <?php echo $student['student_id']; ?> | Course: <?php echo $student['course']; ?></p>
    </div>
  </div>

  <table class="table table-bordered">
    <thead class="table-light">
      <tr><th>Fee Type</th><th>Amount (₹)</th><th>Paid Date</th></tr>
    </thead>
    <tbody>
      <?php if($fee['admission_fee']>0): ?>
      <tr><td>Admission Fee</td><td><?php echo number_format($fee['admission_fee'],2); ?></td><td><?php echo $fee['payment_date']; ?></td></tr>
      <?php endif; ?>
      <?php if($fee['internal1']>0): ?>
      <tr><td>Internal 1</td><td><?php echo number_format($fee['internal1'],2); ?></td><td><?php echo $fee['payment_date']; ?></td></tr>
      <?php endif; ?>
      <?php if($fee['internal2']>0): ?>
      <tr><td>Internal 2</td><td><?php echo number_format($fee['internal2'],2); ?></td><td><?php echo $fee['payment_date']; ?></td></tr>
      <?php endif; ?>
      <?php if($fee['semester1']>0): ?>
      <tr><td>Semester 1</td><td><?php echo number_format($fee['semester1'],2); ?></td><td><?php echo $fee['payment_date']; ?></td></tr>
      <?php endif; ?>
      <?php if($fee['semester2']>0): ?>
      <tr><td>Semester 2</td><td><?php echo number_format($fee['semester2'],2); ?></td><td><?php echo $fee['payment_date']; ?></td></tr>
      <?php endif; ?>
      <?php foreach($months as $m):
        if($fee['month_'.$m]>0):
      ?>
      <tr>
        <td><?php echo ucfirst($m); ?> Fee</td>
        <td><?php echo number_format($fee['month_'.$m],2); ?></td>
        <td><?php echo $fee['month_'.$m.'_date'] ?? $fee['payment_date']; ?></td>
      </tr>
      <?php endif; endforeach; ?>
      <tr class="table-success fw-bold">
        <td>Total Paid</td>
        <td colspan="2">
          ₹<?php 
            $total = $fee['admission_fee']+$fee['internal1']+$fee['internal2']+$fee['semester1']+$fee['semester2'];
            foreach($months as $m) $total += $fee['month_'.$m];
            echo number_format($total,2);
          ?>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="no-print">
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <a href="admin_fee_dashboard.php" class="btn btn-secondary">Back</a>
  </div>
</div>
</body>
</html>
