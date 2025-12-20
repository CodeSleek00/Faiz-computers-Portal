<?php
include("db_connect.php");

$enrollment_id = $_GET['eid'];
$month_no = $_GET['month_no'] ?? null;

// Fetch student + paid months
$query = "SELECT * FROM student_monthly_fee WHERE enrollment_id='$enrollment_id'";
if($month_no) $query .= " AND month_no='$month_no'";
$fees = $conn->query($query);

echo "<h2>Fee Receipt for $enrollment_id</h2>";

while($fee = $fees->fetch_assoc()){
    echo "<div style='border:1px solid #ccc;padding:10px;margin:10px;'>";
    echo "<strong>Name:</strong> ".$fee['name']."<br>";
    echo "<strong>Month:</strong> ".$fee['month_name']."<br>";
    echo "<strong>Fee Paid:</strong> ₹".$fee['fee_amount']."<br>";
    echo "<strong>Payment Date:</strong> ".date('d-M-Y', strtotime($fee['payment_date']))."<br>";
    echo "</div>";
}
?>
