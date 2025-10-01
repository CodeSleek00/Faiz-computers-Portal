<?php
include '../database_connection/db_connect.php';

if (!$conn) die("Database connection not found");

// GET me student_id
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

// Student info fetch
$student = $conn->query("SELECT * FROM students WHERE student_id='$student_id'")->fetch_assoc();

// Existing fee record
$fee = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'")->fetch_assoc();

// Handle form submission
$msg = '';
$redirect_receipt = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_fee = $_POST['total_fee'];
    $internal1 = $_POST['internal1'] ?? 0;
    $internal2 = $_POST['internal2'] ?? 0;
    $semester1 = $_POST['semester1'] ?? 0;
    $semester2 = $_POST['semester2'] ?? 0;

    $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
    $month_values = [];
    $extra_filled = false;

    foreach($months as $m){
        $month_values[$m] = $_POST['month_'.$m] ?? 0;
        if($month_values[$m] > 0) $extra_filled = true;
    }
    if($internal1>0 || $internal2>0 || $semester1>0 || $semester2>0) $extra_filled = true;

    if ($fee) {
        // Update existing
        $stmt = $conn->prepare("UPDATE student_fees SET total_fee=?, internal1=?, internal2=?, semester1=?, semester2=?,
            month_jan=?, month_feb=?, month_mar=?, month_apr=?, month_may=?, month_jun=?, month_jul=?, month_aug=?,
            month_sep=?, month_oct=?, month_nov=?, month_dec=?, last_updated=NOW() WHERE student_id=?");

        $stmt->bind_param("ddddddddddddddddds",
            $total_fee, $internal1, $internal2, $semester1, $semester2,
            $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],
            $month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
            $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec'],
            $student_id
        );
        $stmt->execute();
        $msg = "Fee updated successfully!";
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO student_fees (student_id, student_name, total_fee, internal1, internal2, semester1, semester2,
            month_jan, month_feb, month_mar, month_apr, month_may, month_jun, month_jul, month_aug,
            month_sep, month_oct, month_nov, month_dec)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param("ssdddddddddddddddddd",
            $student['student_id'], $student['name'], $total_fee, $internal1, $internal2, $semester1, $semester2,
            $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],
            $month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
            $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec']
        );
        $stmt->execute();
        $msg = "Fee submitted successfully!";
    }

    // If any field other than total_fee filled → redirect to receipt
    if($extra_filled){
        header("Location: fee_receipt.php?student_id=".$student_id);
        exit;
    }

    // Refresh fee record
    $fee = $conn->query("SELECT * FROM student_fees WHERE student_id='$student_id'")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Fee - <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style> input[type=number]{ width:100px; } </style>
</head>
<body class="bg-light">
<div class="container my-5">
<h2>Submit Fee for <?php echo htmlspecialchars($student['name']); ?> (<?php echo $student['student_id']; ?>)</h2>
<?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

<form method="post">
<div class="mb-3">
    <label>Total Fee</label>
    <input type="number" step="0.01" name="total_fee" class="form-control" value="<?php echo $fee['total_fee']??0; ?>" required>
</div>

<h5>Exam Fees</h5>
<div class="row mb-3">
    <div class="col"><label>Internal 1</label><input type="number" step="0.01" name="internal1" class="form-control" value="<?php echo $fee['internal1']??0; ?>"></div>
    <div class="col"><label>Internal 2</label><input type="number" step="0.01" name="internal2" class="form-control" value="<?php echo $fee['internal2']??0; ?>"></div>
    <div class="col"><label>Semester 1</label><input type="number" step="0.01" name="semester1" class="form-control" value="<?php echo $fee['semester1']??0; ?>"></div>
    <div class="col"><label>Semester 2</label><input type="number" step="0.01" name="semester2" class="form-control" value="<?php echo $fee['semester2']??0; ?>"></div>
</div>

<h5>Monthly Fees</h5>
<div class="row mb-3">
<?php 
$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
foreach($months as $m): ?>
    <div class="col mb-2">
        <label><?php echo ucfirst($m); ?></label>
        <input type="number" step="0.01" name="month_<?php echo $m; ?>" class="form-control" value="<?php echo $fee['month_'.$m]??0; ?>">
    </div>
<?php endforeach; ?>
</div>

<button type="submit" class="btn btn-success">Submit & Generate Receipt</button>
<a href="admin_fee_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</form>
</div>
</body>
</html>
