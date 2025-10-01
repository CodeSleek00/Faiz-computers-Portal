<?php
include 'db_connect.php';

$student_id = $_GET['student_id'];
$student = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $month = $_POST['month'];
    $amount = $_POST['amount'];

    $stmt = $conn->prepare("INSERT INTO student_fees (student_id, month, amount, fee_type) VALUES (?,?,?,?)");
    $fee_type = "Monthly Fee";
    $stmt->bind_param("isis", $student_id, $month, $amount, $fee_type);
    $stmt->execute();
    $last_id = $stmt->insert_id;

    header("Location: fee_receipt.php?fee_id=$last_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set Fee - <?php echo $student['name']; ?></title>
</head>
<body>
<h2>Set Fee for <?php echo $student['name']; ?> (<?php echo $student['enrollment_id']; ?>)</h2>
<form method="post">
    <label>Month:</label>
    <select name="month" required>
        <option value="">-- Select Month --</option>
        <?php
        $months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
        foreach($months as $m){
            echo "<option value='$m'>$m</option>";
        }
        ?>
    </select><br><br>
    <label>Amount:</label>
    <input type="number" name="amount" required><br><br>
    <button type="submit">Submit Fee</button>
</form>
</body>
</html>
