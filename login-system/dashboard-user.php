<?php
session_start();
include '../database_connection/db_connect.php';

if (!isset($_SESSION['enrollment_id'])) {
    header("Location: ../login-system/login.php");
    exit;
}

$enrollment_id = $_SESSION['enrollment_id'];
$student = null;
$table_name = "";

/* 🔹 students table */
$stmt = $conn->prepare("SELECT * FROM students WHERE enrollment_id = ?");
$stmt->bind_param("s", $enrollment_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $student = $res->fetch_assoc();
    $student['contact_number'] = $student['contact_number'];
    $table_name = "students";
    $student_id = $student['student_id'];

} else {
    /* 🔹 students26 table */
    $stmt = $conn->prepare("SELECT * FROM students26 WHERE enrollment_id = ?");
    $stmt->bind_param("s", $enrollment_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $student = $res->fetch_assoc();
        $student['contact_number'] = $student['contact'];
        $table_name = "students26";
        $student_id = $student['id'];
    }
}

if (!$student) {
    echo "Student record not found!";
    exit;
}

/* 🔥 STATUS FETCH */
$status = "Continue"; // default

$stmt = $conn->prepare("SELECT status FROM student_status WHERE student_id=? AND table_name=?");
$stmt->bind_param("is", $student_id, $table_name);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $status = $row['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    padding:20px;
}
.container{
    max-width:1100px;
    margin:auto;
}
.card{
    background:#fff;
    border-radius:10px;
    padding:20px;
    margin-bottom:20px;
}
.profile-header{
    text-align:center;
    background:#2563eb;
    color:white;
    padding:20px;
    border-radius:10px;
}
.profile-header img{
    width:100px;
    height:100px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid white;
}
.profile-name{
    font-size:20px;
    margin-top:10px;
}
.badge{
    display:inline-block;
    padding:6px 12px;
    border-radius:20px;
    margin-top:10px;
    font-size:12px;
}
.status-active {background:#d1fae5;color:#10b981;}
.status-completed {background:#dcfce7;color:#16a34a;}
.status-dropped {background:#fee2e2;color:#dc2626;}
.status-hold {background:#fef3c7;color:#d97706;}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
    margin-top:20px;
}
.item{
    background:#f3f4f6;
    padding:10px;
    border-radius:6px;
}
.label{
    font-size:12px;
    color:#6b7280;
}
.value{
    font-weight:bold;
}
</style>

</head>
<body>

<div class="container">

<div class="card profile-header">

<img src="../uploads/<?= htmlspecialchars($student['photo']) ?>" 
onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($student['name']) ?>'">

<div class="profile-name"><?= htmlspecialchars($student['name']) ?></div>

<?php
$status_class = match($status){
    'Continue' => 'status-active',
    'Completed' => 'status-completed',
    'Dropped' => 'status-dropped',
    'Hold' => 'status-hold',
    default => 'status-active'
};
?>

<div class="badge <?= $status_class ?>">
<?= $status ?>
</div>

</div>

<div class="card">

<div class="grid">

<div class="item">
<div class="label">Enrollment ID</div>
<div class="value"><?= htmlspecialchars($student['enrollment_id']) ?></div>
</div>

<div class="item">
<div class="label">Course</div>
<div class="value"><?= htmlspecialchars($student['course']) ?></div>
</div>

<div class="item">
<div class="label">Contact</div>
<div class="value"><?= htmlspecialchars($student['contact_number']) ?></div>
</div>

<div class="item">
<div class="label">Address</div>
<div class="value"><?= htmlspecialchars($student['address']) ?></div>
</div>

</div>

</div>

</div>

</body>
</html>