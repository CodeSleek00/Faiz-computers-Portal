<?php
session_start();
include "config.php";

if (!isset($_SESSION['admission_id'])) {
    header("Location: index.php");
    exit();
}

$id = $_SESSION['admission_id'];

// FETCH ADMISSION DATA
$result = mysqli_query($conn, "SELECT * FROM admissions WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

// UPDATE PAYMENT STATUS
mysqli_query($conn, "UPDATE admissions SET payment_status='Paid (Cash)' WHERE id='$id'");

// AUTO ADMISSION NUMBER
$admission_no = "AD-" . date("Y") . "-" . str_pad($id, 4, "0", STR_PAD_LEFT);

// ---------- CREATE STUDENT ACCOUNT (student_2026) ----------

// MONTH SHORT NAME (JAN, FEB...)
$month = strtoupper(date("M"));

// YEAR SHORT
$year_short = "26"; // FIXED FOR 2026

// GET LAST ENROLLMENT ID
$check = mysqli_query($conn, "SELECT enrollment_id FROM student_2026 ORDER BY id DESC LIMIT 1");

if (mysqli_num_rows($check) > 0) {
    $row = mysqli_fetch_assoc($check);

    // extract 4-digit number --> FAIZ-JAN26-1001
    $last_no = (int)substr($row['enrollment_id'], -4);
    $new_no = $last_no + 1;

} else {
    $new_no = 1001; // starting number
}

// NEW ENROLLMENT ID
$enrollment_id = "FAIZ-" . $month . $year_short . "-" . $new_no;

// INSERT STUDENT INTO student_2026
mysqli_query($conn, "
INSERT INTO student_2026 (enrollment_id, name, photo, phone, address, course, password)
VALUES (
    '$enrollment_id',
    '{$data['full_name']}',
    '{$data['photo']}',
    '{$data['phone']}',
    '{$data['address']}',
    '{$data['course_name']}',
    '{$data['phone']}'
)
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Admission Receipt</title>
<style>
body {
    background:#f4f4f4;
    font-family:Arial;
    padding:40px;
}
.container {
    width:70%;
    margin:auto;
    background:white;
    padding:25px;
    border: 2px solid black;
    border-radius:8px;
}
.section-title {
    font-size:20px;
    font-weight:bold;
    text-decoration: underline;
    margin-top:30px;
    margin-bottom:10px;
}
table {
    width:100%;
    border-collapse: collapse;
}
table th, table td {
    border:1px solid black;
    padding:10px;
    font-size:16px;
}
.photo {
    width:140px;
    height:140px;
    border:1px solid black;
}
.print-btn {
    padding:12px 20px;
    font-size:18px;
    background:blue;
    color:white;
    border:none;
    cursor:pointer;
    margin-top:20px;
}
</style>
</head>
<body>

<div class="container">

<div style="text-align:center;">
    <h2><b>STUDENT ADMISSION RECEIPT</b></h2>
    <p><b>Admission No:</b> <?= $admission_no ?></p>
    <p><b>Enrollment ID:</b> <?= $enrollment_id ?></p>
</div>

<center>
    <img src="uploads/<?= $data['photo'] ?>" class="photo">
</center>

<!-- PERSONAL DETAILS -->
<div class="section-title">Personal Details</div>
<table>
<tr><th>Full Name</th><td><?= $data['full_name'] ?></td></tr>
<tr><th>Aadhar Number</th><td><?= $data['aadhar_number'] ?></td></tr>
<tr><th>Gender</th><td><?= $data['gender'] ?></td></tr>
<tr><th>DOB</th><td><?= $data['dob'] ?></td></tr>
<tr><th>Phone</th><td><?= $data['phone'] ?></td></tr>
<tr><th>Email</th><td><?= $data['email'] ?></td></tr>
<tr><th>Address</th><td><?= $data['address'] ?></td></tr>
<tr><th>Permanent Address</th><td><?= $data['permanent_address'] ?></td></tr>
</table>

<!-- EDUCATION DETAILS -->
<div class="section-title">Education Details</div>
<table>
<tr><th>10th School</th><td><?= $data['tenth_school'] ?></td></tr>
<tr><th>10th Board</th><td><?= $data['tenth_board'] ?></td></tr>
<tr><th>10th Percentage</th><td><?= $data['tenth_percentage'] ?></td></tr>
<tr><th>10th Year</th><td><?= $data['tenth_year'] ?></td></tr>

<tr><th>12th School</th><td><?= $data['twelfth_school'] ?></td></tr>
<tr><th>12th Board</th><td><?= $data['twelfth_board'] ?></td></tr>
<tr><th>12th Percentage</th><td><?= $data['twelfth_percentage'] ?></td></tr>
<tr><th>12th Year</th><td><?= $data['twelfth_year'] ?></td></tr>

<tr><th>Degree Name</th><td><?= $data['degree_name'] ?></td></tr>
<tr><th>College Name</th><td><?= $data['college_name'] ?></td></tr>
<tr><th>Degree Percentage</th><td><?= $data['degree_percentage'] ?></td></tr>
<tr><th>Degree Year</th><td><?= $data['degree_year'] ?></td></tr>
</table>

<!-- COURSE DETAILS -->
<div class="section-title">Course & Fee Details</div>
<table>
<tr><th>Course Name</th><td><?= $data['course_name'] ?></td></tr>
<tr><th>Duration</th><td><?= $data['duration'] ?></td></tr>
<tr><th>Registration Fee</th><td>₹<?= $data['reg_fee'] ?></td></tr>
<tr><th>Monthly Fee</th><td>₹<?= $data['per_month_fee'] ?></td></tr>
<tr><th>Exam Fee</th><td>₹<?= $data['exam_fee'] ?></td></tr>
<tr><th>Internal Exam Fee</th><td>₹<?= $data['internal_exam_fee'] ?></td></tr>
<tr><th>Total Amount Paid</th><td><b>₹<?= $data['reg_fee'] ?></b></td></tr>
</table>

<!-- PAYMENT -->
<div class="section-title">Payment Status</div>
<table>
<tr><th>Payment Method</th><td>CASH</td></tr>
<tr><th>Status</th><td><b style="color:green;">PAID</b></td></tr>
<tr><th>Date</th><td><?= date("d-m-Y"); ?></td></tr>
</table>

<center>
<button class="print-btn" onclick="window.print()">Print Receipt</button>
</center>

</div>

</body>
</html>
