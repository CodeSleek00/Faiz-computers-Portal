<?php
include "db_connect.php";

/* =========================
   BASIC DATA
========================= */

$name     = $_POST['name'];
$phone    = $_POST['phone'];
$email    = $_POST['email'];
$dob      = $_POST['dob'];
$religion = $_POST['religion'];
$caste    = $_POST['caste'];

$address  = $_POST['address'];
$permanent_address = $_POST['permanent_address'];

$father  = $_POST['father'];
$mother  = $_POST['mother'];
$parent_contact = $_POST['parent_contact'];

$course   = $_POST['course'];
$duration = $_POST['duration'];

$registration_fee = $_POST['registration_fee'];
$monthly_fee      = $_POST['monthly_fee'];
$internal_fee     = $_POST['internal_fee'];
$semester_fee     = $_POST['semester_fee'];
$additional_fee   = $_POST['additional_fee'];


/* =========================
   ENROLLMENT ID GENERATION
   FAIZ-JAN26-1001
========================= */

$month = strtoupper(date("M"));   // JAN
$year  = date("y");               // 26

$q = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM admission 
     WHERE enrollment_id LIKE 'FAIZ-$month$year%'"
);

$d = mysqli_fetch_assoc($q);
$next = 1001 + $d['total'];

$enrollment_id = "FAIZ-$month$year-$next";


/* =========================
   IMAGE UPLOAD
========================= */

$image_name = $_FILES['image']['name'];
$tmp_name   = $_FILES['image']['tmp_name'];

$folder = "uploads/";
if (!is_dir($folder)) {
    mkdir($folder);
}

$photo_path = $folder . time() . "_" . $image_name;
move_uploaded_file($tmp_name, $photo_path);


/* =========================
   INSERT INTO ADMISSION
========================= */

$admissionQuery = "
INSERT INTO admission
(
 enrollment_id, name, photo, phone, email,
 religion, caste, address, permanent_address, dob,
 father_name, mother_name, parent_contact,
 course_name, duration,
 registration_fee, per_month_fee, internal_fee,
 semester_exam_fee, additional_fee
)
VALUES
(
 '$enrollment_id', '$name', '$photo_path', '$phone', '$email',
 '$religion', '$caste', '$address', '$permanent_address', '$dob',
 '$father', '$mother', '$parent_contact',
 '$course', '$duration',
 '$registration_fee', '$monthly_fee', '$internal_fee',
 '$semester_fee', '$additional_fee'
)
";

mysqli_query($conn, $admissionQuery);

/* admission_id for education table */
$admission_id = mysqli_insert_id($conn);


/* =========================
   INSERT EDUCATION DETAILS
========================= */

$degrees = $_POST['degree'];
$boards  = $_POST['board'];
$years   = $_POST['year'];
$percs   = $_POST['percentage'];

for ($i = 0; $i < count($degrees); $i++) {

    if (!empty($degrees[$i])) {

        mysqli_query($conn, "
        INSERT INTO education_qualification
        (
            admission_id,
            enrollment_id,
            student_name,
            student_photo,
            degree_name,
            board_university,
            year_of_passing,
            percentage
        )
        VALUES
        (
            '$admission_id',
            '$enrollment_id',
            '$name',
            '$photo_path',
            '{$degrees[$i]}',
            '{$boards[$i]}',
            '{$years[$i]}',
            '{$percs[$i]}'
        )
        ");
    }
}


/* =========================
   INSERT INTO STUDENTS
   (LOGIN TABLE)
   Password = Phone
========================= */

mysqli_query($conn, "
INSERT INTO students26
(name, photo, contact, course, enrollment_id, password)
VALUES
('$name', '$photo_path', '$phone', '$course', '$enrollment_id', '$phone')
");


/* =========================
   REDIRECT
========================= */

header("Location: admission_success.php?id=$enrollment_id");
exit;

?>
