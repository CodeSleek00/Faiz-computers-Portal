<?php
include '../database_connection/db_connect.php';

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected");

// student details
$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("i", $student_id);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();
if (!$student) die("Student not found");

// fee record
$fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
$fee_q->bind_param("i", $student_id);
$fee_q->execute();
$fee = $fee_q->get_result()->fetch_assoc();
if (!$fee) die("No fee record found");

// identify which fee was just paid
$fee_type = "Unknown Fee";
$fee_amount = 0.00;

// check priority (admission > internal > semester > monthly)
if ($fee['admission_fee'] > 0) {
    $fee_type = "Admission Fee";
    $fee_amount = $fee['admission_fee'];
} elseif ($fee['internal1'] > 0) {
    $fee_type = "Internal Exam 1 Fee";
    $fee_amount = $fee['internal1'];
} elseif ($fee['internal2'] > 0) {
    $fee_type = "Internal Exam 2 Fee";
    $fee_amount = $fee['internal2'];
} elseif ($fee['semester1'] > 0) {
    $fee_type = "Semester 1 Fee";
    $fee_amount = $fee['semester1'];
} elseif ($fee['semester2'] > 0) {
    $fee_type = "Semester 2 Fee";
    $fee_amount = $fee['semester2'];
} else {
    // check monthly
    $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
    foreach ($months as $m) {
        if ($fee['month_'.$m] > 0) {
            $fee_type = ucfirst($m) . " Fee";
            $fee_amount = $fee['month_'.$m];
            break;
        }
    }
}

$payment_date = $fee['payment_date'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  body { background: #f8f9fa; }
  .receipt { max-width: 600px; margin: 40px auto; padding: 20px; background: #fff; border: 1px solid #ddd; }
</style>
</head>
<body>
<div class="receipt">
  <h3 class="text-center">Fee Receipt</h3>
  <hr>
  <p><strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['student_id']; ?>)</p>
  <p><strong>Fee Type:</strong> <?php echo htmlspecialchars($fee_type); ?></p>
  <p><strong>Amount Paid:</strong> ₹<?php echo number_format($fee_amount, 2); ?></p>
  <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($payment_date); ?></p>
  <hr>
  <p class="text-center">Thank you for your payment!</p>
</div>
</body>
</html>
