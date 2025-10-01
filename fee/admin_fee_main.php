<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

$student = $conn->query("SELECT * FROM students WHERE student_id='$student_id'")->fetch_assoc();
$fee = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'")->fetch_assoc();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_fee = $_POST['total_fee'];
    $admission_fee = $_POST['admission_fee'] ?? 0;
    $internal1 = $_POST['internal1'] ?? 0;
    $internal2 = $_POST['internal2'] ?? 0;
    $semester1 = $_POST['semester1'] ?? 0;
    $semester2 = $_POST['semester2'] ?? 0;
    $payment_date = $_POST['payment_date'] ?? date("Y-m-d");

    $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
    $month_values = [];
    $extra_filled = false;

    foreach($months as $m){
        $month_values[$m] = $_POST['month_'.$m] ?? 0;
        if($month_values[$m] > 0) $extra_filled = true;
    }
    if($admission_fee>0 || $internal1>0 || $internal2>0 || $semester1>0 || $semester2>0) $extra_filled = true;

    if ($fee) {
        $stmt = $conn->prepare("UPDATE student_fees SET total_fee=?, admission_fee=?, internal1=?, internal2=?, semester1=?, semester2=?,
            month_jan=?, month_feb=?, month_mar=?, month_apr=?, month_may=?, month_jun=?, month_jul=?, month_aug=?,
            month_sep=?, month_oct=?, month_nov=?, month_dec=?, payment_date=?, last_updated=NOW() WHERE student_id=?");
        $stmt->bind_param("ddddddddddddddddsdss",
            $total_fee, $admission_fee, $internal1, $internal2, $semester1, $semester2,
            $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],
            $month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
            $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec'],
            $payment_date, $student_id
        );
        $stmt->execute();
        $msg = "Fee updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO student_fees (student_id, student_name, total_fee, admission_fee, internal1, internal2, semester1, semester2,
            month_jan, month_feb, month_mar, month_apr, month_may, month_jun, month_jul, month_aug,
            month_sep, month_oct, month_nov, month_dec, payment_date)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssddddddddddddddddds",
            $student['student_id'], $student['name'], $total_fee, $admission_fee, $internal1, $internal2, $semester1, $semester2,
            $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],
            $month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
            $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec'],
            $payment_date
        );
        $stmt->execute();
        $msg = "Fee submitted successfully!";
    }

    if($extra_filled){
        header("Location: fee_receipt.php?student_id=".$student_id);
        exit;
    }

    $fee = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Fee - <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  body { background: #f0f2f5; }
  .card { max-width: 1000px; margin: 40px auto; padding: 20px; }
  .form-label { font-weight: 500; }
  input[type=number] { width: 100px; }
</style>
</head>
<body>
<div class="card shadow-sm">
  <h3 class="mb-4">Submit Fee for <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['student_id']; ?>)</h3>

  <?php if($msg): ?>
    <div class="alert alert-success"><?php echo $msg; ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Total Fee</label>
      <input type="number" step="0.01" name="total_fee" class="form-control" 
        value="<?php echo $fee['total_fee']>0?$fee['total_fee']:''; ?>" required>
    </div>

    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Fee Type</th>
          <th>Amount (₹)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Admission Fee</td>
          <td><input type="number" step="0.01" name="admission_fee" class="form-control" 
            value="<?php echo $fee['admission_fee']>0?$fee['admission_fee']:''; ?>"></td>
        </tr>
        <tr>
          <td>Internal 1</td>
          <td><input type="number" step="0.01" name="internal1" class="form-control" 
            value="<?php echo $fee['internal1']>0?$fee['internal1']:''; ?>"></td>
        </tr>
        <tr>
          <td>Internal 2</td>
          <td><input type="number" step="0.01" name="internal2" class="form-control" 
            value="<?php echo $fee['internal2']>0?$fee['internal2']:''; ?>"></td>
        </tr>
        <tr>
          <td>Semester 1</td>
          <td><input type="number" step="0.01" name="semester1" class="form-control" 
            value="<?php echo $fee['semester1']>0?$fee['semester1']:''; ?>"></td>
        </tr>
        <tr>
          <td>Semester 2</td>
          <td><input type="number" step="0.01" name="semester2" class="form-control" 
            value="<?php echo $fee['semester2']>0?$fee['semester2']:''; ?>"></td>
        </tr>
      </tbody>
    </table>

    <h5 class="mb-3">Monthly Fees</h5>
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
            <td><input type="number" step="0.01" name="month_<?php echo $m; ?>" class="form-control" 
              value="<?php echo $fee['month_'.$m]>0?$fee['month_'.$m]:''; ?>"></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>

    <div class="mb-3">
      <label class="form-label">Payment Date</label>
      <input type="date" name="payment_date" class="form-control"
        value="<?php echo !empty($fee['payment_date']) ? $fee['payment_date'] : date('Y-m-d'); ?>">
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-success">Submit & Generate Receipt</button>
      <a href="admin_fee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
  </form>
</div>
</body>
</html>
