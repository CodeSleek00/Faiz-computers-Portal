<?php
include("../database_connection/db_connect.php");

/* ================= BASIC SAFETY ================= */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");

/* ================= SAFE POST DATA & UPPERCASE ================= */
$name     = strtoupper($_POST['name'] ?? '');
$dob      = $_POST['dob'] ?? '';
$aadhar   = strtoupper($_POST['aadhar'] ?? '');
$apaar    = strtoupper($_POST['apaar'] ?? '');
$phone    = $_POST['phone'] ?? '';
$email    = strtoupper($_POST['email'] ?? '');
$religion = strtoupper($_POST['religion'] ?? '');
$caste    = strtoupper($_POST['caste'] ?? '');
$address  = strtoupper($_POST['address'] ?? '');
$permanent_address = strtoupper($_POST['permanent_address'] ?? '');
$identification_mark = strtoupper($_POST['identification_mark'] ?? '');
$husband_name = strtoupper($_POST['husband_name'] ?? '');

$father_name = strtoupper($_POST['father_name'] ?? '');
$mother_name = strtoupper($_POST['mother_name'] ?? '');
$parent_contact = $_POST['parent_contact'] ?? '';

$course_name = strtoupper($_POST['course_name'] ?? '');
$duration_months = (int)($_POST['duration'] ?? 0);

$registration_fee   = (float)($_POST['registration_fee'] ?? 0);
$per_month_fee      = (float)($_POST['per_month_fee'] ?? 0);
$internal_fee       = (float)($_POST['internal_fee'] ?? 0);
$semester_exam_fee  = (float)($_POST['semester_exam_fee'] ?? 0);
$additional_fee     = (float)($_POST['additional_fee'] ?? 0);

/* ================= IMAGE UPLOAD ================= */
$photo_name = '';
$upload_dir = __DIR__ . "/../uploads/";

if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $photo_name = time() . "_" . strtoupper(basename($_FILES['photo']['name']));
    move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_name);
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

/* ================= ADMISSION MONTH LOGIC ================= */
$admission_month_no = (int)date('n'); // 1-12
$all_months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];

$rotated_months = [];
for ($i = 0; $i < 12; $i++) {
    $index = ($admission_month_no - 1 + $i) % 12;
    $rotated_months[] = $all_months[$index];
}

/* ================= START TRANSACTION ================= */
$conn->begin_transaction();

