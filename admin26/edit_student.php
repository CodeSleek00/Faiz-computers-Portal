<?php
include("../database_connection/db_connect.php");

$id = $_GET['id'] ?? 0;

/* ================= FETCH STUDENT DATA ================= */
$sql = "
SELECT 
    s.id, s.name, s.contact, s.enrollment_id, s.course, s.photo,
    a.aadhar, a.apaar, a.email, a.religion, a.caste, a.address, a.permanent_address, a.dob,
    a.father_name, a.mother_name, a.parent_contact,
    a.course_name, a.duration, a.registration_fee, a.per_month_fee, a.internal_fee, a.semester_exam_fee
FROM students26 s
LEFT JOIN admission a ON s.enrollment_id = a.enrollment_id
WHERE s.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) die("Student not found");

$old_enrollment = $student['enrollment_id'];

/* ================= UPDATE LOGIC ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==== PERSONAL ====
    $name        = $_POST['name'];
    $contact     = $_POST['contact'];
    $enrollment  = $_POST['enrollment_id'];
    $photo       = $_FILES['photo']['name'] ? time().'_'.basename($_FILES['photo']['name']) : $student['photo'];
    if ($_FILES['photo']['name']) move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/".$photo);

    $dob         = $_POST['dob'];
    $father      = $_POST['father_name'];
    $mother      = $_POST['mother_name'];
    $parent_c    = $_POST['parent_contact'];

    // ==== ADDRESS ====
    $address     = $_POST['address'];
    $paddress    = $_POST['permanent_address'];

    // ==== IDENTITY ====
    $aadhar      = $_POST['aadhar'];
    $apaar       = $_POST['apaar'];
    $religion    = $_POST['religion'];
    $caste       = $_POST['caste'];

    // ==== ACADEMIC ====
    $course      = $_POST['course'];
    $course_name = $_POST['course_name'];
    $duration    = $_POST['duration'];

    // ==== FEES ====
    $registration_fee = (float)$_POST['registration_fee'];
    $monthly_fee      = (float)$_POST['monthly_fee'];
    $internal_fee     = (float)$_POST['internal_fee'];
    $semester_fee     = (float)$_POST['semester_fee'];

    $conn->begin_transaction();

    // ===== students26 =====
    $stmt1 = $conn->prepare("
        UPDATE students26 SET
            name=?, contact=?, enrollment_id=?, course=?, photo=?
        WHERE id=?
    ");
    $stmt1->bind_param("sssssi",$name,$contact,$enrollment,$course,$photo,$id);
    $stmt1->execute();

    // ===== admission =====
    $stmt2 = $conn->prepare("
    UPDATE admission SET
        name=?, phone=?, aadhar=?, apaar=?, email=?, religion=?, caste=?,
        address=?, permanent_address=?, dob=?, father_name=?, mother_name=?, parent_contact=?,
        course_name=?, duration=?, registration_fee=?, per_month_fee=?, internal_fee=?, semester_exam_fee=?,
        photo=?, enrollment_id=?
    WHERE enrollment_id=?
");

$stmt2->bind_param(
    "ssssssssssssssiddddsss",
    $name,
    $contact,
    $aadhar,
    $apaar,
    $student['email'],
    $religion,
    $caste,
    $address,
    $paddress,
    $dob,
    $father,
    $mother,
    $parent_c,
    $course_name,
    $duration,
    $registration_fee,
    $monthly_fee,
    $internal_fee,
    $semester_fee,
    $photo,
    $enrollment,
    $old_enrollment
);

    $stmt2->execute();

    // ===== student_fee =====
    $stmt3 = $conn->prepare("
        UPDATE student_fee SET
            name=?, course_name=?, registration_fee=?, monthly_fee=?, july_internal_fee=?,
            dec_internal_fee=?, first_semester_fee=?, second_semester_fee=?, enrollment_id=?
        WHERE enrollment_id=?
    ");
    $stmt3->bind_param("ssddddddss",
        $name,$course_name,$registration_fee,$monthly_fee,$internal_fee,
        $internal_fee,$semester_fee,$semester_fee,$enrollment,$old_enrollment
    );
    $stmt3->execute();

    // ===== student_monthly_fee =====
    $stmt4 = $conn->prepare("
        UPDATE student_monthly_fee SET
            name=?, course_name=?, enrollment_id=?
        WHERE enrollment_id=?
    ");
    $stmt4->bind_param("ssss",$name,$course_name,$enrollment,$old_enrollment);
    $stmt4->execute();

    // ===== monthly fees update =====
    $stmt5 = $conn->prepare("
        UPDATE student_monthly_fee SET fee_amount=? WHERE enrollment_id=? AND fee_type='Monthly'
    ");
    $stmt5->bind_param("ds",$monthly_fee,$enrollment);
    $stmt5->execute();

    $conn->commit();
    header("Location: admin_dashboard26.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student (Full)</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
body{font-family:Poppins;background:#f4f6f9}
.box{max-width:1000px;margin:30px auto;background:#fff;padding:25px;border-radius:12px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
input,textarea,button{width:100%;padding:10px;border-radius:6px;border:1px solid #d1d5db}
textarea{resize:vertical}
button{background:#2563eb;color:#fff;border:none;margin-top:20px;font-size:15px}
h2{margin-bottom:15px}
.full{grid-column:1 / -1}
</style>
</head>
<body>

<div class="box">
<h2>Edit Student (All Details & Fees)</h2>
<form method="post" enctype="multipart/form-data">
<div class="grid">

<!-- Personal -->
<input name="name" value="<?= htmlspecialchars($student['name']) ?>" placeholder="Name" required>
<input name="contact" value="<?= htmlspecialchars($student['contact']) ?>" placeholder="Contact" required>
<input name="enrollment_id" value="<?= htmlspecialchars($student['enrollment_id']) ?>" placeholder="Enrollment ID" required>
<input name="course" value="<?= htmlspecialchars($student['course']) ?>" placeholder="Course (Short)" required>
<input type="file" name="photo">

<input name="dob" type="date" value="<?= htmlspecialchars($student['dob']) ?>">
<input name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>" placeholder="Father Name">
<input name="mother_name" value="<?= htmlspecialchars($student['mother_name']) ?>" placeholder="Mother Name">
<input name="parent_contact" value="<?= htmlspecialchars($student['parent_contact']) ?>" placeholder="Parent Contact">

<!-- Address -->
<textarea class="full" name="address" placeholder="Address"><?= htmlspecialchars($student['address']) ?></textarea>
<textarea class="full" name="permanent_address" placeholder="Permanent Address"><?= htmlspecialchars($student['permanent_address']) ?></textarea>

<!-- Identity -->
<input name="aadhar" value="<?= htmlspecialchars($student['aadhar']) ?>" placeholder="Aadhar">
<input name="apaar" value="<?= htmlspecialchars($student['apaar']) ?>" placeholder="Apaar">
<input name="religion" value="<?= htmlspecialchars($student['religion']) ?>" placeholder="Religion">
<input name="caste" value="<?= htmlspecialchars($student['caste']) ?>" placeholder="Caste">

<!-- Academic -->
<input name="course_name" value="<?= htmlspecialchars($student['course_name']) ?>" placeholder="Course Name">
<input name="duration" value="<?= htmlspecialchars($student['duration']) ?>" placeholder="Duration (Months)">

<!-- Fees -->
<input name="registration_fee" value="<?= $student['registration_fee'] ?>" placeholder="Registration Fee">
<input name="monthly_fee" value="<?= $student['per_month_fee'] ?>" placeholder="Monthly Fee">
<input name="internal_fee" value="<?= $student['internal_fee'] ?>" placeholder="Internal Fee">
<input name="semester_fee" value="<?= $student['semester_exam_fee'] ?>" placeholder="Semester Fee">

</div>
<button type="submit">Update Student & Fees</button>
</form>
</div>
</body>
</html>
