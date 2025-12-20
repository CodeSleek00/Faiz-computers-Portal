<?php
include("db_connect.php");

if($_SERVER['REQUEST_METHOD']=='POST'){
    $fee_ids = $_POST['fee_ids'] ?? [];

    if(empty($fee_ids)){
        die("No fee selected.");
    }

    $enrollment_id = '';

    foreach($fee_ids as $fid){
        // Get enrollment_id for redirect/receipt
        $res = $conn->query("SELECT enrollment_id FROM student_monthly_fee WHERE id='$fid'")->fetch_assoc();
        $enrollment_id = $res['enrollment_id'];

        // Update payment status
        $conn->query("
            UPDATE student_monthly_fee
            SET payment_status='Paid', payment_date=NOW()
            WHERE id='$fid'
        ");
    }

    // Redirect to receipt page
    header("Location: view_receipt.php?eid=".$enrollment_id."&fees=".implode(',', $fee_ids));
    exit;
}
?>
