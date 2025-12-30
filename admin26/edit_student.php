<?php
include("../database_connection/db_connect.php");

$id = $_GET['id'] ?? 0;

/* ================= FETCH STUDENT DATA ================= */
$sql = "
SELECT 
    s.id,
    s.name,
    s.contact,
    s.enrollment_id,
    s.course,
    s.photo,

    a.course_name,
    a.duration,
    a.registration_fee,
    a.per_month_fee,
    a.internal_fee,
    a.semester_exam_fee

FROM students26 s
LEFT JOIN admission a 
ON s.enrollment_id = a.enrollment_id
WHERE s.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found");
}

$old_enrollment = $student['enrollment_id'];

/* ================= UPDATE LOGIC ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = $_POST['name'];
    $contact     = $_POST['contact'];
    $enrollment  = $_POST['enrollment_id'];
    $course      = $_POST['course'];
    $course_name = $_POST['course_name'];
    $duration    = $_POST['duration'];

    $registration_fee = (float)$_POST['registration_fee'];
    $monthly_fee      = (float)$_POST['monthly_fee'];
    $internal_fee     = (float)$_POST['internal_fee'];
    $semester_fee     = (float)$_POST['semester_fee'];

    /* ================= PHOTO ================= */
    if (!empty($_FILES['photo']['name'])) {
        $photo = time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photo);
    } else {
        $photo = $student['photo'];
    }

    /* ================= START TRANSACTION ================= */
    $conn->begin_transaction();

    /* ---------- students26 ---------- */
    $stmt1 = $conn->prepare("
        UPDATE students26 SET
            name=?, contact=?, enrollment_id=?, course=?, photo=?
        WHERE id=?
    ");
    $stmt1->bind_param("sssssi",
        $name, $contact, $enrollment, $course, $photo, $id
    );
    $stmt1->execute();

    /* ---------- admission ---------- */
    $stmt2 = $conn->prepare("
        UPDATE admission SET
            name=?,
            phone=?,
            course_name=?,
            duration=?,
            registration_fee=?,
            per_month_fee=?,
            internal_fee=?,
            semester_exam_fee=?,
            photo=?,
            enrollment_id=?
        WHERE enrollment_id=?
    ");
    $stmt2->bind_param("sssiddiddss",
        $name,
        $contact,
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

    /* ---------- student_fee ---------- */
    $stmt3 = $conn->prepare("
        UPDATE student_fee SET
            name=?,
            course_name=?,
            registration_fee=?,
            monthly_fee=?,
            july_internal_fee=?,
            dec_internal_fee=?,
            first_semester_fee=?,
            second_semester_fee=?,
            enrollment_id=?
        WHERE enrollment_id=?
    ");
    $stmt3->bind_param("ssddddddss",
        $name,
        $course_name,
        $registration_fee,
        $monthly_fee,
        $internal_fee,
        $internal_fee,
        $semester_fee,
        $semester_fee,
        $enrollment,
        $old_enrollment
    );
    $stmt3->execute();

    /* ---------- student_monthly_fee (sync name & course) ---------- */
    $stmt4 = $conn->prepare("
        UPDATE student_monthly_fee SET
            name=?,
            course_name=?,
            enrollment_id=?
        WHERE enrollment_id=?
    ");
    $stmt4->bind_param("ssss",
        $name,
        $course_name,
        $enrollment,
        $old_enrollment
    );
    $stmt4->execute();

    /* ---------- update monthly fee amount ---------- */
    $stmt5 = $conn->prepare("
        UPDATE student_monthly_fee SET
            fee_amount=?
        WHERE enrollment_id=? AND fee_type='Monthly'
    ");
    $stmt5->bind_param("ds", $monthly_fee, $enrollment);
    $stmt5->execute();

    /* ================= COMMIT ================= */
    $conn->commit();

    header("Location: admin_students.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student (Full Sync)</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">
<style>
body{font-family:Poppins;background:#f4f6f9}
.box{
    max-width:900px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
}
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}
input,button{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #d1d5db;
}
button{
    background:#2563eb;
    color:#fff;
    border:none;
    margin-top:20px;
    font-size:15px;
}
h2{margin-bottom:15px}
</style>
</head>

<body>

<div class="box">
<h2>Edit Student (All Tables Synced)</h2>

<form method="post" enctype="multipart/form-data">
<div class="grid">

<input name="name" value="<?= htmlspecialchars($student['name']) ?>" placeholder="Name" required>
<input name="contact" value="<?= htmlspecialchars($student['contact']) ?>" placeholder="Contact" required>

<input name="enrollment_id" value="<?= htmlspecialchars($student['enrollment_id']) ?>" placeholder="Enrollment ID" required>
<input name="course" value="<?= htmlspecialchars($student['course']) ?>" placeholder="Course (Short)" required>

<input name="course_name" value="<?= htmlspecialchars($student['course_name']) ?>" placeholder="Course Name">
<input name="duration" value="<?= htmlspecialchars($student['duration']) ?>" placeholder="Duration (Months)">

<input name="registration_fee" value="<?= $student['registration_fee'] ?>" placeholder="Registration Fee">
<input name="monthly_fee" value="<?= $student['per_month_fee'] ?>" placeholder="Monthly Fee">

<input name="internal_fee" value="<?= $student['internal_fee'] ?>" placeholder="Internal Fee">
<input name="semester_fee" value="<?= $student['semester_exam_fee'] ?>" placeholder="Semester Fee">

<input type="file" name="photo">

</div>

<button type="submit">Update Student (All Tables)</button>
</form>
</div>

</body>
</html>
