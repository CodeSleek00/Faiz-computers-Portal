<?php
include "db.php";

$student_id = $_POST['student_id'];
$monthly_fee = $_POST['monthly_fee'];
$course_duration = $_POST['course_duration'];   // 12 months etc
$registration_fee = $_POST['registration_fee'];
$exam_fee = $_POST['exam_fee'];
$internal_exam_fee = $_POST['internal_exam_fee'];

// Auto-generate MONTHLY fees
$months = ["JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC"];

for ($i=0; $i<$course_duration; $i++) {
    $month = $months[$i % 12];
    $sql = "INSERT INTO fee_structure (student_id, fee_type, month_name, amount)
            VALUES ('$student_id', 'monthly', '$month', '$monthly_fee')";
    mysqli_query($conn, $sql);
}

// Registration Fee (1 time)
mysqli_query($conn, "INSERT INTO fee_structure (student_id, fee_type, month_name, amount)
                     VALUES ('$student_id', 'registration', 'NONE', '$registration_fee')");

// Exam Fee (2 times)
for ($i=1; $i<=2; $i++) {
    mysqli_query($conn, "INSERT INTO fee_structure (student_id, fee_type, month_name, amount)
                         VALUES ('$student_id', 'exam', 'NONE', '$exam_fee')");
}

// Internal Exam Fee (2 times)
for ($i=1; $i<=2; $i++) {
    mysqli_query($conn, "INSERT INTO fee_structure (student_id, fee_type, month_name, amount)
                         VALUES ('$student_id', 'internal_exam', 'NONE', '$internal_exam_fee')");
}

echo "DONE";
?>
