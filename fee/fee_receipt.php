<?php
// fee_receipt.php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// get student id
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) {
    echo "<div style='padding:20px; color:red;'>No student selected.</div>";
    exit;
}

// fetch student details
$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("i", $student_id);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();
$student_q->close();

if (!$student) {
    echo "<div style='padding:20px; color:red;'>Student not found.</div>";
    exit;
}

// label map
$labels = [
    'total_fee' => 'Total Fee',
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

// 1) Try to get latest from fee_payments (preferred, single-payment-per-row model)
$payment_q = $conn->prepare("SELECT * FROM fee_payments WHERE student_id = ? ORDER BY payment_date DESC, id DESC LIMIT 1");
$payment_q->bind_param("i", $student_id);
$payment_q->execute();
$payment = $payment_q->get_result()->fetch_assoc();
$payment_q->close();

$display_changes = []; // associative: field => amount
$payment_date = date('Y-m-d');

if ($payment) {
    // fee_payments row exists -> show only that payment
    $ft = $payment['fee_type'] ?? null;
    $amt = isset($payment['amount']) ? (float)$payment['amount'] : 0.0;
    if ($ft && $amt > 0) {
        $display_changes[$ft] = $amt;
        $payment_date = $payment['payment_date'] ?? $payment_date;
    }
} else {
    // 2) Fallback: read student_fees row and gather all non-zero fields
    $fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ? LIMIT 1");
    $fee_q->bind_param("i", $student_id);
    $fee_q->execute();
    $fee = $fee_q->get_result()->fetch_assoc();
    $fee_q->close();

    if ($fee) {
        // build display_changes from student_fees non-zero fields
        foreach ($labels as $field => $label) {
            if (isset($fee[$field]) && $fee[$field] !== null && $fee[$field] != 0) {
                $display_changes[$field] = (float)$fee[$field];
            }
        }
        // if there is a payment_date field in student_fees, use it
        if (!empty($fee['payment_date'])) {
            $payment_date = $fee['payment_date'];
        } elseif (!empty($fee['last_updated'])) {
            // optional fallback if you have last_updated timestamp
            $payment_date = date('Y-m-d', strtotime($fee['last_updated']));
        }
    }
}

// If still empty -> no payment found
if (empty($display_changes)) {
    echo "<div style='padding:20px; color:#333; background:#fff; max-width:700px; margin:40px auto; border:1px solid #eee; border-radius:8px;'>
            <h3 style='margin-top:0;'>FAIZ COMPUTER INSTITUTE</h3>
            <p><strong>Student:</strong> " . htmlspecialchars($student['name']) . " (" . htmlspecialchars($student['student_id']) . ")</p>
            <div style='padding:12px; background:#fff3cd; border:1px solid #ffeeba; border-radius:6px;'>No payment found for this student.</div>
            <p style='margin-top:12px;'><a href='admin_fee_main.php?student_id=" . urlencode($student_id) . "'>Go to Submit Fee</a></p>
          </div>";
    exit;
}

// HTML output - show only the paid fields (if multiple non-zero in fallback, show them all)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Fee Receipt - <?php echo htmlspecialchars($student['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f8f9fa; font-family: Arial, sans-serif; }
  .receipt { max-width: 760px; margin: 36px auto; background: #fff; padding: 22px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
  .student-photo { width:72px; height:72px; border-radius:50%; object-fit:cover; border:2px solid #e9ecef; }
  @media print { .no-print { display:none; } }
</style>
</head>
<body>
<div class="receipt">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">FAIZ COMPUTER INSTITUTE</h4>
      <strong>Fee Receipt</strong>
    </div>
    <div class="text-end">
      <?php if(!empty($student['photo']) && file_exists("../uploads/".$student['photo'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo" alt="Photo">
      <?php endif; ?>
    </div>
  </div>

  <div class="mb-3">
    <p class="mb-0"><strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
    <p class="mb-0"><strong>Enrollment ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
    <p class="mb-0"><strong>Course:</strong> <?php echo htmlspecialchars($student['course'] ?? '-'); ?></p>
    <p class="mb-0"><strong>Payment Date:</strong> <?php echo htmlspecialchars($payment_date); ?></p>
  </div>

  <table class="table table-bordered mt-3">
    <thead class="table-light">
      <tr><th>Fee Type</th><th>Amount (₹)</th></tr>
    </thead>
    <tbody>
      <?php foreach ($display_changes as $field => $amount): ?>
        <tr>
          <td><?php echo htmlspecialchars($labels[$field] ?? $field); ?></td>
          <td><?php echo number_format($amount, 2); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="text-center mt-3">Thank you for your payment!</p>

  <div class="text-center no-print mt-3">
    <button class="btn btn-primary" onclick="window.print()">🖨 Print Receipt</button>
    <a class="btn btn-secondary" href="admin_fee_dashboard.php">Back</a>
  </div>
</div>
</body>
</html>
