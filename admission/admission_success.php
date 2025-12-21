<?php
include("db_connect.php");

$eid = $_GET['eid'] ?? '';
if(!$eid){
    die("Invalid Admission ID");
}

/* ===== FETCH STUDENT DATA ===== */
$q = $conn->query("
    SELECT *
    FROM admission
    WHERE enrollment_id='$eid'
");
$data = $q->fetch_assoc();

/* ===== FETCH EDUCATION ===== */
$edu = $conn->query("
    SELECT * FROM education_qualification
    WHERE enrollment_id='$eid'
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admission Print</title>

<style>
body{
    font-family: Arial;
    background:#f2f2f2;
}
.a4{
    width:210mm;
    min-height:297mm;
    background:#fff;
    margin:20px auto;
    padding:20mm;
    border:1px solid #000;
}
h2,h3{
    text-align:center;
    margin:5px 0;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}
td,th{
    border:1px solid #000;
    padding:8px;
    font-size:14px;
}
.photo{
    width:120px;
    height:140px;
    border:1px solid #000;
    text-align:center;
}
.sign-box{
    height:150px;
    text-align:center;
    vertical-align:bottom;
}
.print-btn{
    text-align:center;
    margin:15px;
}
@media print{
    body{ background:#fff; }
    .print-btn{ display:none; }
}
</style>
</head>

<body>

<div class="print-btn">
    <button onclick="window.print()">🖨 Print Admission Form</button>
</div>

<div class="a4">

<h2>FAIZ COMPUTER INSTITUTE</h2>
<h3>STUDENT ADMISSION FORM</h3>

<table>
<tr>
<td><b>Enrollment ID</b></td>
<td><?= $data['enrollment_id'] ?></td>
<td rowspan="4" class="photo">
<?php if($data['photo']){ ?>
<img src="uploads/<?= $data['photo'] ?>" width="120" height="140">
<?php } else { echo "Photo"; } ?>
</td>
</tr>
<tr><td>Name</td><td><?= $data['name'] ?></td></tr>
<tr><td>DOB</td><td><?= $data['dob'] ?></td></tr>
<tr><td>Phone</td><td><?= $data['phone'] ?></td></tr>
</table>

<h3>Personal Details</h3>
<table>
<tr><td>Aadhar</td><td><?= $data['aadhar'] ?></td></tr>
<tr><td>Religion</td><td><?= $data['religion'] ?></td></tr>
<tr><td>Caste</td><td><?= $data['caste'] ?></td></tr>
<tr><td>Address</td><td><?= $data['address'] ?></td></tr>
</table>

<h3>Parents Details</h3>
<table>
<tr><td>Father Name</td><td><?= $data['father_name'] ?></td></tr>
<tr><td>Mother Name</td><td><?= $data['mother_name'] ?></td></tr>
<tr><td>Parent Contact</td><td><?= $data['parent_contact'] ?></td></tr>
</table>

<h3>Education Qualification</h3>
<table>
<tr>
<th>Degree</th>
<th>School / College</th>
<th>Board</th>
<th>Year</th>
<th>%</th>
</tr>
<?php while($e = $edu->fetch_assoc()){ ?>
<tr>
<td><?= $e['degree'] ?></td>
<td><?= $e['school_college'] ?></td>
<td><?= $e['board_university'] ?></td>
<td><?= $e['year_of_passing'] ?></td>
<td><?= $e['percentage'] ?></td>
</tr>
<?php } ?>
</table>

<h3>Course & Fees</h3>
<table>
<tr><td>Course</td><td><?= $data['course_name'] ?></td></tr>
<tr><td>Duration</td><td><?= $data['duration'] ?> Months</td></tr>
<tr><td>Registration Fee</td><td>₹<?= $data['registration_fee'] ?></td></tr>
<tr><td>Semester Exam Fee</td><td>₹<?= $data['semester_exam_fee'] ?></td></tr>
<tr><td>Internal Fee</td><td>₹<?= $data['internal_fee'] ?></td></tr>
<tr><td>Monthly Fee</td><td>₹<?= $data['per_month_fee'] ?></td></tr>
</table>

<br><br>

<table>
<tr>
<td class="sign-box">Student Signature</td>
<td class="sign-box">Left Thumb Impression</td>
<td class="sign-box">Right Thumb Impression</td>
</tr>
</table>

</div>

</body>
</html>
