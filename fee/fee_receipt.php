<?php
// fee_receipt.php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// fetch student (for photo etc.)
$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("s", $student_id);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();
if (!$student) die("Student not found.");

// get latest payment from fee_payments
$payment_q = $conn->prepare("SELECT * FROM fee_payments WHERE student_id = ? ORDER BY payment_date DESC, id DESC LIMIT 1");
$payment_q->bind_param("s", $student_id);
$payment_q->execute();
$payment = $payment_q->get_result()->fetch_assoc();
$payment_q->close();

// map labels
$labels = [
    'admission_fee' => 'Admission Fee',
    'internal1' => 'Internal Exam 1',
    'internal2' => 'Internal Exam 2',
    'semester1' => 'Semester 1',
    'semester2' => 'Semester 2',
    'month_jan' => 'Monthly Fee (January)',
    'month_feb' => 'Monthly Fee (February)',
    'month_mar' => 'Monthly Fee (March)',
    'month_apr' => 'Monthly Fee (April)',
    'month_may' => 'Monthly Fee (May)',
    'month_jun' => 'Monthly Fee (June)',
    'month_jul' => 'Monthly Fee (July)',
    'month_aug' => 'Monthly Fee (August)',
    'month_sep' => 'Monthly Fee (September)',
    'month_oct' => 'Monthly Fee (October)',
    'month_nov' => 'Monthly Fee (November)',
    'month_dec' => 'Monthly Fee (December)'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  body { background: #f8f9fa; }
  .receipt { max-width: 700px; margin: 40px auto; padding: 20px; background: #fff; border: 1px solid #ddd; }
  .student-photo { width: 90px; height: 90px; object-fit: cover; border-radius: 50%; border: 2px solid #ccc; }
  @media print { .no-print { display: none; } body { background: #fff; } }
</style>
</head>
<body>
<div class="receipt">
  <h3 class="text-center mb-3">FAIZ COMPUTER INSTITUTE</h3>
  <h3 class="text-center mb-3">Fee Receipt</h3>
  <hr>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <p><strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
      <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
    </div>
    <div>
      <?php if(!empty($student['photo']) && file_exists("../uploads/".$student['photo'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo" alt="Photo">
      <?php else: ?>
        <img src="https://via.placeholder.com/90" class="student-photo" alt="No Photo">
      <?php endif; ?>
    </div>
  </div>

  <?php if ($payment): ?>
    <table class="table table-bordered">
      <thead><tr><th>Fee Type</th><th>Amount (₹)</th><th>Date</th></tr></thead>
      <tbody>
        <tr>
          <td><?php echo $labels[$payment['fee_type']] ?? $payment['fee_type']; ?></td>
          <td><?php echo number_format((float)$payment['amount'], 2); ?></td>
          <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
        </tr>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No payment found for this student.</div>
  <?php endif; ?>

  <p class="text-center mt-3">Thank you for your payment!</p>

  <div class="text-center mt-3 no-print">
      <button class="btn btn-primary" onclick="window.print()">🖨 Print Receipt</button>
      <a class="btn btn-secondary" href="admin_fee_dashboard.php">Back</a>
  </div>
</div>
</body>
</html>
