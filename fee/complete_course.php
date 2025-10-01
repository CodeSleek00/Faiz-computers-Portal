<?php
include 'db_connect.php';

$student_id = $_GET['student_id'];
$conn->query("DELETE FROM student_fees WHERE student_id=$student_id");

header("Location: admin_fee_dashboard.php");
exit;
