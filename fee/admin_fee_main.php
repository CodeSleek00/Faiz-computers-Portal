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
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $admission_fee = $_POST['admission_fee'] ?? 0;
    $internal1 = $_POST['internal1'] ?? 0;
    $internal2 = $_POST['internal2'] ?? 0;
    $semester1 = $_POST['semester1'] ?? 0;
    $semester2 = $_POST['semester2'] ?? 0;
    $months_fee = [];
    foreach ($months as $m) {
        $months_fee[$m] = $_POST['month_'.$m] ?? 0;
    }
    $total_fee = $_POST['total_fee'] ?? 0;
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');

    // check if student fees record exists
    $check = $conn->prepare("SELECT id FROM student_fees WHERE student_id = ?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $result = $check->get_result();
    $old_fee = $result->fetch_assoc();
    $check->close();

    if ($old_fee) {
        // update record
        $sql = "UPDATE student_fees SET admission_fee=?, internal1=?, internal2=?, semester1=?, semester2=?, ";
        foreach ($months as $m) {
            $sql .= "month_$m=?, ";
        }
        $sql .= "total_fee=?, payment_date=? WHERE student_id=?";
        $stmt = $conn->prepare($sql);

        $types = "iiiiiiiii" . str_repeat("i", count($months)) . "is";
        $params = [
            $admission_fee, $internal1, $internal2,
            $semester1, $semester2
        ];
        foreach ($months as $m) {
            $params[] = $months_fee[$m];
        }
        $params[] = $total_fee;
        $params[] = $payment_date;
        $params[] = $student_id;

        mysqli_bind_params_dynamic($stmt, str_repeat("d", count($params)), $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee updated successfully!";
    } else {
        // insert new record
        $cols = "student_id, admission_fee, internal1, internal2, semester1, semester2, ";
        foreach ($months as $m) $cols .= "month_$m, ";
        $cols .= "total_fee, payment_date";

        $placeholders = rtrim(str_repeat("?,", 6 + count($months))," ,") . ",?";
        $sql = "INSERT INTO student_fees ($cols) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);

        $params = [
            $student_id, $admission_fee, $internal1, $internal2,
            $semester1, $semester2
        ];
        foreach ($months as $m) {
            $params[] = $months_fee[$m];
        }
        $params[] = $total_fee;
        $params[] = $payment_date;

        mysqli_bind_params_dynamic($stmt, str_repeat("d", count($params)), $params);
        $stmt->execute();
        $stmt->close();
        $msg = "Fee submitted successfully!";
    }

    // ✅ Save individual payments into fee_payments table
    $submitted = [
        'admission_fee' => $admission_fee,
        'internal1' => $internal1,
        'internal2' => $internal2,
        'semester1' => $semester1,
        'semester2' => $semester2
    ];
    foreach ($months as $m) {
        $submitted["month_$m"] = $months_fee[$m];
    }

    foreach ($submitted as $field => $amount) {
        if ($amount > 0) {
            $insert = $conn->prepare("INSERT INTO fee_payments (student_id, fee_type, amount, payment_date) VALUES (?,?,?,?)");
            $insert->bind_param("ssds", $student_id, $field, $amount, $payment_date);
            $insert->execute();
            $insert->close();
        }
    }

    // redirect to receipt
    header("Location: fee_receipt.php?student_id=" . urlencode($student_id));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
 <link rel="icon" type="image/png" href="image.png">
  <link rel="apple-touch-icon" href="image.png">
<title>Submit Fee - <?php echo htmlspecialchars($student['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .card {  margin: 40px auto; padding: 25px; border-radius: 15px; }
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