/* ================= STUDENTS TABLE ================= */
$conn->query("
INSERT INTO students26
(name, photo, contact, address, course, enrollment_id, password)
VALUES
('$name','$photo_name','$phone','$permanent_address','$course_name','$enrollment_id','$phone')
");

/* ================= ADMISSION TABLE ================= */
$conn->query("
INSERT INTO admission
(
 name,aadhar,apaar,phone,email,religion,caste,address,permanent_address,
 dob,photo,father_name,mother_name,husband_name,parent_contact,
 identification_mark,
 course_name,duration,
 registration_fee,per_month_fee,internal_fee,semester_exam_fee,additional_fee,enrollment_id
)
VALUES
(
 '$name','$aadhar','$apaar','$phone','$email','$religion','$caste','$address','$permanent_address',
 '$dob','$photo_name','$father_name','$mother_name','$husband_name','$parent_contact',
 '$identification_mark',
 '$course_name','$duration_months',
 '$registration_fee','$per_month_fee','$internal_fee','$semester_exam_fee','$additional_fee','$enrollment_id'
)
");

/* ================= EDUCATION QUALIFICATION ================= */
$degree = $_POST['degree'] ?? [];
$school_college = $_POST['school_college'] ?? [];
$board = $_POST['board'] ?? [];
$yearp = $_POST['year'] ?? [];
$perc = $_POST['percentage'] ?? [];

for ($i = 0; $i < count($degree); $i++) {
    if (!empty($degree[$i])) {

        $year_clean = !empty($yearp[$i]) ? (int)$yearp[$i] : NULL;
        $perc_clean = !empty($perc[$i]) ? (float)$perc[$i] : NULL;

        $conn->query("
        INSERT INTO education_qualification
        (enrollment_id, name, student_photo, degree, school_college, board_university, year_of_passing, percentage)
        VALUES
        ('$enrollment_id','$name','$photo_name',
         '".strtoupper($degree[$i])."','".strtoupper($school_college[$i])."','".strtoupper($board[$i])."',
         ".($year_clean === NULL ? "NULL" : "'$year_clean'").",
         ".($perc_clean === NULL ? "NULL" : "'$perc_clean'").")
        ");
    }
}

/* ================= STUDENT_FEE TABLE ================= */
$conn->query("
INSERT INTO student_fee
(
 enrollment_id, name, photo, course_name,
 registration_fee, monthly_fee, additional_fee,
 month_1, month_2, month_3, month_4, month_5, month_6,
 month_7, month_8, month_9, month_10, month_11, month_12,
 july_internal_fee, dec_internal_fee,
 first_semester_fee, second_semester_fee
)
VALUES
(
 '$enrollment_id','$name','$photo_name','$course_name',
 '$registration_fee','$per_month_fee','$additional_fee',
 '$per_month_fee','$per_month_fee','$per_month_fee','$per_month_fee','$per_month_fee','$per_month_fee',
 '$per_month_fee','$per_month_fee','$per_month_fee','$per_month_fee','$per_month_fee','$per_month_fee',
 '$internal_fee','$internal_fee',
 '$semester_exam_fee','$semester_exam_fee'
)
");

/* ================= STUDENT_MONTHLY_FEE ================= */

/* Registration Fee */
$conn->query("
INSERT INTO student_monthly_fee
(enrollment_id,name,photo,course_name,fee_type,month_no,month_name,fee_amount,payment_status)
VALUES
('$enrollment_id','$name','$photo_name','$course_name',
 'REGISTRATION','$admission_month_no','".$rotated_months[0]."','$registration_fee','PENDING')
");

/* Monthly Fees (Dynamic Month-1) */
for ($i = 0; $i < $duration_months; $i++) {

    $month_no   = ($admission_month_no + $i - 1) % 12 + 1;
    $month_name = $rotated_months[$i];

    $conn->query("
    INSERT INTO student_monthly_fee
    (enrollment_id,name,photo,course_name,fee_type,month_no,month_name,fee_amount,payment_status)
    VALUES
    ('$enrollment_id','$name','$photo_name','$course_name',
     'MONTHLY','$month_no','$month_name','$per_month_fee','PENDING')
    ");
}

/* Internal Fees (July & December) */
foreach ([7,12] as $m) {
    $conn->query("
    INSERT INTO student_monthly_fee
    (enrollment_id,name,photo,course_name,fee_type,month_no,month_name,fee_amount,payment_status)
    VALUES
    ('$enrollment_id','$name','$photo_name','$course_name',
     'INTERNAL','$m','".$all_months[$m-1]."','$internal_fee','PENDING')
    ");
}

/* Semester Fees (June & December) */
foreach ([6,12] as $m) {
    $conn->query("
    INSERT INTO student_monthly_fee
    (enrollment_id,name,photo,course_name,fee_type,month_no,month_name,fee_amount,payment_status)
    VALUES
    ('$enrollment_id','$name','$photo_name','$course_name',
     'SEMESTER','$m','".$all_months[$m-1]."','$semester_exam_fee','PENDING')
    ");
}

/* Additional Fee */
if ($additional_fee > 0) {
    $conn->query("
    INSERT INTO student_monthly_fee
    (enrollment_id,name,photo,course_name,fee_type,fee_amount,payment_status)
    VALUES
    ('$enrollment_id','$name','$photo_name','$course_name',
     'ADDITIONAL','$additional_fee','PENDING')
    ");
}

/* ================= COMMIT ================= */
$conn->commit();

/* ================= REDIRECT ================= */
header("Location: admission_success.php?eid=$enrollment_id");
exit;
?>
