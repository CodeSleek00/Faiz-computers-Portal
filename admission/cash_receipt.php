<?php
session_start();
include "config.php";

if (!isset($_SESSION['admission_id'])) {
    header("Location: index.php");
    exit();
}

$id = $_SESSION['admission_id'];

// FETCH STUDENT DATA
$result = mysqli_query($conn, "SELECT * FROM admissions WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

// MARK PAYMENT AS PAID
mysqli_query($conn, "UPDATE admissions SET payment_status='Paid (Cash)' WHERE id='$id'");

// AUTO ADMISSION NUMBER
$admission_no = "AD-" . date("Y") . "-" . str_pad($id, 4, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admission Receipt</title>
<style>
body { background:#f4f4f4; font-family:Arial; padding:40px; }
.container {
    width:70%; margin:auto; background:#fff; padding:25px;
    border:2px solid black; border-radius:5px;
}
.section-title {
    font-size:20px; margin-top:30px; margin-bottom:10px;
    font-weight:bold; text-decoration: underline;
}
table{
    width:100%; border-collapse: collapse;
}
table td, table th {
    border:1px solid #000; padding:10px; font-size:16px;
}
.photo {
    width:140px; height:140px; border:1px solid #000;
}
.print-btn {
    padding:12px 25px; background:blue; color:white;
    font-size:18px; border:none; cursor:pointer; margin:20px 0;
}
</style>
</head>
<body>

<div class="container">

<div style="text-align:center;">
    <h2>STUDENT ADMISSION RECEIPT</h2>
    <p><b>Admission No: <?= $admission_no ?></b></p>
</div>

<center><img src="uploads/<?= $data['photo'] ?>" class="photo"></center>

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

<div class="section-title">Education Details</div>
<table>
<tr><th>10th School</th><td><?= $data['tenth_school'] ?></td>
<th>10th Board</th><td><?= $data['tenth_board'] ?></td>
<th>10th Percentage</th><td><?= $data['tenth_percentage'] ?></td>
<th>10th Year</th><td><?= $data['tenth_year'] ?></td></tr>

<tr><th>12th School</th><td><?= $data['twelfth_school'] ?></td>
<th>12th Board</th><td><?= $data['twelfth_board'] ?></td>
<th>12th Percentage</th><td><?= $data['twelfth_percentage'] ?></td>
<th>12th Year</th><td><?= $data['twelfth_year'] ?></td></tr>

<tr><th>Degree Name</th><td><?= $data['degree_name'] ?></td>
<th>College Name</th><td><?= $data['college_name'] ?></td>
<th>Degree Percentage</th><td><?= $data['degree_percentage'] ?></td>
<th>Degree Year</th><td><?= $data['degree_year'] ?></td></tr>
</table>

<div class="section-title">Course Details</div>
<table>
<tr><th>Course Name</th><td><?= $data['course_name'] ?></td></tr>
<tr><th>Duration</th><td><?= $data['duration'] ?></td></tr>
<tr><th>Admission Date</th><td><?= date("d-m-Y"); ?></td></tr>
</table>

<center>
<button class="print-btn" onclick="window.print()">Print Receipt</button>
</center>

</div>

</body>
</html>
