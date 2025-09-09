<?php
// show_fee.php
include '../database_connection/db_connect.php';

if (!$conn) {
    die("Database connection not found");
}

// Get student fee record ID
$fee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT sf.*, s.name AS student_name, s.course, s.photo, s.student_id AS enrollment
        FROM student_fees sf
        JOIN students s ON sf.student_id = s.student_id
        WHERE sf.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fee_id);
$stmt->execute();
$result = $stmt->get_result();
$fee = $result->fetch_assoc();

if (!$fee) {
    die("No fee record found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fee Details - <?php echo htmlspecialchars($fee['student_name']); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container my-5">
  <div class="card shadow">
    <div class="card-header bg-dark text-white">
      <h4>Fee Details - <?php echo htmlspecialchars($fee['student_name']); ?> (<?php echo htmlspecialchars($fee['enrollment']); ?>)</h4>
    </div>
    <div class="card-body">
      <div class="d-flex mb-4">
        <img src="../uploads/<?php echo htmlspecialchars($fee['photo']); ?>" alt="Student Photo" 
             class="rounded me-3" style="width:100px;height:100px;object-fit:cover;">
        <div>
          <p><strong>Name:</strong> <?php echo htmlspecialchars($fee['student_name']); ?></p>
          <p><strong>Course:</strong> <?php echo htmlspecialchars($fee['course']); ?></p>
          <p><strong>Total Fee:</strong> ₹<?php echo number_format($fee['total_fee'],2); ?></p>
        </div>
      </div>

      <table class="table table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Fee Type / Month</th>
            <th>Amount (₹)</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>Internal 1</td><td><?php echo number_format($fee['internal1'],2); ?></td></tr>
          <tr><td>Internal 2</td><td><?php echo number_format($fee['internal2'],2); ?></td></tr>
          <tr><td>Semester 1</td><td><?php echo number_format($fee['semester1'],2); ?></td></tr>
          <tr><td>Semester 2</td><td><?php echo number_format($fee['semester2'],2); ?></td></tr>
          <tr><td>January</td><td><?php echo number_format($fee['month_jan'],2); ?></td></tr>
          <tr><td>February</td><td><?php echo number_format($fee['month_feb'],2); ?></td></tr>
          <tr><td>March</td><td><?php echo number_format($fee['month_mar'],2); ?></td></tr>
          <tr><td>April</td><td><?php echo number_format($fee['month_apr'],2); ?></td></tr>
          <tr><td>May</td><td><?php echo number_format($fee['month_may'],2); ?></td></tr>
          <tr><td>June</td><td><?php echo number_format($fee['month_jun'],2); ?></td></tr>
          <tr><td>July</td><td><?php echo number_format($fee['month_jul'],2); ?></td></tr>
          <tr><td>August</td><td><?php echo number_format($fee['month_aug'],2); ?></td></tr>
          <tr><td>September</td><td><?php echo number_format($fee['month_sep'],2); ?></td></tr>
          <tr><td>October</td><td><?php echo number_format($fee['month_oct'],2); ?></td></tr>
          <tr><td>November</td><td><?php echo number_format($fee['month_nov'],2); ?></td></tr>
          <tr><td>December</td><td><?php echo number_format($fee['month_dec'],2); ?></td></tr>
        </tbody>
      </table>

      <div class="mt-3">
        <p><strong>Total Paid:</strong> 
          ₹<?php echo number_format(
              $fee['internal1'] + $fee['internal2'] + $fee['semester1'] + $fee['semester2'] +
              $fee['month_jan'] + $fee['month_feb'] + $fee['month_mar'] + $fee['month_apr'] +
              $fee['month_may'] + $fee['month_jun'] + $fee['month_jul'] + $fee['month_aug'] +
              $fee['month_sep'] + $fee['month_oct'] + $fee['month_nov'] + $fee['month_dec'], 2
          ); ?>
        </p>
      </div>

      <a href="admin_fee_dashboard.php" class="btn btn-secondary">Back</a>
    </div>
  </div>
</div>
</body>
</html>
