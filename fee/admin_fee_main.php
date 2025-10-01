<?php
// admin_fee_main.php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// months
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

// helper to bind dynamic params
function mysqli_bind_params_dynamic($stmt, $types, &$params) {
    $bind_names = [];
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_names[] = &$params[$i];
    }
    return call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// get student_id (student_id is treated as string - e.g. FAIZ-JULY-1002)
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// fetch student (student_id is varchar/string)
$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("s", $student_id);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();
if (!$student) die("Student not found.");

// fetch existing fee record (if any)
$fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
$fee_q->bind_param("s", $student_id);
$fee_q->execute();
$old_fee = $fee_q->get_result()->fetch_assoc(); // may be null

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect submitted values (as floats where relevant)
    $submitted = [];
    $submitted['total_fee'] = isset($_POST['total_fee']) && $_POST['total_fee'] !== '' ? (float)$_POST['total_fee'] : 0;
    $submitted['admission_fee'] = isset($_POST['admission_fee']) && $_POST['admission_fee'] !== '' ? (float)$_POST['admission_fee'] : 0;
    $submitted['internal1'] = isset($_POST['internal1']) && $_POST['internal1'] !== '' ? (float)$_POST['internal1'] : 0;
    $submitted['internal2'] = isset($_POST['internal2']) && $_POST['internal2'] !== '' ? (float)$_POST['internal2'] : 0;
    $submitted['semester1'] = isset($_POST['semester1']) && $_POST['semester1'] !== '' ? (float)$_POST['semester1'] : 0;
    $submitted['semester2'] = isset($_POST['semester2']) && $_POST['semester2'] !== '' ? (float)$_POST['semester2'] : 0;
    $submitted['payment_date'] = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');

    foreach ($months as $m) {
        $k = 'month_'.$m;
        $submitted[$k] = isset($_POST[$k]) && $_POST[$k] !== '' ? (float)$_POST[$k] : 0;
    }

    // determine if record exists (update) or new (insert)
    if ($old_fee) {
        // UPDATE
        $sql = "UPDATE student_fees SET total_fee=?, admission_fee=?, internal1=?, internal2=?, semester1=?, semester2=?,
                month_jan=?, month_feb=?, month_mar=?, month_apr=?, month_may=?, month_jun=?, month_jul=?, month_aug=?,
                month_sep=?, month_oct=?, month_nov=?, month_dec=?, payment_date=?, last_updated=NOW()
                WHERE student_id=?";
        $stmt = $conn->prepare($sql);

        // prepare params order exactly same as placeholders
        $params = [
            $submitted['total_fee'],
            $submitted['admission_fee'],
            $submitted['internal1'],
            $submitted['internal2'],
            $submitted['semester1'],
            $submitted['semester2'],
            $submitted['month_jan'],$submitted['month_feb'],$submitted['month_mar'],$submitted['month_apr'],
            $submitted['month_may'],$submitted['month_jun'],$submitted['month_jul'],$submitted['month_aug'],
            $submitted['month_sep'],$submitted['month_oct'],$submitted['month_nov'],$submitted['month_dec'],
            $submitted['payment_date'],
            $student_id
        ];
        $types = str_repeat('d', 18) . 'ss'; // 18 doubles + payment_date (s) + student_id (s)
        mysqli_bind_params_dynamic($stmt, $types, $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee updated successfully!";
    } else {
        // INSERT
        $sql = "INSERT INTO student_fees (student_id, student_name, total_fee, admission_fee, internal1, internal2, semester1, semester2,
                month_jan, month_feb, month_mar, month_apr, month_may, month_jun, month_jul, month_aug,
                month_sep, month_oct, month_nov, month_dec, payment_date)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);

        $params = [
            $student_id,
            $student['name'],
            $submitted['total_fee'],
            $submitted['admission_fee'],
            $submitted['internal1'],
            $submitted['internal2'],
            $submitted['semester1'],
            $submitted['semester2'],
            $submitted['month_jan'],$submitted['month_feb'],$submitted['month_mar'],$submitted['month_apr'],
            $submitted['month_may'],$submitted['month_jun'],$submitted['month_jul'],$submitted['month_aug'],
            $submitted['month_sep'],$submitted['month_oct'],$submitted['month_nov'],$submitted['month_dec'],
            $submitted['payment_date']
        ];
        $types = 'ss' . str_repeat('d', 18) . 's'; // student_id (s), student_name (s), 18 d's, payment_date (s)
        mysqli_bind_params_dynamic($stmt, $types, $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee submitted successfully!";
    }

    // fetch the updated/new record
    $fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
    $fee_q->bind_param("s", $student_id);
    $fee_q->execute();
    $new_fee = $fee_q->get_result()->fetch_assoc();

    // compute changed fields (new - old). If old missing, old=0
    $changes = [];
    $all_fields = array_merge(['admission_fee','internal1','internal2','semester1','semester2'], array_map(function($m){ return 'month_'.$m; }, $months));
    foreach ($all_fields as $field) {
        $old_val = $old_fee[$field] ?? 0;
        // use submitted value if present in POST, otherwise use new_fee
        $new_val = $submitted[$field] ?? ($new_fee[$field] ?? 0);
        // consider change only if new_val > old_val (a new payment)
        if ($new_val > $old_val) {
            // show the actual increment (new - old)
            $amount_paid_now = $new_val - $old_val;
            // ignore tiny floating rounding zeros
            if ($amount_paid_now > 0) {
                $changes[$field] = $amount_paid_now;
            }
        }
    }

    // store recent payment info in session (for receipt)
    if (!empty($changes)) {
        $_SESSION['recent_payment'] = [
            'student_id' => $student_id,
            'student_name' => $student['name'],
            'course' => $student['course'] ?? '',
            'photo' => $student['photo'] ?? '',
            'date' => $submitted['payment_date'],
            'changes' => $changes
        ];
    } else {
        // if no change detected, clear any previous
        unset($_SESSION['recent_payment']);
    }

    // redirect to receipt (receipt will read session['recent_payment'])
    header("Location: fee_receipt.php?student_id=" . urlencode($student_id));
    exit;
}

