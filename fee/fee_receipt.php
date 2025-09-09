<?php
include '../database_connection/db_connect.php';
session_start();

$student_id = $_GET['student_id'] ?? 0;
$latest     = $_GET['latest'] ?? '';

// Fetch student + fee record
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();
$fee     = $conn->query("SELECT * FROM fee WHERE student_id = $student_id")->fetch_assoc();

$fields_map = [
    'internal1' => 'Internal 1',
    'internal2' => 'Internal 2',
    'semester1' => 'Semester 1',
    'semester2' => 'Semester 2',
    'month_jan' => 'January Fee',
    'month_feb' => 'February Fee',
    'month_mar' => 'March Fee',
    'month_apr' => 'April Fee',
    'month_may' => 'May Fee',
    'month_jun' => 'June Fee',
    'month_jul' => 'July Fee',
    'month_aug' => 'August Fee',
    'month_sep' => 'September Fee',
    'month_oct' => 'October Fee',
    'month_nov' => 'November Fee',
    'month_dec' => 'December Fee'
];

// Latest field amount and label
$latest_amount = $latest && isset($fee[$latest]) ? $fee[$latest] : 0;
$latest_label  = $fields_map[$latest] ?? 'Unknown Fee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fee Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <div class="card shadow p-4">
    <h2 class="text-center mb-3">Fee Receipt</h2>

    <div class="mb-3">
      <strong>Student:</strong> <?php echo htmlspecialchars($student['name']); ?><br>
      <strong>Enrollment ID:</strong> <?php echo htmlspecialchars($student['enrollment_id']); ?>
    </div>

    <table class="table table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Fee Type</th>
          <th>Amount (₹)</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php echo $latest_label; ?></td>
          <td>₹<?php echo number_format($latest_amount, 2); ?></td>
          <td><?php echo date("d-m-Y"); ?></td>
        </tr>
      </tbody>
    </table>

    <div class="text-center mt-3">
      <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    </div>
  </div>
</div>
</body>
</html>
