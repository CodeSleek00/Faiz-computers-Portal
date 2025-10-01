<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
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
