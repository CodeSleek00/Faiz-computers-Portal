<?php
include("db_connect.php"); // apna DB connection

// ================= SAFE POST DATA =================
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

$course   = $_POST['course'] ?? '';
$duration = (int)($_POST['duration'] ?? 0);

$registration_fee = (float)($_POST['registration_fee'] ?? 0);
$monthly_fee      = (float)($_POST['monthly_fee'] ?? 0);
$internal_fee     = (float)($_POST['internal_fee'] ?? 0);
$semester_fee     = (float)($_POST['semester_fee'] ?? 0);
$additional_fee   = (float)($_POST['additional_fee'] ?? 0);

// ================= IMAGE UPLOAD =================
$photo_name = '';
if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){
    $photo_name = time().'_'.$_FILES['photo']['name'];
    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/".$photo_name);
}

// ================= ENROLLMENT ID GENERATE =================
$month = strtoupper(date("M"));
$year  = date("y");

$q = mysqli_query($conn,"
SELECT enrollment_id 
FROM students 
WHERE enrollment_id LIKE 'FAIZ-$month$year-%'
ORDER BY enrollment_id DESC LIMIT 1
");

if(mysqli_num_rows($q) > 0){
    $row = mysqli_fetch_assoc($q);
    $last = (int)substr($row['enrollment_id'], -4);
    $new  = $last + 1;
}else{
    $new = 1001;
}

$enrollment_id = "FAIZ-$month$year-$new";

// ================= STUDENTS TABLE =================
mysqli_query($conn,"
INSERT INTO students26
(name, photo, contact, course, enrollment_id, password)
VALUES
('$name','$photo_name','$phone','$course','$enrollment_id','$phone')
");

// ================= ADMISSION TABLE =================
mysqli_query($conn,"
INSERT INTO admission
(name,aadhar,apaar,phone,email,religion,caste,address,permanent_address,
dob,photo,father_name,mother_name,parent_contact,course,duration,
registration_fee,monthly_fee,internal_fee,semester_fee,additional_fee,enrollment_id)
VALUES
('$name','$aadhar','$apaar','$phone','$email','$religion','$caste','$address','$permanent_address',
'$dob','$photo_name','$father_name','$mother_name','$parent_contact','$course','$duration',
'$registration_fee','$monthly_fee','$internal_fee','$semester_fee','$additional_fee','$enrollment_id')
");

// ================= EDUCATION QUALIFICATION =================
$degree = $_POST['degree'] ?? [];
$school_college = $_POST['school_college'] ?? [];
$board  = $_POST['board'] ?? [];
$yearp  = $_POST['year'] ?? [];
$perc   = $_POST['percentage'] ?? [];

for($i=0; $i<count($degree); $i++){
    if($degree[$i] != ""){
        mysqli_query($conn,"
        INSERT INTO education_qualification
        (enrollment_id,name,degree,school_college,board,year,percentage)
        VALUES
        ('$enrollment_id','$name','$degree[$i]','$school_college[$i]','$board[$i]','$yearp[$i]','$perc[$i]')
        ");
    }
}

// ================= FEE SCHEDULE =================
// Registration
mysqli_query($conn,"
INSERT INTO fee_schedule
(enrollment_id,student_name,course_name,fee_type,amount,fee_month)
VALUES
('$enrollment_id','$name','$course','Registration','$registration_fee','".date("M-Y")."')
");

// Monthly Fee
for($i=0; $i<$duration; $i++){
    $m = date("M-Y", strtotime("+$i month"));
    mysqli_query($conn,"
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course','Monthly','$monthly_fee','$m')
    ");
}

// Internal Fee 2 times
for($i=0;$i<2;$i++){
    $m = date("M-Y", strtotime("+".($i*3)." month"));
    mysqli_query($conn,"
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course','Internal','$internal_fee','$m')
    ");
}

// Semester Fee 2 times
for($i=0;$i<2;$i++){
    $m = date("M-Y", strtotime("+".($i*3)." month"));
    mysqli_query($conn,"
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course','Semester','$semester_fee','$m')
    ");
}

// Additional Fee 1 time
if($additional_fee > 0){
    mysqli_query($conn,"
    INSERT INTO fee_schedule
    (enrollment_id,student_name,course_name,fee_type,amount,fee_month)
    VALUES
    ('$enrollment_id','$name','$course','Additional','$additional_fee','".date("M-Y")."')
    ");
}

// ================= REDIRECT =================
header("Location: admission_success.php?eid=$enrollment_id");
exit;
?>
