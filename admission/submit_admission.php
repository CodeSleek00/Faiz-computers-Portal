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
        (enrollment_id, name, student_photo, degree, school_college, board_university, year_of_passing, percentage)
        VALUES
        ('$enrollment_id','$name','$photo_name','$degree[$i]','$school_college[$i]','$board[$i]','$yearp[$i]','$perc[$i]')
        ");
    }
}

/* ================= NEW STUDENT_FEE TABLE INSERT ================= */

// Prepare 12 months
$months = array_fill(1, 12, $per_month_fee);

$conn->query("
INSERT INTO student_fee
(enrollment_id, name, photo, course_name, registration_fee, monthly_fee,
month_1, month_2, month_3, month_4, month_5, month_6,
month_7, month_8, month_9, month_10, month_11, month_12,
july_internal_fee, dec_internal_fee, first_semester_fee, second_semester_fee)
VALUES
('$enrollment_id','$name','$photo_name','$course_name','$registration_fee','$per_month_fee',
'$months[1]','$months[2]','$months[3]','$months[4]','$months[5]','$months[6]',
'$months[7]','$months[8]','$months[9]','$months[10]','$months[11]','$months[12]',
'$internal_fee','$internal_fee','$semester_exam_fee','$semester_exam_fee')
");
/* ================= INSERT MONTHLY FEES ================= */
// Months array
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// Insert monthly fees for the student
for($i=0; $i<$duration_months; $i++){
    $month_name = $months[$i];

    $conn->query("
        INSERT INTO student_monthly_fee
        (enrollment_id, name, photo, course_name, month_no, month_name, fee_amount, payment_status)
        VALUES
        ('$enrollment_id','$name','$photo_name','$course_name',".($i+1).",'$month_name','$per_month_fee','Pending')
    ");
}

/* ================= INSERT INTERNAL / SEMESTER FEES ================= */
// Internal Fee 2 times
$internal_months = [7,12]; // July and December
foreach($internal_months as $m_no){
    $conn->query("
        INSERT INTO student_monthly_fee
        (enrollment_id, name, photo, course_name, month_no, month_name, fee_amount, payment_status)
        VALUES
        ('$enrollment_id','$name','$photo_name','$course_name','$m_no','".$months[$m_no-1]."','$internal_fee','Pending')
    ");
}

// Semester Exam Fee 2 times
$semester_months = [6,12]; // June and December
foreach($semester_months as $m_no){
    $conn->query("
        INSERT INTO student_monthly_fee
        (enrollment_id, name, photo, course_name, month_no, month_name, fee_amount, payment_status)
        VALUES
        ('$enrollment_id','$name','$photo_name','$course_name','$m_no','".$months[$m_no-1]."','$semester_exam_fee','Pending')
    ");
}

// Additional Fee if any
if($additional_fee>0){
    $conn->query("
        INSERT INTO student_monthly_fee
        (enrollment_id, name, photo, course_name, month_no, month_name, fee_amount, payment_status)
        VALUES
        ('$enrollment_id','$name','$photo_name','$course_name',0,'Additional','$additional_fee','Pending')
    ");
}

/* ================= COMMIT ================= */
$conn->commit();

/* ================= REDIRECT ================= */
header("Location: admission_success.php?eid=$enrollment_id");
exit;
?>
