<?php
include("db_connect.php");

/* ================= BASIC SAFETY ================= */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

/* ================= SAFE POST DATA ================= */
$name     = $_POST['name'] ?? '';
$dob      = $_POST['dob'] ?? '';
$aadhar   = $_POST['aadhar'] ?? '';
$apaar    = $_POST['apaar'] ?? '';
$phone    = $_POST['phone'] ?? '';
$email    = $_POST['email'] ?? '';
$religion = $_POST['religion'] ?? '';
$caste    = $_POST['caste'] ?? '';
$address  = $_POST['address'] ?? '';
$permanent_address = $_POST['permanent_address'] ?? '';

$father_name = $_POST['father_name'] ?? '';
$mother_name = $_POST['mother_name'] ?? '';
$parent_contact = $_POST['parent_contact'] ?? '';

$course_name = $_POST['course_name'] ?? '';
$duration_months = (int)($_POST['duration'] ?? 0);

$registration_fee   = (float)($_POST['registration_fee'] ?? 0);
$per_month_fee      = (float)($_POST['per_month_fee'] ?? 0);
$internal_fee       = (float)($_POST['internal_fee'] ?? 0);
$semester_exam_fee  = (float)($_POST['semester_exam_fee'] ?? 0);
$additional_fee     = (float)($_POST['additional_fee'] ?? 0);

/* ================= IMAGE UPLOAD ================= */
$photo_name = '';
if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }
    $photo_name = time() . "_" . basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo_name);
}

/* ================= ENROLLMENT ID ================= */
$month = strtoupper(date("M"));
$year  = date("y");

$q = $conn->query("
    SELECT enrollment_id 
    FROM students26
    WHERE enrollment_id LIKE 'FAIZ-$month$year-%'
    ORDER BY enrollment_id DESC
    LIMIT 1
");

if ($q->num_rows > 0) {
    $row = $q->fetch_assoc();
    $last = (int)substr($row['enrollment_id'], -4);
    $new  = $last + 1;
} else {
    $new = 1001;
}

$enrollment_id = "FAIZ-$month$year-$new";

/* ================= START TRANSACTION ================= */
$conn->begin_transaction();

/* ================= STUDENTS TABLE ================= */
$conn->query("
INSERT INTO students26
(name, photo, contact, course, enrollment_id, password)
VALUES
('$name','$photo_name','$phone','$course_name','$enrollment_id','$phone')
");

/* ================= ADMISSION TABLE ================= */
$conn->query("
INSERT INTO admission
(name,aadhar,apaar,phone,email,religion,caste,address,permanent_address,
dob,photo,father_name,mother_name,parent_contact,course_name,duration,
registration_fee,per_month_fee,internal_fee,semester_exam_fee,additional_fee,enrollment_id)
VALUES
('$name','$aadhar','$apaar','$phone','$email','$religion','$caste','$address','$permanent_address',
'$dob','$photo_name','$father_name','$mother_name','$parent_contact','$course_name','$duration_months',
'$registration_fee','$per_month_fee','$internal_fee','$semester_exam_fee','$additional_fee','$enrollment_id')
");

/* ================= EDUCATION QUALIFICATION ================= */
$degree = $_POST['degree'] ?? [];
$school_college = $_POST['school_college'] ?? [];
$board = $_POST['board'] ?? [];
$yearp = $_POST['year'] ?? [];
$perc = $_POST['percentage'] ?? [];

for ($i = 0; $i < count($degree); $i++) {
    if (!empty($degree[$i])) {
        $conn->query("
        INSERT INTO education_qualification
        (enrollment_id,name,degree,school_college,board,year,percentage)
        VALUES
        ('$enrollment_id','$name','$degree[$i]','$school_college[$i]','$board[$i]','$yearp[$i]','$perc[$i]')
        ");
    }
}

/* ================= FEE SCHEDULE ================= */

/* Registration Fee */
$conn->query("
INSERT INTO fee_schedule
(enrollment_id,student_name,course_name,fee_type,amount,fee_month)
VALUES
('$enrollment_id','$name','$course_name','Registration','$registration_fee','".date("M-Y")."')
");

/* Monthly Fees */
for ($i = 0; $i < $duration_months; $i++) {
    $m = date("M-Y", strtotime("+$i month"));
    $conn->query("
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course_name','Monthly','$per_month_fee','$m')
    ");
}

/* Internal Fees (2 times) */
for ($i = 0; $i < 2; $i++) {
    $m = date("M-Y", strtotime("+".($i * 3)." month"));
    $conn->query("
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course_name','Internal','$internal_fee','$m')
    ");
}

/* Semester Fees (2 times) */
for ($i = 0; $i < 2; $i++) {
    $m = date("M-Y", strtotime("+".($i * 6)." month"));
    $conn->query("
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course_name','Semester','$semester_exam_fee','$m')
    ");
}

/* Additional Fee */
if ($additional_fee > 0) {
    $conn->query("
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course_name','Additional','$additional_fee','".date("M-Y")."')
    ");
}

/* ================= COMMIT ================= */
$conn->commit();

/* ================= REDIRECT ================= */
header("Location: admission_success.php?eid=$enrollment_id");
exit;
?>
