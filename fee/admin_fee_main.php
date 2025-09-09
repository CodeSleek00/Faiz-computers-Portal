<?php
// admin_fee_main.php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

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

// get student_id
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

$student_q = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$student_q->bind_param("s", $student_id);
$student_q->execute();
$student = $student_q->get_result()->fetch_assoc();
if (!$student) die("Student not found.");

$fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
$fee_q->bind_param("s", $student_id);
$fee_q->execute();
$old_fee = $fee_q->get_result()->fetch_assoc(); // may be null

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if ($old_fee) {
        $sql = "UPDATE student_fees SET total_fee=?, admission_fee=?, internal1=?, internal2=?, semester1=?, semester2=?,
                month_jan=?, month_feb=?, month_mar=?, month_apr=?, month_may=?, month_jun=?, month_jul=?, month_aug=?,
                month_sep=?, month_oct=?, month_nov=?, month_dec=?, payment_date=?, last_updated=NOW()
                WHERE student_id=?";
        $stmt = $conn->prepare($sql);

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
        $types = str_repeat('d', 18) . 'ss';
        mysqli_bind_params_dynamic($stmt, $types, $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee updated successfully!";
    } else {
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
        $types = 'ss' . str_repeat('d', 18) . 's';
        mysqli_bind_params_dynamic($stmt, $types, $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee submitted successfully!";
    }

    $fee_q = $conn->prepare("SELECT * FROM student_fees WHERE student_id = ?");
    $fee_q->bind_param("s", $student_id);
    $fee_q->execute();
    $new_fee = $fee_q->get_result()->fetch_assoc();

    header("Location: fee_receipt.php?student_id=" . urlencode($student_id));
    exit;
}

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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Submit Fee - <?php echo htmlspecialchars($student['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .card { max-width: 1000px; margin: 40px auto; padding: 25px; border-radius: 15px; }
    h3 { font-weight: 600; }
    input[type=number] { max-width: 120px; }
    table th, table td { vertical-align: middle; text-align: center; }
    .monthly-fees-container { overflow-x: auto; }
    .monthly-fees-container table { min-width: 700px; }
    .student-photo { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
    @media (max-width: 576px) {
        input[type=number] { width: 80px; }
        .btn { font-size: 0.85rem; padding: 0.4rem 0.7rem; }
    }
</style>
</head>
<body>
<div class="card shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Submit Fee for <?php echo htmlspecialchars($student['name']); ?></h3>
            <small class="text-muted"><?php echo $student['student_id']; ?> | <?php echo $student['course'] ?? ''; ?></small>
        </div>
        <?php if($student['photo']): ?>
            <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo" alt="Student Photo">
        <?php endif; ?>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-4">
            <label class="form-label fw-semibold">Total Fee</label>
            <input type="number" step="0.01" name="total_fee" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'total_fee'); ?>" required>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr><th>Fee Type</th><th>Amount (₹)</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $fee_types = ['admission_fee'=>'Admission Fee','internal1'=>'Internal 1','internal2'=>'Internal 2','semester1'=>'Semester 1','semester2'=>'Semester 2'];
                    foreach($fee_types as $key => $label): ?>
                        <tr>
                            <td><?php echo $label; ?></td>
                            <td><input type="number" step="0.01" name="<?php echo $key; ?>" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],$key); ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h5 class="mb-3">Monthly Fees</h5>
        <div class="monthly-fees-container mb-4">
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
                            <td><input type="number" step="0.01" name="month_<?php echo $m; ?>" class="form-control" value="<?php echo print_val($new_fee ?? $old_fee ?? [],'month_'.$m); ?>"></td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Payment Date</label>
            <input type="date" name="payment_date" class="form-control" value="<?php echo htmlspecialchars(($new_fee['payment_date'] ?? $old_fee['payment_date'] ?? date('Y-m-d'))); ?>">
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-success flex-fill">Submit & Generate Receipt</button>
            <a href="admin_fee_dashboard.php" class="btn btn-secondary flex-fill">Back to Dashboard</a>
        </div>
    </form>
</div>
</body>
</html>
