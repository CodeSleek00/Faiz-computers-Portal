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
/* Add scrollable container */
.scrollable-table {
    max-height: 80vh;
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

/* Custom scrollbar styling */
.scrollable-table::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.scrollable-table::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 6px;
}

.scrollable-table::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 6px;
    border: 2px solid #f1f5f9;
}

.scrollable-table::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* For Firefox */
.scrollable-table {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

table{
    border-collapse:collapse;
    width:max-content;
    min-width:100%;
}

th, td{
    padding:12px 18px;
    text-align:left;
    border-bottom:1px solid #e5e7eb;
    vertical-align:top;
    white-space:normal;
    word-break:break-word;
}

th{
    background:#2563eb;
    color:#fff;
    font-weight:500;
    white-space:nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
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
.btn-view{
    background:#16a34a;
    margin-top:6px;
}
.btn-view:hover{
    background:#15803d;
}

</style>
</head>

<body>

<div class="container">
    <div class="card">
        <h1>Student Dashboard (Full Details)</h1>

        <div class="scrollable-table">
            <table>
                <thead>
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
                        <th>Father's Name</th>
                        <th>Mother's Name</th>
                        <th>Parent Contact</th>
                        <th>Address</th>
                        <th>Permanent Address</th>
                        <th>Course</th>
                        <th>Duration (Months)</th>
                        <th>Admission Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
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
                        <a class="btn" href="edit_student.php?id=<?= $row['id'] ?>">Edit</a><br>

                        <a class="btn btn-view"
                        href="../admission/admission_success.php?eid=<?= urlencode($row['enrollment_id']) ?>"
                        target="_blank">
                        View Form
                        </a>
                    </td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>