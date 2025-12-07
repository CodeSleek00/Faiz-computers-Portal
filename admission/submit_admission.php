<?php
session_start();
include "config.php";

// CREATE UPLOAD FOLDER
if (!is_dir("../uploads")) {
    mkdir("../uploads");
}

// PHOTO UPLOAD
$target_dir = "../uploads/";
$photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
$target_file = $target_dir . $photo_name;
move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);

// COLLECT FORM DATA
$data = $_POST;

// INSERT DATA INTO admissions
$sql = "INSERT INTO admissions
(photo, full_name, aadhar_number, aapar_id, gender, phone, dob, address, permanent_address, religion, email, parents_mobile,
tenth_school, tenth_board, tenth_percentage, tenth_year,
twelfth_school, twelfth_board, twelfth_percentage, twelfth_year,
degree_name, college_name, degree_year, degree_percentage,
course_name, duration, reg_fee, per_month_fee, exam_fee, internal_exam_fee,
payment_method)
VALUES
('$photo_name', '{$data['full_name']}', '{$data['aadhar_number']}', '{$data['aapar_id']}',
'{$data['gender']}', '{$data['phone']}', '{$data['dob']}',
'{$data['address']}', '{$data['permanent_address']}', '{$data['religion']}',
'{$data['email']}', '{$data['parents_mobile']}',
'{$data['tenth_school']}', '{$data['tenth_board']}', '{$data['tenth_percentage']}', '{$data['tenth_year']}',
'{$data['twelfth_school']}', '{$data['twelfth_board']}', '{$data['twelfth_percentage']}', '{$data['twelfth_year']}',
'{$data['degree_name']}', '{$data['college_name']}', '{$data['degree_year']}', '{$data['degree_percentage']}',
'{$data['course_name']}', '{$data['duration']}', '{$data['reg_fee']}', '{$data['per_month_fee']}',
'{$data['exam_fee']}', '{$data['internal_exam_fee']}',
'{$data['payment_method']}'
)";

if (mysqli_query($conn, $sql)) {

    $admission_id = mysqli_insert_id($conn);

    $_SESSION['admission_id'] = $admission_id;
    $_SESSION['student_name'] = $data['full_name'];

    // -----------------------------------------
    // INSERT INTO students_2026
    // -----------------------------------------
    $sql2 = "INSERT INTO students_2026
    (photo, full_name, phone, email, course_name, duration, reg_fee, per_month_fee, exam_fee, internal_exam_fee, admission_id)
    VALUES
    ('$photo_name', '{$data['full_name']}', '{$data['phone']}', '{$data['email']}', '{$data['course_name']}', '{$data['duration']}', '{$data['reg_fee']}', '{$data['per_month_fee']}', '{$data['exam_fee']}', '{$data['internal_exam_fee']}', '$admission_id')";

    if(mysqli_query($conn, $sql2)){
        $student2026_id = mysqli_insert_id($conn);

        // -----------------------------------------
        // AUTO-GENERATE FEES FOR students_2026
        // -----------------------------------------
        $duration = $data['duration'];
        $reg_fee = $data['reg_fee'];
        $per_month_fee = $data['per_month_fee'];
        $exam_fee = $data['exam_fee'];
        $internal_exam_fee = $data['internal_exam_fee'];

        $months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
        $startIndex = date('n') - 1;

        // 1️⃣ Registration Fee
        mysqli_query($conn, "INSERT INTO fee_master (student_id, month_name, fee_type, amount) VALUES ('$student2026_id','N/A','registration','$reg_fee')");

        // 2️⃣ Monthly Fee
        for($i=0; $i<$duration; $i++){
            $month_name = $months[($startIndex+$i)%12];
            mysqli_query($conn, "INSERT INTO fee_master (student_id, month_name, fee_type, amount) VALUES ('$student2026_id','$month_name','monthly_fee','$per_month_fee')");
        }

        // 3️⃣ Exam Fee (2 times)
        for($i=1; $i<=2; $i++){
            mysqli_query($conn, "INSERT INTO fee_master (student_id, month_name, fee_type, amount) VALUES ('$student2026_id','Exam-$i','exam_fee','$exam_fee')");
        }

        // 4️⃣ Internal Exam Fee (2 times)
        for($i=1; $i<=2; $i++){
            mysqli_query($conn, "INSERT INTO fee_master (student_id, month_name, fee_type, amount) VALUES ('$student2026_id','Internal-$i','internal_exam_fee','$internal_exam_fee')");
        }
    }

    // -----------------------------------------
    // REDIRECT BASED ON PAYMENT METHOD
    // -----------------------------------------
    if ($data['payment_method'] == "cash") {
        header("Location: cash_success.php");
        exit();
    } else {
        header("Location: razorpay_payment.php");
        exit();
    }

} else {
    echo "Database Error: " . mysqli_error($conn);
}
?>
