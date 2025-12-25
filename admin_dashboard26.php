<?php
// admin_students.php
session_start();
include("database_connection/db_connect.php");

// ================= FETCH ALL STUDENTS =================
$result = $conn->query("SELECT id, name, contact, enrollment_id, course, photo FROM students26 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Student Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body{font-family:Poppins,sans-serif;background:#f4f6f9;margin:0}
.container{padding:30px}
.card{background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.08);padding:20px}
h1{margin-bottom:20px}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;text-align:left;border-bottom:1px solid #e5e7eb}
th{background:#2563eb;color:#fff}
img{width:50px;height:50px;border-radius:50%;object-fit:cover}
.btn{background:#2563eb;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;font-size:14px}
.btn:hover{background:#1e40af}
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Student Dashboard</h1>
        <table>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Enrollment</th>
                <th>Course</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><img src="uploads/<?= htmlspecialchars($row['photo']) ?>"></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                <td><?= htmlspecialchars($row['enrollment_id']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><a class="btn" href="edit_student.php?id=<?= $row['id'] ?>">Edit</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
