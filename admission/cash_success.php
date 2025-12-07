<?php
session_start();

if (!isset($_SESSION['admission_id'])) {
    header("Location: index.php");
    exit();
}

$name = $_SESSION['student_name'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Cash Confirmation</title>
<style>
body { font-family: Arial; text-align:center; background:#f4f4f4; padding-top:80px; }
.box {
    background:white; width:400px; margin:auto; padding:25px;
    border-radius:10px; box-shadow:0 0 10px #aaa;
}
.btn {
    display:inline-block; padding:12px 20px; background:#28a745;
    color:white; text-decoration:none; border-radius:8px; font-size:18px;
}
.btn:hover { background:#218838; }
</style>
</head>
<body>

<div class="box">
    <h2>Confirm Cash Payment</h2>
    <p>Dear <b><?php echo $name; ?></b>,<br><br>
    Are you sure you want to confirm CASH PAYMENT?</p>

    <a class="btn" href="cash_receipt.php">YES, CONFIRM</a>
</div>

</body>
</html>
