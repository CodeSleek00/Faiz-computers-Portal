<?php
include("db_connect.php");

if($_SERVER['REQUEST_METHOD']=='POST'){
    $fee_ids = $_POST['fee_ids'] ?? [];

    if(empty($fee_ids)){
        die("No fee selected.");
    }

    foreach($fee_ids as $fid){
        $conn->query("
            UPDATE student_monthly_fee
            SET payment_status='Paid', payment_date=NOW()
            WHERE id='$fid'
        ");
    }

    // Redirect to dashboard with success message
    header("Location: dashboard.php?msg=Fees paid successfully");
    exit;
}
?>
