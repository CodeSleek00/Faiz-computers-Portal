<?php
// admin_fee_main.php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// define months early to avoid undefined warnings
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

// helper to bind params dynamically (mysqli)
function stmt_bind_params($stmt, $types, $params) {
    // mysqli bind_param requires references
    $bind_names = [];
    $bind_names[] = $types;
    for ($i=0;$i<count($params);$i++) {
        $bind_names[] = &$params[$i];
    }
    return call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// GET student_id
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// fetch student safely
$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("i", $student_id);
$student_q->execute();
$student_res = $student_q->get_result();
$student = $student_res->fetch_assoc();
if (!$student) die("Student not found.");

// fetch existing fee record if any
$fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
$fee_q->bind_param("i", $student_id);
$fee_q->execute();
$fee_res = $fee_q->get_result();
$fee = $fee_res->fetch_assoc(); // may be null

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize numeric inputs (empty -> 0)
    $total_fee = isset($_POST['total_fee']) && $_POST['total_fee'] !== '' ? (float)$_POST['total_fee'] : 0;
    $admission_fee = isset($_POST['admission_fee']) && $_POST['admission_fee'] !== '' ? (float)$_POST['admission_fee'] : 0;
    $internal1 = isset($_POST['internal1']) && $_POST['internal1'] !== '' ? (float)$_POST['internal1'] : 0;
    $internal2 = isset($_POST['internal2']) && $_POST['internal2'] !== '' ? (float)$_POST['internal2'] : 0;
    $semester1 = isset($_POST['semester1']) && $_POST['semester1'] !== '' ? (float)$_POST['semester1'] : 0;
    $semester2 = isset($_POST['semester2']) && $_POST['semester2'] !== '' ? (float)$_POST['semester2'] : 0;
    $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');

    $month_values = [];
    $extra_filled = false;
    foreach ($months as $m) {
        $val = isset($_POST['month_'.$m]) && $_POST['month_'.$m] !== '' ? (float)$_POST['month_'.$m] : 0;
        $month_values[$m] = $val;
        if ($val > 0) $extra_filled = true;
    }
    if ($admission_fee > 0 || $internal1 > 0 || $internal2 > 0 || $semester1 > 0 || $semester2 > 0) $extra_filled = true;

    if ($fee) {
        // UPDATE existing record
        $sql = "UPDATE student_fees SET total_fee=?, admission_fee=?, internal1=?, internal2=?, semester1=?, semester2=?,
                month_jan=?, month_feb=?, month_mar=?, month_apr=?, month_may=?, month_jun=?, month_jul=?, month_aug=?,
                month_sep=?, month_oct=?, month_nov=?, month_dec=?, payment_date=?, last_updated=NOW()
                WHERE student_id=?";
        $stmt = $conn->prepare($sql);
        // types: 18 doubles (total_fee + admission + 4 exam + 12 months) => 18 d's, then payment_date (s), then student_id (i)
        $types = str_repeat('d', 18) . 's' . 'i';
        $params = [
            $total_fee, $admission_fee, $internal1, $internal2, $semester1, $semester2,
            $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],
            $month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
            $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec'],
            $payment_date,
            (int)$student_id
        ];
        stmt_bind_params($stmt, $types, $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee updated successfully!";
    } else {
        // INSERT new record
        $sql = "INSERT INTO student_fees (student_id, student_name, total_fee, admission_fee, internal1, internal2, semester1, semester2,
                month_jan, month_feb, month_mar, month_apr, month_may, month_jun, month_jul, month_aug,
                month_sep, month_oct, month_nov, month_dec, payment_date)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        // types: student_id (i), student_name (s), then 18 doubles, then payment_date (s)
        // But earlier design had student_id stored as INT in DB -> bind as i; student_name s
        // We'll bind: i, s, d*18, s
        $types = 'is' . str_repeat('d', 18) . 's';
        $params = [
            (int)$student['student_id'],
            $student['name'],
            $total_fee, $admission_fee, $internal1, $internal2, $semester1, $semester2,
            $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],
            $month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
            $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec'],
            $payment_date
        ];
        // bind
        stmt_bind_params($stmt, $types, $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee submitted successfully!";
    }

    // refresh $fee
    $fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
    $fee_q->bind_param("i", $student_id);
    $fee_q->execute();
    $fee_res = $fee_q->get_result();
    $fee = $fee_res->fetch_assoc();

    // If any extra fields (other than only total_fee) filled, redirect to receipt showing the submitted fields
    if ($extra_filled) {
        // To show only newly submitted fields in receipt, we can pass them via POST redirect.
        // Simple approach: store submitted values in session then redirect to receipt (or pass via GET if small).
        // Here we'll redirect to receipt (which will use DB values). If you want strictly "only submitted fields" shown,
        // we can pass them via session — ask if you want that. For now redirect to receipt page.
        header("Location: fee_receipt.php?student_id=" . urlencode($student_id));
        exit;
    }
}

// helper for printing value or blank
function print_val($arr, $key) {
    if (!isset($arr[$key]) || $arr[$key] === null) return '';
    $v = (float)$arr[$key];
    return $v > 0 ? $v : '';
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
  table td, table th { vertical-align: middle; }
</style>
</head>
<body>
<div class="card shadow-sm">
  <h3 class="mb-4">Submit Fee for <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['student_id']; ?>)</h3>

  <?php if($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Total Fee</label>
      <input type="number" step="0.01" name="total_fee" class="form-control" value="<?php echo print_val($fee,'total_fee'); ?>" required>
    </div>

    <table class="table table-bordered mb-4">
      <thead class="table-light">
        <tr><th>Fee Type</th><th>Amount (₹)</th></tr>
      </thead>
      <tbody>
        <tr>
          <td>Admission Fee</td>
          <td><input type="number" step="0.01" name="admission_fee" class="form-control" value="<?php echo print_val($fee,'admission_fee'); ?>"></td>
        </tr>
        <tr>
          <td>Internal 1</td>
          <td><input type="number" step="0.01" name="internal1" class="form-control" value="<?php echo print_val($fee,'internal1'); ?>"></td>
        </tr>
        <tr>
          <td>Internal 2</td>
          <td><input type="number" step="0.01" name="internal2" class="form-control" value="<?php echo print_val($fee,'internal2'); ?>"></td>
        </tr>
        <tr>
          <td>Semester 1</td>
          <td><input type="number" step="0.01" name="semester1" class="form-control" value="<?php echo print_val($fee,'semester1'); ?>"></td>
        </tr>
        <tr>
          <td>Semester 2</td>
          <td><input type="number" step="0.01" name="semester2" class="form-control" value="<?php echo print_val($fee,'semester2'); ?>"></td>
        </tr>
      </tbody>
    </table>

    <h5 class="mb-3">Monthly Fees</h5>
    <table class="table table-bordered mb-3">
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
            <td><input type="number" step="0.01" name="month_<?php echo $m; ?>" class="form-control" value="<?php echo print_val($fee,'month_'.$m); ?>"></td>
          <?php endforeach; ?>
        </tr>
      </tbody>
    </table>

    <div class="mb-3">
      <label class="form-label">Payment Date</label>
      <input type="date" name="payment_date" class="form-control" value="<?php echo htmlspecialchars($fee['payment_date'] ?? date('Y-m-d')); ?>">
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-success">Submit & Generate Receipt</button>
      <a href="admin_fee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
  </form>
</div>
</body>
</html>
