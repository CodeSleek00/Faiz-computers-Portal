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
<title>Admission Form - <?= $data['enrollment_id'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:'Poppins',sans-serif;
    background:#f5f5f5;
    font-size:12px;
}

.a4{
    width:210mm;
    min-height:297mm;
    background:#fff;
    margin:20px auto;
    padding:5mm;
    position:relative;
}

/* ===== ENROLLMENT ID (TOP MOST) ===== */
.enrollment-id{
    position:absolute;
    top:5mm;
    right:20mm;
    background:#2c3e50;
    color:#fff;
    padding:6px 16px;
    font-weight:600;
    font-size:13px;
    border-radius:4px;
    z-index:10;
}

/* ===== HEADER ===== */
.header{
    text-align:center;
    margin-top:15mm;
    border-bottom:2px solid #2c3e50;
    padding-bottom:10px;
}

.institute-name{
    font-size:24px;
    font-weight:700;
    color:#2c3e50;
}

.institute-tagline{
    font-size:14px;
    color:#7f8c8d;
}

.form-title{
    font-size:18px;
    font-weight:600;
    color:#e74c3c;
    margin-top:8px;
}

/* ===== PHOTO ===== */
.photo-container{
    width:110px;
    height:140px;
    border:2px solid #7f8c8d;
    position:absolute;
    right:20mm;
    top:35mm;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

.photo-container img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* ===== COMMON ===== */
.section-title{
    background:#34495e;
    color:#fff;
    padding:6px 10px;
    font-size:14px;
    margin:15px 0 8px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:12px;
}

td,th{
    border:1px solid #bdc3c7;
    padding:6px;
}

th{
    background:#ecf0f1;
}

.two-column{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}

.signature-table td{
    height:90px;
    vertical-align:bottom;
    text-align:center;
}

.sign-space{
    height:150px;
    border-bottom:1px solid #000;
}

.footer-note{
    text-align:center;
    font-size:10px;
    margin-top:12px;
    color:#7f8c8d;
}

.print-btn{
    text-align:center;
    margin:15px;
}

@media print{
    .print-btn{display:none}
    body{background:#fff}
    .a4{margin:0}
}
</style>
</head>

<body>

<div class="print-btn">
    <button onclick="window.print()">🖨 Print Admission Form</button>
</div>

<div class="a4">

<div class="enrollment-id">
    Enrollment ID : <?= $data['enrollment_id'] ?>
</div>

<div class="header">
    <div class="institute-name">FAIZ COMPUTER INSTITUTE</div>
    <div class="institute-tagline">Quality Computer Education Since 2005</div>
    <div class="form-title">Student Admission Form</div>
</div>

<div class="photo-container">
<?php if(!empty($data['photo'])){ ?>
    <img src="../uploads/<?= $data['photo'] ?>">
<?php } else { ?>
    Photo
<?php } ?>
</div>

<div class="section-title">Student Information</div>
<table>
<tr>
    <td><b>Name</b></td><td><?= $data['name'] ?></td>
    <td><b>DOB</b></td><td><?= date('d/m/Y',strtotime($data['dob'])) ?></td>
</tr>
<tr>
    <td><b>Student Phone</b></td><td><?= $data['phone'] ?></td>
    <td><b>Email</b></td><td><?= $data['email'] ?></td>
</tr>
</table>

<div class="two-column">
<div>
<div class="section-title">Personal Details</div>
<table>
<tr><td>Aadhar</td><td><?= $data['aadhar'] ?></td></tr>
<tr><td>Religion</td><td><?= $data['religion'] ?></td></tr>
<tr><td>Caste</td><td><?= $data['caste'] ?></td></tr>

<?php if(!empty($data['identification_mark'])){ ?>
<tr>
    <td>Visible Identification Mark</td>
    <td><?= $data['identification_mark'] ?></td>
</tr>
<?php } ?>

<tr><td>Address</td><td><?= nl2br($data['address']) ?></td></tr>
</table>
</div>

<div>
<div class="section-title">Parent Details</div>
<table>
<tr><td>Apaar</td><td><?= $data['apaar'] ?></td></tr>
<tr><td>Father's Name</td><td><?= $data['father_name'] ?></td></tr>
<tr><td>Mother's Name</td><td><?= $data['mother_name'] ?></td></tr>
<tr><td>Parents Contact</td><td><?= $data['parent_contact'] ?></td></tr>
</table>
</div>
</div>

<div class="section-title">Educational Qualification</div>
<table>
<tr>
<th>Degree</th><th>Institute</th><th>Board</th><th>Year</th><th>%</th>
</tr>
<?php while($e=$edu->fetch_assoc()){ ?>
<tr>
<td><?= $e['degree'] ?></td>
<td><?= $e['school_college'] ?></td>
<td><?= $e['board_university'] ?></td>
<td><?= $e['year_of_passing'] ?></td>
<td><?= $e['percentage'] ?></td>
</tr>
<?php } ?>
</table>


<div class="section-title">Course & Fees</div>
<table>
<tr><td>Course</td><td><?= $data['course_name'] ?></td></tr>
<tr><td>Duration</td><td><?= $data['duration'] ?> Months</td></tr>
<tr><td>Admission Date</td><td><?= date('d/m/Y',strtotime($data['admission_date'])) ?></td></tr>
</table>

<div class="section-title">Signatures</div>
<table class="signature-table">
<tr>
<td><div class="sign-space"></div>Student Signature</td>
<td><div class="sign-space"></div>Left Thumb</td>
<td><div class="sign-space"></div>Right Thumb</td>
</tr>
</table>

<div class="footer-note">
Generated on <?= date('d/m/Y H:i') ?> | <?= $data['enrollment_id'] ?>
</div>

</div>
</body>
</html>
