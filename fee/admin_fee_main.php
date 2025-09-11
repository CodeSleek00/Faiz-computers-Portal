<?php
// admin_fee_main.php
session_start();
include '../database_connection/db_connect.php';
if (!$conn) die("Database connection not found");

// Student list fetch
$students = [];
$sql = "SELECT student_id, name, enrollment_id FROM students ORDER BY name ASC";
$res = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $students[] = $row;
}

// Handle fee submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id     = intval($_POST['student_id']);
    $month_no       = intval($_POST['month_no']);
    $month_name     = date("F", mktime(0, 0, 0, $month_no, 10)); // 1→January, etc.
    $year           = intval($_POST['year']);
    $amount         = floatval($_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Unique receipt no generate
    $receipt_no = "RCPT" . date("YmdHis") . rand(100, 999);

    // Insert query
    $sql = "INSERT INTO student_fees 
        (student_id, month_no, month_name, year, amount, payment_method, payment_date, receipt_no) 
        VALUES (?,?,?,?,?,?,NOW(),?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiisdss", 
        $student_id, $month_no, $month_name, $year, $amount, $payment_method, $receipt_no
    );

    if (mysqli_stmt_execute($stmt)) {
        // Redirect to receipt page
        header("Location: fee_receipt.php?receipt_no=" . urlencode($receipt_no));
        exit;
    } else {
        echo "<p style='color:red'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Fee Submission</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f5f6fa; padding:20px; }
        .container { max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align:center; }
        label { display:block; margin-top:15px; font-weight:bold; }
        select, input { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:5px; }
        button { margin-top:20px; padding:12px; background:#0984e3; color:#fff; border:none; border-radius:5px; cursor:pointer; font-size:16px; width:100%; }
        button:hover { background:#074f94; }
    </style>
</head>
<body>
<div class="container">
    <h2>Submit Student Fee (Month-wise)</h2>
    <form method="POST">
        <label for="student_id">Select Student</label>
        <select name="student_id" required>
            <option value="">-- Select Student --</option>
            <?php foreach ($students as $stu) { ?>
                <option value="<?= $stu['student_id'] ?>">
                    <?= htmlspecialchars($stu['name']) ?> (<?= $stu['enrollment_id'] ?>)
                </option>
            <?php } ?>
        </select>

        <label for="month_no">Month</label>
        <select name="month_no" required>
            <option value="">-- Select Month --</option>
            <?php for ($m=1; $m<=12; $m++) { ?>
                <option value="<?= $m ?>"><?= date("F", mktime(0,0,0,$m,10)) ?></option>
            <?php } ?>
        </select>

        <label for="year">Year</label>
        <input type="number" name="year" value="<?= date('Y') ?>" required>

        <label for="amount">Amount</label>
        <input type="number" step="0.01" name="amount" required>

        <label for="payment_method">Payment Method</label>
        <select name="payment_method" required>
            <option value="Cash">Cash</option>
            <option value="Online">Online</option>
            <option value="UPI">UPI</option>
            <option value="Card">Card</option>
        </select>

        <button type="submit">Submit Fee & Generate Receipt</button>
    </form>
</div>
</body>
</html>
