<?php
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

$months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) die("No student selected.");

$student = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();
if(!$student) die("Student not found.");

$fee = $conn->query("SELECT * FROM student_fees WHERE student_id=$student_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $total_fee = $_POST['total_fee'] ?? 0;
    $admission_fee = $_POST['admission_fee'] ?? 0;
    $internal1 = $_POST['internal1'] ?? 0;
    $internal2 = $_POST['internal2'] ?? 0;
    $semester1 = $_POST['semester1'] ?? 0;
    $semester2 = $_POST['semester2'] ?? 0;
    $payment_date = $_POST['payment_date'] ?? date('Y-m-d');

    $month_values = [];
    foreach($months as $m){
        $month_values[$m] = $_POST['month_'.$m] ?? 0;
    }

    $stmt = $conn->prepare("INSERT INTO student_fees (student_id, student_name, total_fee, admission_fee, internal1, internal2, semester1, semester2,
    month_jan, month_feb, month_mar, month_apr, month_may, month_jun, month_jul, month_aug,
    month_sep, month_oct, month_nov, month_dec, payment_date)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param("isdddddddddddddddddd",$student['student_id'],$student['name'],$total_fee,$admission_fee,$internal1,$internal2,$semester1,$semester2,
    $month_values['jan'],$month_values['feb'],$month_values['mar'],$month_values['apr'],$month_values['may'],$month_values['jun'],$month_values['jul'],$month_values['aug'],
    $month_values['sep'],$month_values['oct'],$month_values['nov'],$month_values['dec'],$payment_date);
    $stmt->execute();
    header("Location: fee_receipt.php?student_id=$student_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Fee - <?php echo $student['name']; ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style> input[type=number]{ width:100px; } </style>
</head>
<body>
<div class="container my-5">
<h2>Submit Fee for <?php echo $student['name']; ?> (<?php echo $student['student_id']; ?>)</h2>
<form method="post">
<table class="table table-bordered">
<tr><th>Fee Type</th><th>Amount</th></tr>
<tr><td>Total Fee</td><td><input type="number" step="0.01" name="total_fee" value="<?php echo $fee['total_fee']??''; ?>"></td></tr>
<tr><td>Admission Fee</td><td><input type="number" step="0.01" name="admission_fee" value="<?php echo $fee['admission_fee']??''; ?>"></td></tr>
<tr><td>Internal 1</td><td><input type="number" step="0.01" name="internal1" value="<?php echo $fee['internal1']??''; ?>"></td></tr>
<tr><td>Internal 2</td><td><input type="number" step="0.01" name="internal2" value="<?php echo $fee['internal2']??''; ?>"></td></tr>
<tr><td>Semester 1</td><td><input type="number" step="0.01" name="semester1" value="<?php echo $fee['semester1']??''; ?>"></td></tr>
<tr><td>Semester 2</td><td><input type="number" step="0.01" name="semester2" value="<?php echo $fee['semester2']??''; ?>"></td></tr>
</table>

<h5>Monthly Fees</h5>
<table class="table table-bordered"><tr>
<?php foreach($months as $m): ?>
<th><?php echo ucfirst($m); ?></th>
<?php endforeach; ?>
</tr><tr>
<?php foreach($months as $m): ?>
<td><input type="number" step="0.01" name="month_<?php echo $m; ?>" value="<?php echo $fee['month_'.$m]??''; ?>"></td>
<?php endforeach; ?>
</tr></table>

<div class="mb-3">
<label>Payment Date</label>
<input type="date" name="payment_date" class="form-control" value="<?php echo $fee['payment_date']??date('Y-m-d'); ?>">
</div>

<button class="btn btn-success" type="submit">Submit & Generate Receipt</button>
</form>
</div>
</body>
</html>
