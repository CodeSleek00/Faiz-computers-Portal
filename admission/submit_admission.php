<?php
include "config.php";

// Upload photo
$target_dir = "uploads/";
$photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
$target_file = $target_dir . $photo_name;
move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);

// Collect form data
$data = $_POST;

$sql = "INSERT INTO admissions 
(photo, full_name, aadhar_number, aapar_id, gender, phone, dob, address, permanent_address, religion, email, parents_mobile,
tenth_school, tenth_board, tenth_percentage, tenth_year,
twelfth_school, twelfth_board, twelfth_percentage, twelfth_year,
degree_name, college_name, degree_year, degree_percentage,
course_name, duration, reg_fee, per_month_fee, exam_fee, internal_exam_fee)
VALUES
('$photo_name', '{$data['full_name']}', '{$data['aadhar_number']}', '{$data['aapar_id']}', '{$data['gender']}',
'{$data['phone']}', '{$data['dob']}', '{$data['address']}', '{$data['permanent_address']}', '{$data['religion']}',
'{$data['email']}', '{$data['parents_mobile']}',
'{$data['tenth_school']}', '{$data['tenth_board']}', '{$data['tenth_percentage']}', '{$data['tenth_year']}',
'{$data['twelfth_school']}', '{$data['twelfth_board']}', '{$data['twelfth_percentage']}', '{$data['twelfth_year']}',
'{$data['degree_name']}', '{$data['college_name']}', '{$data['degree_year']}', '{$data['degree_percentage']}',
'{$data['course_name']}', '{$data['duration']}', '{$data['reg_fee']}', '{$data['per_month_fee']}', '{$data['exam_fee']}', '{$data['internal_exam_fee']}')
";

if (mysqli_query($conn, $sql)) {
    echo "<h2>Admission Form Submitted Successfully!</h2>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
