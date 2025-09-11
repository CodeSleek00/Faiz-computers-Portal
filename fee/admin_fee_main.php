<?php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// Fetch all students for dropdown
$students = [];
$res = mysqli_query($conn, "SELECT student_id, name, enrollment_id FROM students ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($res)) $students[] = $row;

// Get selected student_id
$student_id = $_GET['student_id'] ?? ($_POST['student_id'] ?? '');
$student = null;
if ($student_id) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

// Handle Fee Submission
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && $student) {
    $month_no       = intval($_POST['month_no']);
    $month_name     = date("F", mktime(0,0,0,$month_no,10));
    $year           = intval($_POST['year']);
    $amount         = floatval($_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $receipt_no     = "RCPT" . date("YmdHis") . rand(100,999);

    $sql = "INSERT INTO student_fees
            (student_id, month_no, month_name, year, amount, payment_method, payment_date, receipt_no)
            VALUES (?,?,?,?,?,?,NOW(),?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiisdss", $student_id, $month_no, $month_name, $year, $amount, $payment_method, $receipt_no);

    if ($stmt->execute()) {
        $msg = "Fee submitted successfully! Receipt No: $receipt_no";
        header("Location: fee_receipt.php?receipt_no=" . urlencode($receipt_no));
        exit;
    } else {
        $msg = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Submit Fee</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9;font-family:'Segoe UI',sans-serif;}
.card{margin:40px auto;padding:25px;border-radius:15px;max-width:600px;}
h3{font-weight:600;}
</style>
</head>
<body>
<div class="card shadow-sm">
<h3 class="mb-4">Submit Fee</h3>

<?php if($msg): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<!-- Student Dropdown -->
<form method="get" class="mb-4">
<label>Select Student</label>
<select name="student_id" class="form-select" onchange="this.form.submit()" required>
<option value="">-- Select Student --</option>
<?php foreach($students as $stu): ?>
<option value="<?= $stu['student_id'] ?>" <?= ($stu['student_id']==$student_id)?'selected':'' ?>>
<?= htmlspecialchars($stu['name']) ?> (<?= $stu['enrollment_id'] ?>)
</option>
<?php endforeach; ?>
</select>
</form>

<?php if($student): ?>
<form method="post">
<input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">

<div class="mb-3">
<label>Student Name</label>
<input type="text" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" disabled>
</div>

<div class="mb-3">
<label>Month</label>
<select name="month_no" class="form-select" required>
<?php for($m=1;$m<=12;$m++): ?>
<option value="<?= $m ?>"><?= date("F", mktime(0,0,0,$m,10)) ?></option>
<?php endfor; ?>
</select>
</div>

<div class="mb-3">
<label>Year</label>
<input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
</div>

<div class="mb-3">
<label>Amount (₹)</label>
<input type="number" step="0.01" name="amount" class="form-control" required>
</div>

<div class="mb-3">
<label>Payment Method</label>
<select name="payment_method" class="form-select" required>
<option value="Cash">Cash</option>
<option value="Online">Online</option>
<option value="UPI">UPI</option>
<option value="Card">Card</option>
</select>
</div>

<button type="submit" class="btn btn-success w-100">Submit Fee & Generate Receipt</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
