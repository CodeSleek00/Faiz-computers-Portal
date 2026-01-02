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
<title>Admission Form - <?= $data['enrollment_id'] ?? '' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f5f5f5;
    font-size: 12px;
    line-height: 1.4;
}

.a4 {
    width: 210mm;
    min-height: 297mm;
    background: #fff;
    margin: 20px auto;
    padding: 15mm 20mm;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    position: relative;
}

.header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #2c3e50;
    padding-bottom: 10px;
}

.institute-name {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    letter-spacing: 1px;
}

.institute-tagline {
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 400;
    margin-top: 2px;
}

.form-title {
    font-size: 18px;
    font-weight: 600;
    color: #e74c3c;
    margin-top: 10px;
    text-transform: uppercase;
}

.section-title {
    background: #34495e;
    color: white;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 600;
    margin: 15px 0 8px 0;
    border-radius: 3px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
    font-size: 12px;
}

td, th {
    border: 1px solid #bdc3c7;
    padding: 6px 8px;
    vertical-align: top;
}

th {
    background: #ecf0f1;
    font-weight: 600;
}

.photo-container {
    width: 110px;
    height: 140px;
    border: 2px solid #7f8c8d;
    position: absolute;
    right: 20mm;
    top: 15mm;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f8f9fa;
}

.photo-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-placeholder {
    color: #95a5a6;
    font-size: 11px;
    text-align: center;
    padding: 10px;
}

.compact-row td {
    padding: 4px 8px;
}

.signature-section {
    margin-top: 25px;
    text-align: center;
}

.signature-box {
    display: inline-block;
    width: 200px;
    margin: 0 20px;
    text-align: center;
}

.signature-space {
    height: 40px;
    border-bottom: 1px solid #2c3c3d;
    margin-bottom: 5px;
}

.signature-label {
    font-size: 11px;
    color: #2c3e50;
    font-weight: 500;
}

.print-btn {
    text-align: center;
    margin: 20px;
}

