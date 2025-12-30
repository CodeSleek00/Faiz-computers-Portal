<?php
// admin_students.php
session_start();
include("../database_connection/db_connect.php");

/* ================= FETCH STUDENTS WITH FULL DETAILS ================= */
$sql = "
SELECT 
    s.id,
    s.photo,
    s.name,
    s.contact,
    s.enrollment_id,
    s.course,

    a.aadhar,
    a.apaar,
    a.email,
    a.religion,
    a.caste,
    a.address,
    a.permanent_address,
    a.dob,
    a.father_name,
    a.mother_name,
    a.parent_contact,
    a.course_name,
    a.duration,
    a.admission_date

FROM students26 s
LEFT JOIN admission a 
ON s.enrollment_id = a.enrollment_id
ORDER BY s.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Student Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body{
    font-family:Poppins,sans-serif;
    background:#f4f6f9;
    margin:0;
}
.container{
    padding:25px;
}
.card{
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    padding:20px;
}
h1{
    margin-bottom:20px;
}
.table-wrapper{
    overflow-x:auto;
}
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}
th,td{
    padding:10px 12px;
    text-align:left;
    border-bottom:1px solid #e5e7eb;
    vertical-align:top;
}
th{
    background:#2563eb;
    color:#fff;
    position:sticky;
    top:0;
    z-index:10;
}
img{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
}
.btn{
    background:#2563eb;
    color:#fff;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    display:inline-block;
}
.btn:hover{
    background:#1e40af;
}
.small{
    font-size:12px;
    color:#374151;
}
</style>
</head>

<body>

<div class="container">
    <div class="card">
        <h1>Student Dashboard (Full Details)</h1>

        <div class="table-wrapper">
        <table>
            <tr>
                <th>Photo</th>
                <th>Enrollment ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Aadhar</th>
                <th>Apaar</th>
                <th>Religion</th>
                <th>Caste</th>
                <th>DOB</th>
                <th>Father</th>
                <th>Mother</th>
                <th>Parent Contact</th>
                <th>Address</th>
                <th>Permanent Address</th>
                <th>Course</th>
                <th>Duration (Months)</th>
                <th>Admission Date</th>
                <th>Action</th>
            </tr>

            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>">
                </td>

                <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['contact']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['aadhar']) ?></td>
                <td><?= htmlspecialchars($row['apaar']) ?></td>
                <td><?= htmlspecialchars($row['religion']) ?></td>
                <td><?= htmlspecialchars($row['caste']) ?></td>
                <td><?= htmlspecialchars($row['dob']) ?></td>
                <td><?= htmlspecialchars($row['father_name']) ?></td>
                <td><?= htmlspecialchars($row['mother_name']) ?></td>
                <td><?= htmlspecialchars($row['parent_contact']) ?></td>

                <td class="small"><?= nl2br(htmlspecialchars($row['address'])) ?></td>
                <td class="small"><?= nl2br(htmlspecialchars($row['permanent_address'])) ?></td>

                <td><?= htmlspecialchars($row['course_name']) ?></td>
                <td><?= htmlspecialchars($row['duration']) ?></td>
                <td><?= htmlspecialchars($row['admission_date']) ?></td>

                <td>
                    <a class="btn" href="edit_student.php?id=<?= $row['id'] ?>">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>
        </div>
    </div>
</div>

</body>
</html>
