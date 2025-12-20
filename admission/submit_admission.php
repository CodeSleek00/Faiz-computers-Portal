<?php
include "db_connect.php";

$name = $_POST['name'];
$phone = $_POST['phone'];
$course = $_POST['course'];

$month = strtoupper(date("M"));
$year = date("y");

/* Generate Enrollment ID */
$q = "SELECT COUNT(*) AS total FROM students26 
      WHERE enrollment_id LIKE 'FAIZ-$month$year%'";
$r = mysqli_query($conn, $q);
$d = mysqli_fetch_assoc($r);

$number = 1001 + $d['total'];
$enrollment_id = "FAIZ-$month$year-$number";

/* Password = phone */
$password = $phone;

/* Upload Image */
$image = $_FILES['image']['name'];
$tmp = $_FILES['image']['tmp_name'];
$path = "uploads/".$image;
move_uploaded_file($tmp, $path);

/* Admission table */
mysqli_query($conn,"INSERT INTO admission
(name,aadhar_number,apaar_id,phone,email,religion,caste,address,permanent_address,dob,
image,degree_name,board_university,year_of_passing,percentage,
father_name,mother_name,parent_contact,
course_name,duration,registration_fee,per_month_fee,internal_fee,semester_exam_fee,additional_fee)
VALUES
('$name','$_POST[aadhar]','$_POST[apaar]','$phone','$_POST[email]',
'$_POST[religion]','$_POST[caste]','$_POST[address]','$_POST[permanent_address]','$_POST[dob]',
'$path','$_POST[degree]','$_POST[board]','$_POST[year]','$_POST[percentage]',
'$_POST[father]','$_POST[mother]','$_POST[parent_contact]',
'$course','$_POST[duration]','$_POST[registration_fee]','$_POST[monthly_fee]',
'$_POST[internal_fee]','$_POST[semester_fee]','$_POST[additional_fee]'
)");

/* Students table */
mysqli_query($conn,"INSERT INTO students26
(name,photo,contact,course,enrollment_id,password)
VALUES
('$name','$path','$phone','$course','$enrollment_id','$password')");

header("Location: admission_success.php?id=$enrollment_id");
?>
