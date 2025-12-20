<?php
include("db_connect.php");

/* ================= SAFE GET DATA ================= */
$fee_id = $_GET['fee_id'] ?? null;

if (!$fee_id) {
    die("Invalid fee ID.");
}

/* ================= FETCH FEE ROW ================= */
$fee = $conn->query("SELECT * FROM student_monthly_fee WHERE id='$fee_id'")->fetch_assoc();

if (!$fee) {
    die("Fee record not found.");
}

/* ================= HANDLE FORM SUBMISSION ================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = (float)($_POST['fee_amount'] ?? 0);

    if ($amount <= 0) {
        $error = "Please enter a valid fee amount.";
    } else {
        // Update fee row
        $conn->query("
            UPDATE student_monthly_fee
            SET fee_amount='$amount', payment_status='Paid', payment_date=NOW()
            WHERE id='$fee_id'
        ");

        // Redirect to receipt
        header("Location: view_receipt.php?fee_id=$fee_id");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Fee - <?= htmlspecialchars($fee['name']) ?></title>
<style>
body{font-family:Arial; background:#f4f6f8; padding:20px;}
form{background:#fff; padding:20px; max-width:500px; margin:auto; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
input[type=number]{width:100%; padding:8px; margin:10px 0;}
button{padding:10px 15px; border:none; background:#198754; color:#fff; cursor:pointer;}
h2{background:#0d6efd; color:#fff; padding:10px; border-radius:4px;}
.error{color:#dc3545;}
</style>
</head>
<body>

<h2>Submit Fee for <?= htmlspecialchars($fee['name']) ?></h2>

<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

<form method="POST">
    <label>Fee Type:</label>
    <input type="text" value="<?= htmlspecialchars($fee['fee_type']) ?>" disabled>

    <?php if (!empty($fee['month_name'])): ?>
    <label>Month:</label>
    <input type="text" value="<?= htmlspecialchars($fee['month_name']) ?>" disabled>
    <?php endif; ?>

    <label>Fee Amount:</label>
    <input type="number" name="fee_amount" value="<?= htmlspecialchars($fee['fee_amount']) ?>" required>

    <button type="submit">Submit Fee</button>
</form>

</body>
</html>