.print-btn button {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 30px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.print-btn button:hover {
    background: #2980b9;
}

.footer-note {
    text-align: center;
    font-size: 10px;
    color: #7f8c8d;
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px dashed #bdc3c7;
}

.enrollment-id {
    position: absolute;
    right: 20mm;
    top: 165mm;
    background: #2c3e50;
    color: white;
    padding: 5px 15px;
    border-radius: 3px;
    font-weight: 600;
    font-size: 13px;
}

.course-highlight {
    background: #e8f4fc !important;
    font-weight: 600;
}

@media print {
    body {
        background: #fff;
    }
    
    .print-btn {
        display: none;
    }
    
    .a4 {
        margin: 0;
        padding: 15mm 20mm;
        box-shadow: none;
        border: none;
    }
    
    .course-highlight {
        background: #e8f4fc !important;
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
}
.signature-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}

.signature-table td {
    border: 1px solid #2c3e50;
    height: 90px;
    vertical-align: bottom;
    text-align: center;
    padding: 8px;
}

.sign-space {
    height: 45px;
    border-bottom: 1px solid #000;
    margin-bottom: 6px;
}

.sign-label {
    font-size: 11px;
    font-weight: 500;
    color: #2c3e50;
}


/* Two-column layout for personal details */
.two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.column {
    margin-bottom: 0;
}

.compact-table td {
    padding: 4px 8px;
    height: 28px;
}

.fees-table td:nth-child(2) {
    font-weight: 600;
    color: #2c3e50;
}

/* Ensure all content fits on one page */
.page-break-avoid {
    page-break-inside: avoid;
}

/* Responsive adjustments */
@media screen and (max-width: 900px) {
    .a4 {
        width: 95%;
        padding: 15px;
    }
    
    .photo-container {
        position: static;
        margin: 0 auto 15px auto;
    }
    
    .two-column {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="print-btn">
    <button onclick="window.print()">🖨 Print Admission Form</button>
</div>
<a href="admin26/admin_dashboard26.php" target="_blank">
   Dashobard
</a>


<div class="a4">

<div class="header">
    <div class="institute-name">FAIZ COMPUTER INSTITUTE</div>
    <div class="institute-tagline">Quality Computer Education Since 2005</div>
    <div class="form-title">Student Admission Form</div>
</div>

<div class="photo-container">
    <?php if($data['photo']){ ?>
        <img src="/../uploads/<?= $data['photo'] ?>" alt="Student Photo">
    <?php } else { ?>
        <div class="photo-placeholder">Passport Size<br>Photo</div>
    <?php } ?>
</div>

<div class="enrollment-id">ID: <?= $data['enrollment_id'] ?></div>

<div class="section-title">Student Information</div>
<table class="compact-table">
<tr>
    <td width="25%"><strong>Full Name</strong></td>
    <td width="25%"><?= $data['name'] ?></td>
    <td width="25%"><strong>Date of Birth</strong></td>
    <td width="25%"><?= date('d/m/Y', strtotime($data['dob'])) ?></td>
</tr>
<tr>
    <td><strong>Phone Number</strong></td>
    <td><?= $data['phone'] ?></td>
    <td><strong>Email Address</strong></td>
    <td><?= $data['email'] ?? 'Not provided' ?></td>
</tr>
</table>

<div class="two-column page-break-avoid">
    <div class="column">
        <div class="section-title">Personal Details</div>
        <table class="compact-table">
        <tr><td width="40%"><strong>Aadhar Number</strong></td><td><?= $data['aadhar'] ?></td></tr>
        <tr><td><strong>Religion</strong></td><td><?= $data['religion'] ?></td></tr>
        <tr><td><strong>Caste</strong></td><td><?= $data['caste'] ?></td></tr>
        <tr><td><strong>Address</strong></td><td><?= nl2br($data['address']) ?></td></tr>
        </table>
    </div>
    
    <div class="column">
        <div class="section-title">Parents Details</div>
        <table class="compact-table">
        <tr><td width="40%"><strong>Father's Name</strong></td><td><?= $data['father_name'] ?></td></tr>
        <tr><td><strong>Mother's Name</strong></td><td><?= $data['mother_name'] ?></td></tr>
        <tr><td><strong>Parent's Contact</strong></td><td><?= $data['parent_contact'] ?></td></tr>
        <tr><td><strong>Occupation</strong></td><td><?= $data['parent_occupation'] ?? 'Not provided' ?></td></tr>
        </table>
    </div>
</div>

<div class="section-title page-break-avoid">Educational Qualifications</div>
<table>
<tr>
    <th width="25%">Degree/Qualification</th>
    <th width="30%">School/College</th>
    <th width="20%">Board/University</th>
    <th width="10%">Year</th>
    <th width="15%">Percentage/Grade</th>
</tr>
<?php 
$counter = 0;
while($e = $edu->fetch_assoc()){ 
    $counter++;
?>
<tr>
    <td><?= $e['degree'] ?></td>
    <td><?= $e['school_college'] ?></td>
    <td><?= $e['board_university'] ?></td>
    <td><?= $e['year_of_passing'] ?></td>
    <td><?= $e['percentage'] ?>%</td>
</tr>
<?php } 
if($counter == 0){
    echo '<tr><td colspan="5" style="text-align:center; padding:15px; color:#7f8c8d;">No educational records found</td></tr>';
}
?>
</table>

<div class="section-title page-break-avoid">Course & Fee Details</div>
<table class="fees-table">
<tr class="course-highlight">
    <td width="30%"><strong>Course Name</strong></td>
    <td width="70%"><?= $data['course_name'] ?></td>
</tr>
<tr>
    <td><strong>Duration</strong></td>
    <td><?= $data['duration'] ?> Months</td>
</tr>
<tr>
    <td><strong>Registration Fee</strong></td>
    <td>₹<?= number_format($data['registration_fee'], 2) ?></td>
</tr>
<tr>
    <td><strong>Semester Exam Fee</strong></td>
    <td>₹<?= number_format($data['semester_exam_fee'], 2) ?></td>
</tr>
<tr>
    <td><strong>Internal Fee</strong></td>
    <td>₹<?= number_format($data['internal_fee'], 2) ?></td>
</tr>
<tr>
    <td><strong>Monthly Fee</strong></td>
    <td>₹<?= number_format($data['per_month_fee'], 2) ?> per month</td>
</tr>
<tr class="course-highlight">
    <td><strong>Admission Date</strong></td>
    <td><?= date('d/m/Y', strtotime($data['admission_date'] ?? date('Y-m-d'))) ?></td>
</tr>
</table>

<div class="section-title page-break-avoid">Declaration & Signatures</div>

<table class="signature-table page-break-avoid">
    <tr>
        <td>
            <div class="sign-space"></div>
            <div class="sign-label">Student Signature</div>
        </td>
        <td>
            <div class="sign-space"></div>
            <div class="sign-label">Left Thumb Impression</div>
        </td>
        <td>
            <div class="sign-space"></div>
            <div class="sign-label">Right Thumb Impression</div>
        </td>
    </tr>
</table>


<div class="footer-note">
    This is a computer generated document. No physical signature required.<br>
    Form ID: <?= $data['enrollment_id'] ?> | Generated on: <?= date('d/m/Y H:i:s') ?>
</div>

</div>

<script>
// Ensure the page fits on one A4 sheet when printing
window.onload = function() {
    // Add page break avoidance for printing
    const style = document.createElement('style');
    style.innerHTML = `
        @media print {
            .page-break-avoid {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    `;
    document.head.appendChild(style);
};
</script>

</body>
</html>