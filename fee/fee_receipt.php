<?php
// fee_receipt.php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// --- Get student ID ---
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) {
    echo "<div style='padding:20px; color:red;'>No student selected.</div>";
    exit;
}

// --- Get student details ---
$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("i", $student_id);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();

if (!$student) {
    echo "<div style='padding:20px; color:red;'>Student not found.</div>";
    exit;
}

// --- Get latest fee details ---
$fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ? ORDER BY last_updated DESC LIMIT 1");
$fee_q->bind_param("i", $student_id);
$fee_q->execute();
$fee = $fee_q->get_result()->fetch_assoc();

if (!$fee) {
    echo "<div style='padding:20px; color:red;'>No payment found for this student.</div>";
    exit;
}

function format_val($arr, $key) {
    if (!isset($arr[$key]) || $arr[$key] == 0) return "-";
    return "₹" . number_format($arr[$key], 2);
}

$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt - <?php echo htmlspecialchars($student['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; padding:20px; }
    .receipt { max-width: 800px; margin: auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .receipt-header { text-align: center; margin-bottom: 20px; }
    .receipt-header h2 { margin-bottom: 5px; }
    .student-photo { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
    table th, table td { text-align: center; }
    .signature { margin-top: 40px; text-align: right; font-style: italic; }
    .btn-print { margin-top: 20px; }
</style>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        <h2>FAIZ Computer Institute</h2>
        <p><strong>Fee Receipt</strong></p>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <p><strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
            <p><strong>Enrollment ID:</strong> <?php echo $student['student_id']; ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
        </div>
        <?php if($student['photo']): ?>
            <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo" alt="Photo">
        <?php endif; ?>
    </div>

    <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($fee['payment_date']); ?></p>

    <h5 class="mt-4">Fee Details</h5>
    <table class="table table-bordered">
        <tr><th>Total Fee</th><td><?php echo format_val($fee,'total_fee'); ?></td></tr>
        <tr><th>Admission Fee</th><td><?php echo format_val($fee,'admission_fee'); ?></td></tr>
        <tr><th>Internal 1</th><td><?php echo format_val($fee,'internal1'); ?></td></tr>
        <tr><th>Internal 2</th><td><?php echo format_val($fee,'internal2'); ?></td></tr>
        <tr><th>Semester 1</th><td><?php echo format_val($fee,'semester1'); ?></td></tr>
        <tr><th>Semester 2</th><td><?php echo format_val($fee,'semester2'); ?></td></tr>
    </table>

    <h5 class="mt-4">Monthly Fees</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <?php foreach($months as $m): ?>
                    <th><?php echo ucfirst($m); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach($months as $m): ?>
                    <td><?php echo format_val($fee,'month_'.$m); ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <p>Authorized Signatory</p>
    </div>

    <div class="text-center">
        <button class="btn btn-primary btn-print" onclick="window.print()">Print Receipt</button>
    </div>
</div>
</body>
</html>