// helper to print blank if zero
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
      <input type="number" step="0.01" name="total_fee" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'total_fee'); ?>" required>
    </div>

    <table class="table table-bordered mb-4">
      <thead class="table-light"><tr><th>Fee Type</th><th>Amount (₹)</th></tr></thead>
      <tbody>
        <tr><td>Admission Fee</td><td><input type="number" step="0.01" name="admission_fee" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'admission_fee'); ?>"></td></tr>
        <tr><td>Internal 1</td><td><input type="number" step="0.01" name="internal1" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'internal1'); ?>"></td></tr>
        <tr><td>Internal 2</td><td><input type="number" step="0.01" name="internal2" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'internal2'); ?>"></td></tr>
        <tr><td>Semester 1</td><td><input type="number" step="0.01" name="semester1" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'semester1'); ?>"></td></tr>
        <tr><td>Semester 2</td><td><input type="number" step="0.01" name="semester2" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'semester2'); ?>"></td></tr>
      </tbody>
    </table>

    <h5 class="mb-3">Monthly Fees</h5>
    <table class="table table-bordered mb-3">
      <thead class="table-light"><tr>
        <?php foreach($months as $m): ?><th><?php echo ucfirst($m); ?></th><?php endforeach; ?>
      </tr></thead>
      <tbody><tr>
        <?php foreach($months as $m): ?>
          <td><input type="number" step="0.01" name="month_<?php echo $m; ?>" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'month_'.$m); ?>"></td>
        <?php endforeach; ?>
      </tr></tbody>
    </table>

    <div class="mb-3">
      <label class="form-label">Payment Date</label>
      <input type="date" name="payment_date" class="form-control" value="<?php echo htmlspecialchars(($new_fee['payment_date'] ?? $old_fee['payment_date'] ?? date('Y-m-d'))); ?>">
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-success">Submit & Generate Receipt</button>
      <a href="admin_fee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
  </form>
</div>
</body>
</html>
