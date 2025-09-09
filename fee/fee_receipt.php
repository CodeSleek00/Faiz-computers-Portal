<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// GET student_id
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// Student info
$student = $conn->query("SELECT * FROM students WHERE student_id='$student_id'")->fetch_assoc();
if (!$student) die("Student not found.");

// Student fee info
$fee = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'")->fetch_assoc();
if (!$fee) die("No fee record found for this student.");

// Calculate total paid (all exam + month fees)
$total_paid = $fee['internal1'] + $fee['internal2'] + $fee['semester1'] + $fee['semester2'];
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
foreach($months as $m){
    $total_paid += $fee['month_'.$m];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt - <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  body { background: #f8f9fa; }
  .receipt { max-width: 600px; margin: auto; background: #fff; padding: 20px; border: 1px solid #ccc; }
  .receipt img { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; }
  h2, h4 { margin: 0; }
  table { width: 100%; margin-top: 15px; }
  th, td { padding: 5px; text-align: left; }
  .text-right { text-align: right; }
  .total { font-weight: bold; font-size: 1.2em; }
  @media print {
    .no-print { display: none; }
    body { background: #fff; }
  }
</style>
</head>
<body>
<div class="receipt">
  <div class="d-flex align-items-center mb-3">
    <div>
      <?php if(!empty($student['photo']) && file_exists("../uploads/".$student['photo'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Photo">
      <?php else: ?>
        <img src="https://via.placeholder.com/80" alt="No Photo">
      <?php endif; ?>
    </div>
    <div class="ms-3">
      <h2><?php echo htmlspecialchars($student['name']); ?></h2>
      <h4>Enrollment ID: <?php echo htmlspecialchars($student['student_id']); ?></h4>
      <p>Course: <?php echo htmlspecialchars($student['course']); ?></p>
    </div>
  </div>

  <table class="table table-bordered">
    <thead>
      <tr class="table-dark">
        <th>Type</th>
        <th>Amount (₹)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Internal 1</td>
        <td><?php echo number_format($fee['internal1'],2); ?></td>
      </tr>
      <tr>
        <td>Internal 2</td>
        <td><?php echo number_format($fee['internal2'],2); ?></td>
      </tr>
      <tr>
        <td>Semester 1</td>
        <td><?php echo number_format($fee['semester1'],2); ?></td>
      </tr>
      <tr>
        <td>Semester 2</td>
        <td><?php echo number_format($fee['semester2'],2); ?></td>
      </tr>
      <?php foreach($months as $m):
        if($fee['month_'.$m]>0):
      ?>
      <tr>
        <td><?php echo ucfirst($m); ?> Fee</td>
        <td><?php echo number_format($fee['month_'.$m],2); ?></td>
      </tr>
      <?php endif; endforeach; ?>
      <tr class="table-success total">
        <td>Total Paid</td>
        <td>₹<?php echo number_format($total_paid,2); ?></td>
      </tr>
    </tbody>
  </table>
  
  <div class="mt-3 no-print">
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <a href="admin_fee_main.php?student_id=<?php echo $student_id; ?>" class="btn btn-secondary">Back</a>
  </div>
</div>
</body>
</html>
